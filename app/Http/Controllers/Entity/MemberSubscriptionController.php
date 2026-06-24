<?php

namespace App\Http\Controllers\Entity;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Memberships\Actions\CheckDuplicateSubscriptionAction;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\Actions\FilterInsuranceOnlyPackagesAction;
use Domain\Memberships\Actions\RenewMemberSubscriptionAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MemberSubscriptionController extends Controller
{
    public function index(): View
    {
        $entity = Auth::user()->getEntity();

        // Get the current active and pending subscriptions with detailed relationships
        // Exclude insurance-only packages (they should only appear in /entity/insurances)
        $currentSubscription = MemberSubscription::with([
            'membershipPackage.affiliationPlans.federation.country',
            'membershipPackage.insurancePlans',
            'affiliations.memberSubscription.membershipPackage.affiliationPlans',
            'affiliations.federation.country',
            'insurances.insurancePlan',
        ])
            ->where('member_type', 'entity')
            ->where('member_id', $entity->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveMemberSubscriptionState::class,
                PendingPaymentMemberSubscriptionState::class,
            ])
            ->whereHas('membershipPackage', function ($query) {
                $query->whereHas('affiliationPlans');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Exclude insurance-only packages from history as well
        $subscriptionHistory = MemberSubscription::with([
            'membershipPackage.affiliationPlans.federation.country',
            'membershipPackage.insurancePlans',
            'affiliations.memberSubscription.membershipPackage.affiliationPlans',
            'affiliations.federation.country',
            'insurances.insurancePlan',
        ])
            ->where('member_type', 'entity')
            ->where('member_id', $entity->id)
            ->where('end_date', '<', now())
            ->whereHas('membershipPackage', function ($query) {
                $query->whereHas('affiliationPlans');
            })
            ->orderBy('end_date', 'desc')
            ->get();

        // Get available packages (excluding those already subscribed to)
        $activePackageIds = $currentSubscription->pluck('membership_package_id')->toArray();
        $activeAffiliationPlanIds = $this->getActiveAffiliationPlanIds($entity);
        $availablePackages = $this->getAvailablePackagesForEntity($entity, $activePackageIds, $activeAffiliationPlanIds);

        return view('web.entity.subscriptions.index', compact('entity', 'currentSubscription', 'subscriptionHistory', 'availablePackages'));
    }

    private function getAvailablePackagesForEntity(Entity $entity, array $excludePackageIds = [], array $excludeAffiliationPlanIds = []): \Illuminate\Support\Collection
    {
        $entityFederationIds = $entity->federations()->pluck('federation.id')->toArray();

        $query = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::ENTITY)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('federations', function ($query) use ($entityFederationIds) {
                $query->whereIn('federation.id', $entityFederationIds);
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
            });

        // Exclude packages that the entity already has active subscriptions for
        if (! empty($excludePackageIds)) {
            $query->whereNotIn('membership_packages.id', $excludePackageIds);
        }

        // Exclude packages that contain affiliation plans the entity already has
        if (! empty($excludeAffiliationPlanIds)) {
            $query->whereDoesntHave('affiliationPlans', function ($affiliationQuery) use ($excludeAffiliationPlanIds) {
                $affiliationQuery->whereIn('affiliation_plans.id', $excludeAffiliationPlanIds);
            });
        }

        // Get packages and apply additional filtering to ensure no insurance-only packages
        $packages = $query->get();

        $filterAction = new FilterInsuranceOnlyPackagesAction;

        return $filterAction->execute($packages);
    }

    private function getActiveAffiliationPlanIds(Entity $entity): array
    {
        return $entity->memberSubscriptions()
            ->with('membershipPackage.affiliationPlans')
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

    public function store(
        Request $request,
        CreateMemberSubscriptionAction $action,
        CreateSubscriptionDocumentAction $documentAction,
        SubscriptionValidationService $validationService,
        ?MembershipPackage $package = null
    ): RedirectResponse {

        // If package is provided via route parameter, use it; otherwise, validate from request
        if ($package) {
            $packageId = $package->id;
        } else {
            $request->validate([
                'membership_package_id' => 'required|exists:membership_packages,id',
            ], [
                'membership_package_id.required' => __('memberships.package_selection_required'),
                'membership_package_id.exists' => __('memberships.package_selection_invalid'),
            ]);
            $packageId = $request->membership_package_id;
        }

        try {
            DB::beginTransaction();

            // Try both methods to get entity
            $entity = auth()->user()->getEntity();

            // If getEntity() returns null, try direct relationship
            if (! $entity) {
                $entity = auth()->user()->entities()->first();
            }

            // Check if entity exists and has an ID
            if (! $entity || ! $entity->id) {
                Log::error('MemberSubscription: Entity not found', [
                    'user_id' => auth()->id(),
                    'user_group_id' => auth()->user()->group_id,
                    'entity_from_getEntity' => auth()->user()->getEntity(),
                    'entity_from_entities' => auth()->user()->entities()->first(),
                    'package_id' => $packageId,
                ]);
                throw new \Exception('Entity not found for current user');
            }

            // Load the package to check if it has fees
            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($packageId);

            // Check for duplicate subscription to the same package
            $duplicateChecker = new CheckDuplicateSubscriptionAction;
            if ($duplicateChecker->execute($entity, $package)) {
                DB::rollBack();

                return back()->with('error', __('memberships.already_subscribed_to_package'));
            }

            // Validate subscription according to business rules
            $validation = $validationService->validateSubscription($entity, $package);
            if (! $validation['valid']) {
                DB::rollBack();

                return back()->with('error', $validation['error']);
            }

            // Calculate total price to determine if payment is needed
            $totalPrice = $package->calculatePriceFor(Entity::class);

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $packageId,
                'member_type' => Entity::class,
                'entity_id' => $entity->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => MemberSubscription::calculateAnnualEndDate(),
                'status_class' => $totalPrice > 0
                    ? PendingPaymentMemberSubscriptionState::class
                    : ActiveMemberSubscriptionState::class,
            ]);

            $subscription = $action($subscriptionData);

            // Create payment document if payment is required
            if ($totalPrice > 0) {
                $document = $documentAction->execute($subscription);
            }

            DB::commit();

            // Determine which page to redirect based on package type
            $redirectRoute = $package->affiliationPlans->isEmpty() && $package->insurancePlans->isNotEmpty()
                ? 'entity.insurances.index'
                : 'entity.subscriptions.index';

            // Return with appropriate message based on payment requirement
            if ($totalPrice > 0) {
                return redirect()->route($redirectRoute)
                    ->with('success', __('memberships.subscription_created_pending_payment'));
            }

            return redirect()->route($redirectRoute)
                ->with('success', __('memberships.subscription_created_free'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MemberSubscription: ' . $e->getMessage());

            return back()->with('error', __('memberships.subscription_creation_error'));
        }
    }

    public function show(MemberSubscription $subscription): View
    {
        // Ensure the subscription belongs to the current entity
        $entity = Auth::user()->getEntity();

        if ($subscription->member_type !== 'entity' || $subscription->member_id !== $entity->id) {
            abort(403, 'Unauthorized access to subscription');
        }

        // Load relationships
        $subscription->load([
            'membershipPackage.affiliationPlans.federation.country',
            'membershipPackage.insurancePlans',
            'affiliations.memberSubscription.membershipPackage.affiliationPlans',
            'affiliations.federation.country',
            'insurances.insurancePlan',
        ]);

        return view('web.entity.subscriptions.show', compact('subscription', 'entity'));
    }

    public function renew(MemberSubscription $subscription, RenewMemberSubscriptionAction $action): RedirectResponse
    {
        $renewedSubscription = $action($subscription);

        return redirect()->route('entity.member-subscriptions.show', $renewedSubscription)->with('success', __('memberships.subscription_renewed_successfully'));
    }

    public function update(Request $request, MemberSubscription $subscription): RedirectResponse
    {
        // Ensure the subscription belongs to the current entity
        $entity = Auth::user()->getEntity();

        if ($subscription->member_type !== 'entity' || $subscription->member_id !== $entity->id) {
            abort(403, 'Unauthorized access to subscription');
        }

        // Check if this is a payment status update
        if ($request->has('payment_status') && $request->payment_status === 'paid') {
            // Check if subscription is in pending payment state
            if ($subscription->status_class === PendingPaymentMemberSubscriptionState::class) {
                try {
                    DB::beginTransaction();

                    // Activate the subscription
                    $subscription->status_class = ActiveMemberSubscriptionState::class;
                    $subscription->save();

                    // Also activate related affiliations
                    foreach ($subscription->affiliations as $affiliation) {
                        if ($affiliation->status_class === 'Domain\Memberships\States\PendingPaymentAffiliationState') {
                            $affiliation->status_class = 'Domain\Memberships\States\ActiveAffiliationState';
                            $affiliation->save();
                        }
                    }

                    // Also activate related insurances
                    foreach ($subscription->insurances as $insurance) {
                        if ($insurance->status === 'pending_payment') {
                            $insurance->status = 'active';
                            $insurance->save();
                        }
                    }

                    DB::commit();

                    return redirect()->route('entity.subscriptions.index')
                        ->with('success', __('Subscrição marcada como paga com sucesso'));
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to update subscription payment status: ' . $e->getMessage());

                    return back()->with('error', __('Erro ao atualizar estado do pagamento'));
                }
            }
        }

        return back()->with('error', __('Operação inválida'));
    }

    // Add edit and update methods as needed
}
