<?php

namespace App\Http\Controllers\Individual;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\Actions\RenewMemberSubscriptionAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\ExpiredMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberSubscriptionController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();

        if (! $individual) {
            return redirect()->route('individual.profile.create')
                ->with('info', __('memberships.complete_profile_before_managing_subscriptions'));
        }
        // Get the current active and pending subscriptions
        // Exclude insurance-only packages (those should appear in /individual/insurance)
        $currentSubscription = $individual->memberSubscriptions()
            ->with(['membershipPackage.affiliationPlans', 'membershipPackage.insurancePlans'])
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

        // Add document information to each subscription
        $currentSubscription->each(function (MemberSubscription $subscription) use ($individual) {
            // Documents are owned by the individual, but their details reference the subscription
            $subscription->documents = \Domain\Documents\Models\Document::where('owner_type', 'individual')
                ->where('owner_id', $individual->id)
                ->whereHas('details', function ($q) use ($subscription) {
                    $q->where('owner_type', \Domain\Memberships\Models\MemberSubscription::class)
                        ->where('owner_id', $subscription->id);
                })->get();

            $subscription->pendingDocument = $subscription->documents->filter(function ($doc) {
                return $doc->status_class === \Domain\Documents\States\PendingDocumentState::class;
            })->first();
        });

        // Exclude insurance-only packages from history as well
        $subscriptionHistory = $individual->memberSubscriptions()
            ->with('membershipPackage')
            ->where('end_date', '<', now())
            ->whereHas('membershipPackage', function ($query) {
                $query->whereHas('affiliationPlans');
            })
            ->orderBy('end_date', 'desc')
            ->get();

        // Get available packages (excluding those already subscribed to)
        $activePackageIds = $currentSubscription->pluck('membership_package_id')->toArray();
        $activeAffiliationPlanIds = $this->getActiveAffiliationPlanIds($individual);
        $activeInsurancePlanIds = $this->getActiveInsurancePlanIds($individual);
        $availablePackages = $this->getAvailablePackagesForIndividual($individual, $activePackageIds, $activeAffiliationPlanIds, $activeInsurancePlanIds);

        return view('web.individual.subscriptions.index', compact('individual', 'currentSubscription', 'subscriptionHistory', 'availablePackages'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();

        if (! $individual) {
            return redirect()->route('individual.profile.create')
                ->with('info', __('memberships.complete_profile_before_selecting_subscription'));
        }

        // Check if a specific package was selected
        $selectedPackageId = $request->query('package');
        $selectedPackage = null;

        if ($selectedPackageId) {
            $selectedPackage = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($selectedPackageId);
        }

        // Get current active subscriptions to exclude from available packages
        $currentSubscriptions = $individual->memberSubscriptions()
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveMemberSubscriptionState::class,
                PendingPaymentMemberSubscriptionState::class,
            ])
            ->get();

        $activePackageIds = $currentSubscriptions->pluck('membership_package_id')->toArray();
        $activeAffiliationPlanIds = $this->getActiveAffiliationPlanIds($individual);
        $activeInsurancePlanIds = $this->getActiveInsurancePlanIds($individual);

        // Get available packages using the proper filtering method
        $availablePackages = $this->getAvailablePackagesForIndividual($individual, $activePackageIds, $activeAffiliationPlanIds, $activeInsurancePlanIds);

        return view('web.individual.subscriptions.create', compact('individual', 'selectedPackage', 'availablePackages', 'selectedPackageId'));
    }

    public function store(
        Request $request,
        CreateMemberSubscriptionAction $action,
        CreateSubscriptionDocumentAction $documentAction,
        SubscriptionValidationService $validationService
    ): RedirectResponse {

        $request->validate([
            'membership_package_id' => 'required|exists:membership_packages,id',
        ], [
            'membership_package_id.required' => __('memberships.package_selection_required'),
            'membership_package_id.exists' => __('memberships.package_selection_invalid'),
        ]);
        try {
            DB::beginTransaction();

            // Get the current user
            $user = auth()->user();

            if (! $user) {
                DB::rollBack();

                return redirect()->route('login')
                    ->with('error', __('memberships.please_login_to_continue'));
            }

            // Try to get the individual record
            $individual = $user->individuals()->first();

            if (! $individual) {
                DB::rollBack();
                Log::error('No individual record found for user', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);

                return redirect()->route('individual.profile.create')
                    ->with('info', __('memberships.complete_profile_before_purchasing_subscription'));
            }

            // Ensure the individual has an ID
            if (! $individual->id) {
                DB::rollBack();
                Log::error('Individual record has no ID', [
                    'user_id' => $user->id,
                    'individual_attributes' => $individual->toArray(),
                ]);

                return back()->with('error', __('memberships.profile_issue_contact_support'));
            }

            // Debug log the individual
            Log::info('Individual subscription attempt:', [
                'user_id' => $user->id,
                'individual_id' => $individual->id,
                'package_id' => $request->membership_package_id,
            ]);

            // Load the package to check if it has fees
            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($request->membership_package_id);

            // NOTE: Insurance validation is now handled in SubscriptionValidationService
            // which has different rules for individual self-subscription vs entity-managed subscriptions
            // This allows individuals to subscribe to insurance packages as part of their first subscription

            // Check if individual already has a pending subscription for this package
            $existingPendingSubscription = $individual->memberSubscriptions()
                ->where('membership_package_id', $package->id)
                ->where('status_class', PendingPaymentMemberSubscriptionState::class)
                ->first();

            if ($existingPendingSubscription) {
                DB::rollBack();

                // If there's a document associated, redirect to it
                // Documents are owned by the individual, but their details reference the subscription
                $document = \Domain\Documents\Models\Document::where('owner_type', 'individual')
                    ->where('owner_id', $individual->id)
                    ->whereHas('details', function ($q) use ($existingPendingSubscription) {
                        $q->where('owner_type', \Domain\Memberships\Models\MemberSubscription::class)
                            ->where('owner_id', $existingPendingSubscription->id);
                    })->first();
                if ($document) {
                    return redirect()->route('individual.document.show', $document->id)
                        ->with('info', __('memberships.subscription_already_pending_payment'));
                }

                return back()->with('error', __('memberships.subscription_already_pending'));
            }

            // Validate subscription according to business rules
            $validation = $validationService->validateSubscription($individual, $package);
            if (! $validation['valid']) {
                DB::rollBack();

                return back()->with('error', $validation['error']);
            }

            // Check if individual meets document requirements
            if (! $package->individualMeetsDocumentRequirements($individual)) {
                $missingRequirements = $package->getMissingDocumentRequirements($individual);
                DB::rollBack();

                $errorMessage = __('memberships.missing_official_documents') . ' ';
                foreach ($missingRequirements as $requirement) {
                    $errorMessage .= __('memberships.insurance_requires_document', [
                        'insurance' => $requirement['insurance_plan'],
                        'document' => $requirement['required_document_type'],
                    ]) . ' ';
                }

                return back()->with('error', $errorMessage);
            }

            // Calculate total price to determine if payment is needed
            $totalPrice = $package->calculatePriceFor(get_class($individual));

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $request->membership_package_id,
                'member_type' => get_class($individual),
                'individual_id' => $individual->id,
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

                DB::commit();

                // Redirect to document payment page
                return redirect()->route('individual.document.show', $document->id)
                    ->with('success', __('memberships.insurance_subscription_created_pending_payment'));
            }

            DB::commit();

            return redirect()->route('individual.subscriptions.index')
                ->with('success', __('memberships.subscription_created_free'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MemberSubscription: ' . $e->getMessage());

            return back()->with('error', __('memberships.subscription_creation_error'));
        }
    }

    public function show(MemberSubscription $subscription): View
    {
        $subscription->load([
            'membershipPackage.affiliationPlans.federation',
            'membershipPackage.insurancePlans',
            'affiliations.federation',
            'insurances.insurancePlan',
        ]);

        return view('web.individual.subscriptions.show', compact('subscription'));
    }
    public function renew(MemberSubscription $subscription, RenewMemberSubscriptionAction $action): RedirectResponse
    {

        if ($subscription->status_class !== ExpiredMemberSubscriptionState::class) {
            return back()->with('error', __('memberships.subscription_not_eligible_for_renewal'));
        }

        try {
            DB::beginTransaction();

            $renewedSubscription = $action($subscription);

            // Here you would typically integrate with a payment gateway
            // For this example, we'll just mark it as paid
            $renewedSubscription->update(['status_class' => ActiveMemberSubscriptionState::class]);

            DB::commit();

            return redirect()->route('individual.subscriptions.show', $renewedSubscription->id)
                ->with('success', __('memberships.subscription_renewed_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', __('memberships.renewal_error_try_again'));
        }
    }

    public function history(): View|RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();

        if (! $individual) {
            return redirect()->route('individual.profile.create')
                ->with('info', __('memberships.complete_profile_before_viewing_history'));
        }
        $subscriptions = $individual->memberSubscriptions()
            ->with('membershipPackage')
            ->orderBy('end_date', 'desc')
            ->get();

        return view('web.individual.subscriptions.history', compact('individual', 'subscriptions'));
    }

    private function getAvailablePackagesForIndividual(Individual $individual, array $excludePackageIds = [], array $excludeAffiliationPlanIds = [], array $excludeInsurancePlanIds = []): \Illuminate\Database\Eloquent\Collection
    {
        $individualFederationIds = $individual->federations()->pluck('federation.id')->toArray();

        $query = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('membership_packages.is_active', true)
            ->where('membership_packages.target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('membership_packages.distribution_methods', 'direct')
            // Package must have affiliation plans (with or without insurance plans)
            ->whereHas('affiliationPlans', function ($query) {
                $query->where(function ($q) {
                    // Include plans with type='individual' OR plans with type='entity' that have individual_fee set
                    $q->where('affiliation_plans.type', 'individual')
                        ->orWhere(function ($subQ) {
                            $subQ->where('affiliation_plans.type', 'entity')
                                ->whereNotNull('affiliation_plans.individual_fee')
                                ->where('affiliation_plans.individual_fee', '>', 0);
                        });
                })->where(function ($q) {
                    // Date filters apply to all plans
                    $q->where(function ($subQuery) {
                        $subQuery->whereNull('affiliation_plans.start_date')
                            ->orWhere('affiliation_plans.start_date', '<=', now());
                    })->where(function ($subQuery) {
                        $subQuery->whereNull('affiliation_plans.end_date')
                            ->orWhere('affiliation_plans.end_date', '>=', now());
                    });
                });
            })
            ->whereHas('federations', function ($query) use ($individualFederationIds) {
                $query->whereIn('federation.id', $individualFederationIds);
            });

        // Exclude packages that the individual already has active subscriptions for
        if (! empty($excludePackageIds)) {
            $query->whereNotIn('membership_packages.id', $excludePackageIds);
        }

        // Exclude packages that contain affiliation plans the individual already has
        if (! empty($excludeAffiliationPlanIds)) {
            $query->whereDoesntHave('affiliationPlans', function ($affiliationQuery) use ($excludeAffiliationPlanIds) {
                $affiliationQuery->whereIn('affiliation_plans.id', $excludeAffiliationPlanIds);
            });
        }

        // Exclude packages that contain insurance plans the individual already has
        if (! empty($excludeInsurancePlanIds)) {
            $query->whereDoesntHave('insurancePlans', function ($insuranceQuery) use ($excludeInsurancePlanIds) {
                $insuranceQuery->whereIn('insurance_plans.id', $excludeInsurancePlanIds);
            });
        }

        return $query->get();
    }

    private function getActiveAffiliationPlanIds(Individual $individual): array
    {
        return $individual->memberSubscriptions()
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

    private function getActiveInsurancePlanIds(Individual $individual): array
    {
        return \Domain\Insurance\Models\Insurance::where('member_type', 'individual')
            ->where('member_id', $individual->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                \Domain\Insurance\States\ActiveInsuranceState::class,
            ])
            ->pluck('insurance_plan_id')
            ->toArray();
    }

    public function subscribeToPackage(
        MembershipPackage $package,
        CreateMemberSubscriptionAction $action,
        CreateSubscriptionDocumentAction $documentAction,
        SubscriptionValidationService $validationService
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            if (! $user) {
                DB::rollBack();

                return redirect()->route('login')
                    ->with('error', __('memberships.please_login_to_continue'));
            }

            $individual = $user->individuals()->first();

            if (! $individual) {
                DB::rollBack();

                return redirect()->route('individual.profile.create')
                    ->with('info', __('memberships.complete_profile_before_purchasing_subscription'));
            }

            if (! $individual->id) {
                DB::rollBack();

                return back()->with('error', __('memberships.profile_issue_contact_support'));
            }

            // Check if individual already has a pending subscription for this package
            $existingPendingSubscription = $individual->memberSubscriptions()
                ->where('membership_package_id', $package->id)
                ->where('status_class', PendingPaymentMemberSubscriptionState::class)
                ->first();

            if ($existingPendingSubscription) {
                DB::rollBack();

                // If there's a document associated, redirect to it
                // Documents are owned by the individual, but their details reference the subscription
                $document = \Domain\Documents\Models\Document::where('owner_type', 'individual')
                    ->where('owner_id', $individual->id)
                    ->whereHas('details', function ($q) use ($existingPendingSubscription) {
                        $q->where('owner_type', \Domain\Memberships\Models\MemberSubscription::class)
                            ->where('owner_id', $existingPendingSubscription->id);
                    })->first();
                if ($document) {
                    return redirect()->route('individual.document.show', $document->id)
                        ->with('info', __('memberships.subscription_already_pending_payment'));
                }

                return back()->with('error', __('memberships.subscription_already_pending'));
            }

            // Validate subscription according to business rules
            $validation = $validationService->validateSubscription($individual, $package);
            if (! $validation['valid']) {
                DB::rollBack();

                return back()->with('error', $validation['error']);
            }

            // Check if individual meets document requirements
            if (! $package->individualMeetsDocumentRequirements($individual)) {
                $missingRequirements = $package->getMissingDocumentRequirements($individual);
                DB::rollBack();

                $errorMessage = __('memberships.missing_official_documents') . ' ';
                foreach ($missingRequirements as $requirement) {
                    $errorMessage .= __('memberships.insurance_requires_document', [
                        'insurance' => $requirement['insurance_plan'],
                        'document' => $requirement['required_document_type'],
                    ]) . ' ';
                }

                return back()->with('error', $errorMessage);
            }

            // Calculate total price to determine if payment is needed
            $totalPrice = $package->calculatePriceFor(get_class($individual));

            $subscriptionData = MemberSubscriptionData::fromArray([
                'membership_package_id' => $package->id,
                'member_type' => get_class($individual),
                'individual_id' => $individual->id,
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

                DB::commit();

                // Redirect to document payment page
                return redirect()->route('individual.document.show', $document->id)
                    ->with('success', __('memberships.insurance_subscription_created_pending_payment'));
            }

            DB::commit();

            // Determine which page to redirect based on package type
            $redirectRoute = $package->affiliationPlans->isEmpty() && $package->insurancePlans->isNotEmpty()
                ? 'individual.insurance.index'
                : 'individual.subscriptions.index';

            return redirect()->route($redirectRoute)
                ->with('success', __('memberships.subscription_created_free'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Individual MemberSubscription: ' . $e->getMessage());

            return back()->with('error', __('memberships.subscription_creation_error'));
        }
    }
}
