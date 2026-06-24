<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\States\ActiveAffiliationState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IndividualInsuranceController extends Controller
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
     * Display a pure listing of existing individual insurance subscriptions.
     * No creation forms - just the data table.
     */
    public function index(Request $request): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        $query = Insurance::with([
            'insurancePlan',
            'member.entities',
            'memberSubscription.membershipPackage.affiliationPlans',
            'requester',
        ])
            ->where(function ($q) {
                $q->where('member_type', 'Domain\Individuals\Models\Individual')
                    ->orWhere('member_type', 'individual');
            });

        // Apply federation-specific filtering
        if (! $isMainFederation) {
            // For local federations, only show insurances for individuals with active affiliation
            $query->whereIn('member_id', function ($subQuery) use ($federation) {
                $subQuery->select('member_id')
                    ->from('affiliations')
                    ->where('member_type', 'individual')
                    ->where('federation_id', $federation->id)
                    ->where('status_class', ActiveAffiliationState::class);
            });
        }
        // If main federation, no filtering - show ALL individual insurances

        // Validate filter inputs
        $request->validate([
            'filter_individual_name' => ['nullable', 'string', 'max:100'],
            'filter_entity_id' => ['nullable', 'integer'],
            'filter_insurance_plan_id' => ['nullable', 'integer'],
            'filter_status_class' => ['nullable', 'string', 'max:255'],
            'filter_start_date' => ['nullable', 'date'],
            'filter_end_date' => ['nullable', 'date'],
        ]);

        // Apply filters
        if ($request->filled('filter_individual_name')) {
            $escapedName = addcslashes($request->filter_individual_name, '%_');
            $query->whereHasMorph('member', [Individual::class], function ($q) use ($escapedName) {
                $q->where(function ($subQuery) use ($escapedName) {
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

        if ($request->filled('filter_insurance_plan_id')) {
            $query->where('insurance_plan_id', $request->filter_insurance_plan_id);
        }

        if ($request->filled('filter_status_class')) {
            $query->where('status_class', $request->filter_status_class);
        }

        if ($request->filled('filter_start_date')) {
            $query->whereDate('start_date', '>=', $request->filter_start_date);
        }

        if ($request->filled('filter_end_date')) {
            $query->whereDate('end_date', '<=', $request->filter_end_date);
        }

        $insurances = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get entities for filter dropdown (entities that have requested insurances)
        $entitiesForFilter = Entity::query()
            ->whereIn('id', function ($subQuery) use ($federation, $isMainFederation) {
                $subQuery->select('requester_id')
                    ->from('insurances')
                    ->where('requester_type', 'entity')
                    ->where(function ($q) {
                        $q->where('member_type', 'Domain\Individuals\Models\Individual')
                            ->orWhere('member_type', 'individual');
                    });

                if (! $isMainFederation) {
                    $subQuery->whereIn('member_id', function ($affiliationQuery) use ($federation) {
                        $affiliationQuery->select('member_id')
                            ->from('affiliations')
                            ->where('member_type', 'individual')
                            ->where('federation_id', $federation->id)
                            ->where('status_class', ActiveAffiliationState::class);
                    });
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get insurance plans for filter dropdown
        $insurancePlansForFilter = InsurancePlan::query()
            ->whereIn('id', function ($subQuery) use ($federation, $isMainFederation) {
                $subQuery->select('insurance_plan_id')
                    ->from('insurances')
                    ->where(function ($q) {
                        $q->where('member_type', 'Domain\Individuals\Models\Individual')
                            ->orWhere('member_type', 'individual');
                    });

                if (! $isMainFederation) {
                    $subQuery->whereIn('member_id', function ($affiliationQuery) use ($federation) {
                        $affiliationQuery->select('member_id')
                            ->from('affiliations')
                            ->where('member_type', 'individual')
                            ->where('federation_id', $federation->id)
                            ->where('status_class', ActiveAffiliationState::class);
                    });
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('web.federation.individual-insurances.index', compact(
            'insurances',
            'federation',
            'entitiesForFilter',
            'insurancePlansForFilter'
        ));
    }

    /**
     * Display the specified individual insurance.
     */
    public function show(Insurance $insurance): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Load relationships
        $insurance->load(['member.country', 'member.district', 'member.entities', 'member.federations', 'insurancePlan.media', 'memberSubscription.membershipPackage', 'requester']);

        // Verify this insurance belongs to an individual (handle both formats)
        if ($insurance->member_type !== 'Domain\Individuals\Models\Individual' && $insurance->member_type !== 'individual') {
            abort(404);
        }

        $individual = $insurance->member;

        // Apply federation-specific access control
        if (! $isMainFederation) {
            // For local federations, check if individual has active affiliation with this federation
            $hasAffiliation = Affiliation::where('member_type', 'individual')
                ->where('member_id', $individual->id)
                ->where('federation_id', $federation->id)
                ->where('status_class', ActiveAffiliationState::class)
                ->exists();

            if (! $hasAffiliation) {
                abort(403, __('federation.insurance_not_in_federation'));
            }
        }
        // If main federation, allow access to any insurance

        // Format dates
        $startDateFormatted = $insurance->start_date->format('d/m/Y');
        $endDateFormatted = $insurance->end_date->format('d/m/Y');

        return view('web.federation.individual-insurances.show', compact(
            'insurance',
            'individual',
            'startDateFormatted',
            'endDateFormatted'
        ));
    }
}
