<?php

namespace App\Http\Controllers\Federation;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Memberships\Actions\CheckDuplicateSubscriptionAction;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AffiliationController extends Controller
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

    public function index(Request $request): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Base query for entity affiliations
        $query = Affiliation::with([
            'member.media', // The entity with media for profile images
            'federation',
            'memberSubscription.membershipPackage.affiliationPlans',
            'requester',
        ])
            ->where('member_type', 'entity'); // Filter for entity affiliations only

        // If not main federation, apply federation-specific filtering
        if (! $isMainFederation) {
            $query->where('federation_id', $federation->id); // Only affiliations for this federation

            // Also filter to only show affiliations for entities under this federation
            $query->whereHasMorph('member', [Entity::class], function ($subQuery) use ($federation) {
                $subQuery->whereHas('federations', function ($fedQuery) use ($federation) {
                    $fedQuery->where('federation.id', $federation->id)
                        ->where('entity_federation.status_class', ActiveEntityFederationState::class);
                });
            });
        }
        // If main federation, no filtering - show ALL entity affiliations across all federations

        // Apply filters (filter components submit values nested under filter[])
        $filterStatusClass = $request->input('filter.filter_status_class');
        if ($filterStatusClass) {
            $query->where('status_class', $filterStatusClass);
        }

        $filterEntityName = $request->input('filter.filter_entity_name');
        if ($filterEntityName) {
            $escapedName = addcslashes($filterEntityName, '%_');
            $query->whereHasMorph('member', [Entity::class], function ($q) use ($escapedName) {
                $q->where('name', 'like', '%' . $escapedName . '%');
            });
        }

        $filterAffiliationPlanId = $request->input('filter.filter_affiliation_plan_id');
        if ($filterAffiliationPlanId) {
            $query->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($q) use ($filterAffiliationPlanId) {
                $q->where('affiliation_plans.id', $filterAffiliationPlanId);
            });
        }

        // Filter by affiliation start/end dates
        $filterStartDate = $request->input('filter.filter_start_date');
        if ($filterStartDate) {
            $query->whereDate('start_date', '>=', $filterStartDate);
        }

        $filterEndDate = $request->input('filter.filter_end_date');
        if ($filterEndDate) {
            $query->whereDate('end_date', '<=', $filterEndDate);
        }

        // Filter by activation date (date of first paid document transaction)
        $filterActivationDateStart = $request->query('filter_activation_date_start');
        $filterActivationDateEnd = $request->query('filter_activation_date_end');

        if ($filterActivationDateStart || $filterActivationDateEnd) {
            $query->whereHas('memberSubscription', function ($subscriptionQuery) use ($filterActivationDateStart, $filterActivationDateEnd) {
                $subscriptionQuery->whereExists(function ($subQuery) use ($filterActivationDateStart, $filterActivationDateEnd) {
                    $subQuery->select(DB::raw(1))
                        ->from('documents')
                        ->join('document_detail', 'documents.id', '=', 'document_detail.document_id')
                        ->join('transactions', 'documents.id', '=', 'transactions.document_id')
                        ->whereColumn('document_detail.owner_id', 'member_subscriptions.id')
                        ->where('document_detail.owner_type', MemberSubscription::class)
                        ->where('documents.status_class', \Domain\Documents\States\PaidDocumentState::class);

                    if ($filterActivationDateStart) {
                        $subQuery->whereDate('transactions.created_at', '>=', $filterActivationDateStart);
                    }
                    if ($filterActivationDateEnd) {
                        $subQuery->whereDate('transactions.created_at', '<=', $filterActivationDateEnd);
                    }
                });
            });
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $affiliations = $query->paginate(15)->withQueryString();

        // Batch-compute activation dates for the current page
        $subscriptionIds = $affiliations->getCollection()
            ->pluck('member_subscription_id')
            ->filter()
            ->unique()
            ->values();

        $activationDates = [];
        if ($subscriptionIds->isNotEmpty()) {
            $paidDocuments = Document::with(['transactions', 'details'])
                ->where('status_class', PaidDocumentState::class)
                ->whereHas('details', function ($q) use ($subscriptionIds) {
                    $q->where('owner_type', MemberSubscription::class)
                        ->whereIn('owner_id', $subscriptionIds);
                })
                ->get();

            foreach ($paidDocuments as $document) {
                $subscriptionDetail = $document->details
                    ->where('owner_type', MemberSubscription::class)
                    ->whereIn('owner_id', $subscriptionIds->toArray())
                    ->first();

                if ($subscriptionDetail && $document->transactions->isNotEmpty()) {
                    $latestTransaction = $document->transactions->sortByDesc('created_at')->first();
                    $subId = $subscriptionDetail->owner_id;
                    if (! isset($activationDates[$subId])) {
                        $activationDates[$subId] = $latestTransaction->created_at;
                    }
                }
            }
        }

        // Get affiliation plans for filter dropdown
        $affiliationPlans = AffiliationPlan::where('federation_id', $federation->id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($plan) => [$plan->id => $plan->name])
            ->toArray();

        return view('web.federation.affiliations.index', compact('affiliations', 'affiliationPlans', 'activationDates'));
    }

    public function show(Affiliation $affiliation): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Ensure the affiliation is for an entity
        if ($affiliation->member_type !== 'entity') {
            abort(404);
        }

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Apply federation-specific access control
        if (! $isMainFederation && $affiliation->federation_id !== $federation->id) {
            abort(403, __('federation.affiliation_not_in_federation'));
        }

        // If not main federation, also ensure the entity belongs to this federation
        if (! $isMainFederation) {
            if (! $affiliation->member->federations()->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                abort(403, __('federation.affiliation_not_in_federation'));
            }
        }

        // Load relationships
        $affiliation->load([
            'member.district', // The entity with district
            'federation',
            'memberSubscription.membershipPackage.affiliationPlans.federation',
            'memberSubscription.membershipPackage.insurancePlans',
            'requester',
        ]);

        return view('web.federation.affiliations.show', compact('affiliation'));
    }

    public function create(): View
    {
        $federation = $this->resolveAuthenticatedFederation();

        // Get available packages for entities with distribution methods for federation
        $availablePackages = $this->getAvailablePackagesForFederation($federation);

        // Get entities under this federation for selection
        if ($federation->isMainFederation()) {
            $entities = Entity::with([
                'federations' => function ($query) {
                    $query->where('entity_federation.status_class', ActiveEntityFederationState::class);
                },
            ])->whereHas('federations', function (Builder $query) {
                $query->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })->get();
        } else {
            $entities = Entity::whereHas('federations', function (Builder $query) use ($federation) {
                $query->select('federation.id')
                    ->where('federation.id', $federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })->get();
        }

        return view('web.federation.affiliations.create', compact('availablePackages', 'entities'));
    }

    private function getAvailablePackagesForFederation($federation): \Illuminate\Database\Eloquent\Collection
    {
        return MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::ENTITY])
            // Must have federation distribution method
            ->whereJsonContains('distribution_methods', 'federation_managed')
            ->whereHas('federations', function ($query) use ($federation) {
                $query->where('federation.id', $federation->id);
            })
            // Must have at least one affiliation plan OR at least one insurance plan
            ->where(function ($query) {
                $query->whereHas('affiliationPlans', function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })->where(function ($sq) {
                        $sq->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
                })->orWhereHas('insurancePlans');
            })
            // Ensure ALL affiliation plans belong to the current federation
            ->whereDoesntHave('affiliationPlans', function ($query) use ($federation) {
                $query->where('federation_id', '!=', $federation->id);
            })
            ->get();
    }

    public function store(
        Request $request,
        CreateMemberSubscriptionAction $action,
        CreateSubscriptionDocumentAction $documentAction
    ): RedirectResponse {
        $federation = $this->resolveAuthenticatedFederation();

        $request->validate([
            'entity_id' => 'required|exists:entity,id',
            'membership_package_id' => 'required|exists:membership_packages,id',
        ], [
            'entity_id.required' => __('federation.entity_affiliations_section.entity_required'),
            'entity_id.exists' => __('federation.entity_affiliations_section.entity_invalid'),
            'membership_package_id.required' => __('memberships.package_selection_required'),
            'membership_package_id.exists' => __('memberships.package_selection_invalid'),
        ]);

        try {
            DB::beginTransaction();

            $entity = Entity::findOrFail($request->entity_id);

            // Verify that this entity belongs to this federation (unless it's the main federation)
            if (! $federation->isMainFederation()) {
                if (! $entity->federations()->where('federation.id', $federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                    throw new \Exception('Entity does not belong to this federation');
                }
            } else {
                // For main federation, just verify entity has at least one active federation
                if (! $entity->federations()->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                    throw new \Exception('Entity is not associated with any federation');
                }
            }

            // Load the package and verify it has federation distribution method
            $package = MembershipPackage::with(['affiliationPlans.federation', 'insurancePlans'])
                ->findOrFail($request->membership_package_id);

            // Verify package has federation distribution method
            if (! in_array('federation_managed', $package->distribution_methods ?? [])) {
                throw new \Exception('Package is not available for federation distribution');
            }

            // Check for duplicate subscription to the same package
            $duplicateChecker = new CheckDuplicateSubscriptionAction;
            if ($duplicateChecker->execute($entity, $package)) {
                DB::rollBack();

                return back()->with('error', __('memberships.duplicate_affiliation_plans_error'));
            }

            // Check for duplicate affiliation plans
            $activeAffiliationPlanIds = $this->getActiveAffiliationPlanIds($entity);
            $packageAffiliationPlanIds = $package->affiliationPlans->pluck('id')->toArray();
            $duplicateAffiliationPlans = array_intersect($activeAffiliationPlanIds, $packageAffiliationPlanIds);

            if (! empty($duplicateAffiliationPlans)) {
                DB::rollBack();

                return back()->with('error', __('memberships.duplicate_affiliation_plans_error'));
            }

            // Calculate total price to determine if payment is needed
            $totalPrice = $package->calculatePriceFor(Entity::class);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $request->membership_package_id,
                'member_type' => Entity::class,
                'member_id' => $entity->id,
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
                return redirect()->route('federation.entity-affiliations.index')
                    ->with('success', __('federation.affiliation_created_with_payment', ['entity' => $entity->name]));
            }

            return redirect()->route('federation.entity-affiliations.index')
                ->with('success', __('federation.affiliation_created_free', ['entity' => $entity->name]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Federation Affiliation Creation: ' . $e->getMessage());

            return back()->with('error', __('memberships.subscription_creation_error'));
        }
    }

    private function getActiveAffiliationPlanIds(Entity $entity): array
    {
        return $entity->memberSubscriptions()
            ->with('membershipPackage.affiliationPlans.federation')
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveMemberSubscriptionState::class,
            ])
            ->get()
            ->flatMap(function ($subscription) {
                return $subscription->membershipPackage->affiliationPlans->pluck('id');
            })
            ->toArray();
    }
}
