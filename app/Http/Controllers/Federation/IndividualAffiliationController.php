<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\BulkMemberSubscriptionAction;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndividualAffiliationController extends Controller
{
    private function resolveAuthenticatedFederation(): \Domain\Federations\Models\Federation
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, __('federation.common.unauthorized_action'));
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
            abort(403, __('federation.common.federation_not_found'));
        }

        return $federation;
    }

    /**
     * Display a listing of individual affiliations.
     */
    public function index(Request $request): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Base query for individual affiliations
        $query = Affiliation::with([
            'member.entities', // The individual with their entities
            'federation',
            'memberSubscription.membershipPackage.affiliationPlans',
            'requester',
        ])
            ->where('member_type', 'individual'); // Filter for individual affiliations only

        // If not main federation, apply federation-specific filtering
        if (! $isMainFederation) {
            $query->where('federation_id', $federation->id); // Only affiliations for this federation

            // Also filter to only show affiliations for individuals from entities under this federation
            $query->whereHasMorph('member', [Individual::class], function ($subQuery) use ($federation) {
                $subQuery->whereHas('entities', function ($entityQuery) use ($federation) {
                    $entityQuery->whereHas('federations', function ($fedQuery) use ($federation) {
                        $fedQuery->where('federation.id', $federation->id)
                            ->where('entity_federation.status_class', ActiveEntityFederationState::class);
                    });
                });
            });
        }
        // If main federation, no filtering - show ALL individual affiliations across all federations

        // Validate filter inputs
        $request->validate([
            'filter_status_class' => ['nullable', 'string', 'max:255'],
            'filter_individual_name' => ['nullable', 'string', 'max:100'],
            'filter_entity_id' => ['nullable', 'integer'],
            'filter_start_date' => ['nullable', 'date'],
            'filter_end_date' => ['nullable', 'date'],
        ]);

        // Apply filters
        if ($request->filled('filter_status_class')) {
            $query->where('status_class', $request->filter_status_class);
        }

        if ($request->filled('filter_individual_name')) {
            $escapedName = addcslashes($request->filter_individual_name, '%_');
            $query->whereHasMorph('member', [Individual::class], function ($q) use ($escapedName) {
                $q->where(function ($subQuery) use ($escapedName) {
                    // Match names where search term is at start of name or start of a word
                    $subQuery->where('name', 'like', $escapedName . '%')
                        ->orWhere('name', 'like', '% ' . $escapedName . '%')
                        ->orWhere('surname', 'like', $escapedName . '%')
                        ->orWhere('surname', 'like', '% ' . $escapedName . '%');
                });
            });
        }

        if ($request->filled('filter_entity_id')) {
            $query->where('requester_type', 'entity')
                ->where('requester_id', $request->filter_entity_id);
        }

        if ($request->filled('filter_start_date')) {
            $query->whereDate('start_date', '>=', $request->filter_start_date);
        }

        if ($request->filled('filter_end_date')) {
            $query->whereDate('end_date', '<=', $request->filter_end_date);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $affiliations = $query->paginate(15)->withQueryString();

        // Get distinct entities that have requested affiliations for the filter dropdown
        $entitiesForFilter = Entity::query()
            ->whereIn('id', function ($subQuery) use ($federation, $isMainFederation) {
                $subQuery->select('requester_id')
                    ->from('affiliations')
                    ->where('member_type', 'individual')
                    ->where('requester_type', 'entity');

                if (! $isMainFederation) {
                    $subQuery->where('federation_id', $federation->id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('web.federation.individual-affiliations.index', compact('affiliations', 'entitiesForFilter'));
    }

    /**
     * Show the form for creating new individual affiliations.
     * The Livewire component handles all the logic.
     */
    public function create(): View
    {
        $this->resolveAuthenticatedFederation();

        return view('web.federation.individual-affiliations.create');
    }

    /**
     * Store a new individual affiliation subscription.
     */
    public function store(Request $request, BulkMemberSubscriptionAction $action): RedirectResponse
    {
        $federation = $this->resolveAuthenticatedFederation();

        $validatedData = $request->validate([
            'membership_package_id' => ['required', 'exists:membership_packages,id'],
            'entity_id' => ['required', 'exists:entity,id'],
            'individuals' => ['required', 'array', 'min:1'],
            'individuals.*' => ['required', 'exists:individual,id'],
        ]);

        try {
            DB::beginTransaction();

            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($validatedData['membership_package_id']);

            $entity = Entity::findOrFail($validatedData['entity_id']);

            // Security: Validate entity belongs to this federation (unless main federation)
            if (! $federation->isMainFederation()) {
                $entityBelongsToFederation = $entity->federations()
                    ->where('federation.id', $federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class)
                    ->exists();

                if (! $entityBelongsToFederation) {
                    DB::rollback();

                    return redirect()->back()->with('error', __('federation.entity_not_in_federation'));
                }
            }

            // Security: Validate individuals are associated with the entity
            $individuals = Individual::whereIn('id', $validatedData['individuals'])
                ->whereHas('entities', function ($query) use ($entity) {
                    $query->where('entity.id', $entity->id);
                })
                ->get();

            if ($individuals->count() !== count($validatedData['individuals'])) {
                DB::rollback();

                return redirect()->back()->with('error', __('federation.individuals_not_in_entity'));
            }

            $result = $action->execute(
                $package,
                $individuals->pluck('id')->all(),
                $entity,
                'federation_facilitated'
            );

            if (! empty($result['failed'])) {
                DB::rollback();
                $message = collect($result['failed'])->pluck('error')->filter()->first()
                    ?? __('federation.error_creating_subscription');

                Notification::make()
                    ->title(__('federation.error_creating_subscription'))
                    ->body($message)
                    ->danger()
                    ->send();

                return redirect()->back()->with('error', $message);
            }

            DB::commit();

            Notification::make()
                ->title(__('federation.success'))
                ->body(__('federation.affiliations_created_successfully', ['count' => $individuals->count()]))
                ->success()
                ->send();

            return redirect()->route('federation.individual-affiliations.index')
                ->with('success', __('federation.affiliations_created_successfully', ['count' => $individuals->count()]));

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating individual affiliations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title(__('federation.error'))
                ->body(__('federation.error_creating_affiliations'))
                ->danger()
                ->send();

            return redirect()->back()->with('error', __('federation.error_creating_affiliations'));
        }
    }

    /**
     * Display the specified affiliation.
     */
    public function show(Affiliation $affiliation): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Ensure this is an individual affiliation
        if ($affiliation->member_type !== 'individual') {
            abort(404);
        }

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Apply federation-specific access control
        if (! $isMainFederation && $affiliation->federation_id !== $federation->id) {
            abort(403, __('federation.affiliation_not_in_federation'));
        }
        // If main federation, allow access to any affiliation

        // Load relationships
        // Note: affiliationPlan is an accessor that uses memberSubscription.membershipPackage.affiliationPlans
        $affiliation->load([
            'member.country',
            'member.district',
            'member.entities',
            'member.federations',
            'federation',
            'memberSubscription.membershipPackage.affiliationPlans',
            'memberSubscription.membershipPackage.insurancePlans',
            'memberSubscription.insurances.insurancePlan',
            'requester',
        ]);

        $individual = $affiliation->member;
        $startDateFormatted = $affiliation->start_date->format('d/m/Y');
        $endDateFormatted = $affiliation->end_date->format('d/m/Y');

        return view('web.federation.individual-affiliations.show', compact(
            'affiliation',
            'individual',
            'startDateFormatted',
            'endDateFormatted'
        ));
    }
}
