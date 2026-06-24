<?php

namespace App\Http\Controllers\Federation;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Insurance\Models\Insurance;
use Domain\Memberships\Actions\CheckDuplicateSubscriptionAction;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InsuranceController extends Controller
{
    public function index(): View
    {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Get insurances for entities
        $insurances = Insurance::query()
            ->where('member_type', 'entity') // Only entity insurances
            ->with([
                'insurancePlan',
                'memberSubscription.membershipPackage',
                'requester', // The entity that requested the insurance
                'member', // Eager load the polymorphic member relationship
            ]);

        // Apply federation-specific filtering
        if (! $isMainFederation) {
            // For local federations, only show insurances for entities with active affiliation
            $insurances->whereIn('member_id', function ($query) use ($federation) {
                $query->select('member_id')
                    ->from('affiliations')
                    ->where('member_type', 'entity')
                    ->where('federation_id', $federation->id)
                    ->where('status_class', ActiveAffiliationState::class);
            });
        }
        // If main federation, no filtering - show ALL entity insurances

        $insurances = $insurances->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('web.federation.insurances.index', compact('insurances'));
    }

    public function show(Insurance $insurance): View
    {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Load relationships
        $insurance->load(['member', 'insurancePlan', 'memberSubscription.membershipPackage', 'requester']);

        // Verify this insurance belongs to an entity
        if ($insurance->member_type !== 'entity') {
            abort(404);
        }

        $entity = $insurance->member;

        // Apply federation-specific access control
        if (! $isMainFederation) {
            // For local federations, check if entity has active affiliation with this federation
            $hasAffiliation = Affiliation::where('member_type', 'entity')
                ->where('member_id', $entity->id)
                ->where('federation_id', $federation->id)
                ->where('status_class', ActiveAffiliationState::class)
                ->exists();

            if (! $hasAffiliation) {
                abort(403, 'Insurance does not belong to an entity affiliated with this federation.');
            }
        }
        // If main federation, allow access to any insurance

        // Format dates
        $startDateFormatted = $insurance->start_date->format('d/m/Y');
        $endDateFormatted = $insurance->end_date->format('d/m/Y');

        return view('web.federation.insurances.show', compact(
            'insurance',
            'entity',
            'startDateFormatted',
            'endDateFormatted'
        ));
    }

    public function create(): View
    {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        // Get available insurance-only packages for entities
        $availableInsurancePackages = $this->getAvailableInsurancePackages($federation);

        // Get all entities under this federation for selection
        $entities = Entity::whereHas('federations', function (Builder $query) use ($federation) {
            $query->select('federation.id')
                ->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class);
        })->get();

        return view('web.federation.insurances.create', compact('availableInsurancePackages', 'entities'));
    }

    private function getAvailableInsurancePackages($federation): \Illuminate\Database\Eloquent\Collection
    {
        // Get insurance-only membership packages for entities
        return MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::ENTITY, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) use ($federation) {
                $query->where('federation.id', $federation->id);
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                // Only packages that have insurance plans and no affiliation plans (insurance-only)
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            })
            ->map(function ($package) {
                $package->calculated_price = $package->calculatePriceForType('entity');

                return $package;
            });
    }

    public function store(
        Request $request,
        CreateMemberSubscriptionAction $action,
        CreateSubscriptionDocumentAction $documentAction,
        SubscriptionValidationService $validationService
    ): RedirectResponse {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        $request->validate([
            'entity_id' => 'required|exists:entity,id',
            'membership_package_id' => 'required|exists:membership_packages,id',
        ], [
            'entity_id.required' => __('Please select an entity'),
            'entity_id.exists' => __('Selected entity is invalid'),
            'membership_package_id.required' => __('Please select an insurance package'),
            'membership_package_id.exists' => __('Selected insurance package is invalid'),
        ]);

        try {
            DB::beginTransaction();

            $entity = Entity::findOrFail($request->entity_id);

            // Verify that this entity belongs to this federation
            if (! $entity->federations()->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                throw new \Exception('Entity does not belong to this federation');
            }

            // Load the package and verify it's insurance-only
            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($request->membership_package_id);

            // Ensure this is an insurance-only package
            if ($package->affiliationPlans->isNotEmpty() || $package->insurancePlans->isEmpty()) {
                DB::rollBack();

                return back()->with('error', __('Selected package is not an insurance-only package'));
            }

            // Check for duplicate subscription to the same package
            $duplicateChecker = new CheckDuplicateSubscriptionAction;
            if ($duplicateChecker->execute($entity, $package)) {
                DB::rollBack();

                return back()->with('error', __('Entity already subscribed to this insurance package'));
            }

            // Validate subscription according to business rules (includes validation plan check and duplicate insurance check)
            $validation = $validationService->validateSubscription($entity, $package);
            if (! $validation['valid']) {
                DB::rollBack();

                return back()->with('error', $validation['error']);
            }

            // Calculate total price to determine if payment is needed
            $totalPrice = $package->calculatePriceFor(Entity::class);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $request->membership_package_id,
                'member_type' => Entity::class,
                'member_id' => $entity->id, // Changed from entity_id to member_id
                'start_date' => now()->format('Y-m-d'),
                'end_date' => MemberSubscription::calculateAnnualEndDate(),
                'status_class' => $totalPrice > 0
                    ? PendingPaymentMemberSubscriptionState::class
                    : ActiveMemberSubscriptionState::class,
                'requester_type' => Entity::class, // Entity pays
                'requester_id' => $entity->id, // Entity gets documents
                'request_type' => 'federation_facilitated', // Federation facilitated
            ]);

            $subscription = $action($subscriptionData);

            // Create payment document if payment is required (will be sent to entity)
            if ($totalPrice > 0) {
                $document = $documentAction->execute($subscription);
            }

            DB::commit();

            // Return with appropriate message based on payment requirement
            if ($totalPrice > 0) {
                return redirect()->route('federation.entity-insurances.index')
                    ->with('success', __('Insurance subscription created for :entity. Payment document sent to entity.', ['entity' => $entity->name]));
            }

            return redirect()->route('federation.entity-insurances.index')
                ->with('success', __('Free insurance subscription created for :entity successfully.', ['entity' => $entity->name]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Federation Insurance Subscription: ' . $e->getMessage());

            return back()->with('error', __('Error creating insurance subscription. Please try again.'));
        }
    }
}
