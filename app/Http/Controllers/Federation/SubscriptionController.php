<?php

namespace App\Http\Controllers\Federation;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Memberships\Actions\CheckDuplicateSubscriptionAction;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\Actions\FilterInsuranceOnlyPackagesAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubscriptionController extends Controller
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

        // Get ALL entity subscriptions for this federation
        // Shows all request types: federation_facilitated, entity_group, individual, etc.
        $query = MemberSubscription::with([
            'membershipPackage.affiliationPlans',
            'membershipPackage.insurancePlans',
            'member', // The entity
            'requester', // Who requested the subscription
            'affiliations.federation',
            'insurances.insurancePlan',
        ])
            ->where('member_type', 'entity') // Filter for entity subscriptions (using morph map key)
            ->whereHas('membershipPackage.affiliationPlans'); // Only packages with affiliation plans (not insurance-only)

        // If not main federation, filter to only show entities under this federation
        if (! $isMainFederation) {
            $query->whereHasMorph('member', [Entity::class], function ($subQuery) use ($federation) {
                $subQuery->whereHas('federations', function ($fedQuery) use ($federation) {
                    $fedQuery->where('federation.id', $federation->id)
                        ->where('entity_federation.status_class', ActiveEntityFederationState::class);
                });
            });
        }
        // If main federation, no additional filtering needed - show ALL entity subscriptions

        $subscriptions = $query->whereIn('status_class', [
            ActiveMemberSubscriptionState::class,
            PendingPaymentMemberSubscriptionState::class,
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('web.federation.subscriptions.index', compact('subscriptions'));
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

        // Get available packages for entities (excluding insurance-only)
        $availablePackages = $this->getAvailablePackagesForEntities($federation);

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

        return view('web.federation.subscriptions.create', compact('availablePackages', 'entities'));
    }

    private function getAvailablePackagesForEntities($federation): \Illuminate\Support\Collection
    {
        $query = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::ENTITY, MembershipTargetType::BOTH])
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('federations', function ($query) use ($federation) {
                $query->where('federation.id', $federation->id);
            })
            // Only show packages with affiliation plans (exclude insurance-only packages)
            ->whereHas('affiliationPlans', function ($query) {
                $query->where(function ($q) {
                    $q->where(function ($subQuery) {
                        $subQuery->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })->where(function ($subQuery) {
                        $subQuery->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
                });
            })
            // Ensure ALL affiliation plans belong to the current federation
            ->whereDoesntHave('affiliationPlans', function ($query) use ($federation) {
                $query->where('federation_id', '!=', $federation->id);
            });

        // Get packages and apply additional filtering to ensure no insurance-only packages
        $packages = $query->get();

        $filterAction = new FilterInsuranceOnlyPackagesAction;
        $filteredPackages = $filterAction->execute($packages);

        return $filteredPackages;
    }

    public function store(
        Request $request,
        CreateMemberSubscriptionAction $action,
        CreateSubscriptionDocumentAction $documentAction
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

            // Load the package to check if it has fees
            $package = MembershipPackage::with(['affiliationPlans.federation', 'insurancePlans'])
                ->findOrFail($request->membership_package_id);

            // Check for duplicate subscription to the same package
            $duplicateChecker = new CheckDuplicateSubscriptionAction;
            if ($duplicateChecker->execute($entity, $package)) {
                DB::rollBack();

                return back()->with('error', __('memberships.already_subscribed_to_package'));
            }

            // Check validation plan privileges for insurance-only packages
            if ($package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty()) {
                $validationPlanService = resolve(ValidationPlanPrivilegeService::class);
                if (! $validationPlanService->canRequestInsurance($entity)) {
                    $reason = $validationPlanService->getValidationPlanReason($entity, 'insurance');
                    DB::rollBack();

                    return back()->with('error', __('memberships.insurance_subscription_not_authorized', ['reason' => $reason]));
                }
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
                'entity_id' => $entity->id,
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
                return redirect()->route('federation.entity-subscriptions.index')
                    ->with('success', __('Subscription created for :entity. Payment document sent to entity.', ['entity' => $entity->name]));
            }

            return redirect()->route('federation.entity-subscriptions.index')
                ->with('success', __('Free subscription created for :entity successfully.', ['entity' => $entity->name]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Federation Subscription: ' . $e->getMessage());

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

    public function show(MemberSubscription $subscription): View
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

        // Ensure the subscription belongs to an entity under this federation (unless it's the main federation)
        if ($subscription->member_type !== 'entity') {
            abort(403, 'Unauthorized access to subscription');
        }

        if (! $federation->isMainFederation()) {
            if (! $subscription->member->federations()->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                abort(403, 'Unauthorized access to subscription');
            }
        } else {
            // For main federation, just verify entity has at least one active federation
            if (! $subscription->member->federations()->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                abort(403, 'Unauthorized access to subscription');
            }
        }

        // Load relationships
        $subscription->load([
            'membershipPackage.affiliationPlans.federation.country',
            'membershipPackage.insurancePlans',
            'affiliations.memberSubscription.membershipPackage.affiliationPlans',
            'affiliations.federation.country',
            'insurances.insurancePlan',
            'member', // The entity
        ]);

        return view('web.federation.subscriptions.show', compact('subscription'));
    }

    public function update(Request $request, MemberSubscription $subscription): RedirectResponse
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

        // Ensure the subscription belongs to an entity under this federation (unless it's the main federation)
        if ($subscription->member_type !== 'entity') {
            abort(403, 'Unauthorized access to subscription');
        }

        if (! $federation->isMainFederation()) {
            if (! $subscription->member->federations()->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                abort(403, 'Unauthorized access to subscription');
            }
        } else {
            // For main federation, just verify entity has at least one active federation
            if (! $subscription->member->federations()->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                abort(403, 'Unauthorized access to subscription');
            }
        }

        // Federation should not be able to mark subscriptions as paid
        // Entities must handle their own payments
        return back()->with('error', __('federation.cannot_mark_as_paid'));
    }
}
