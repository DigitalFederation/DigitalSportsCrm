<?php

namespace Domain\Memberships\Actions;

use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Actions\CreateInsuranceAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkMemberSubscriptionAction
{
    public function __construct(
        private readonly CreateMemberSubscriptionAction $createMemberSubscriptionAction,
        private readonly CreateSubscriptionDocumentAction $createSubscriptionDocumentAction,
        private readonly ValidationPlanPrivilegeService $validationPlanService,
        private readonly CreateAffiliationAction $createAffiliationAction,
        private readonly CreateInsuranceAction $createInsuranceAction,
        private readonly SubscriptionValidationService $subscriptionValidationService
    ) {}

    /**
     * Create multiple member subscriptions for the selected individuals
     *
     * @param  mixed  $requester  Entity or Federation that is requesting the subscriptions
     * @param  string  $requestType  Type of request (entity_group, federation_facilitated, etc)
     */
    public function execute(MembershipPackage $package, array $individualIds, $requester = null, ?string $requestType = null): array
    {
        // Validate entity has permission to subscribe members
        if (auth()->user()->isEntity()) {
            $entity = auth()->user()->getEntity();

            if (! $entity) {
                throw new \Exception('Entity not found for user');
            }

            // Check if entity has validation plan privileges for member subscriptions
            if (! $this->validationPlanService->canSubscribeMembersToPackages($entity)) {
                $reason = $this->validationPlanService->getValidationPlanReason($entity, 'entity_member_subscriptions');
                throw new \Exception("Entity member subscription not authorized: {$reason}");
            }
        }

        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($individualIds as $individualId) {
                try {
                    $individual = Individual::findOrFail($individualId);

                    // Validate if the individual can subscribe to this package
                    $validationResult = $this->subscriptionValidationService->validateSubscription($individual, $package);
                    if (! $validationResult['valid']) {
                        Log::warning("Subscription validation failed for individual {$individualId}: " . $validationResult['error']);

                        $results['failed'][] = [
                            'individual_id' => $individualId,
                            'name' => $individual->name,
                            'error' => $validationResult['error'],
                        ];

                        continue;
                    }

                    // Calculate total price to determine subscription status
                    // When entity is subscribing individuals, use entity price
                    // When federation is facilitating, use individual price
                    $totalPrice = match (true) {
                        $requestType === 'entity_group' => $package->calculatePriceForType('entity'),
                        $requestType === 'federation_facilitated' => $package->calculatePriceFor(Individual::class),
                        auth()->user()?->isEntity() => $package->calculatePriceForType('entity'),
                        default => $package->calculatePriceFor(Individual::class)
                    };

                    // Create subscription data
                    $subscriptionData = MemberSubscriptionData::fromArray([
                        'membership_package_id' => $package->id,
                        'member_type' => Individual::class,
                        'member_id' => $individual->id,
                        'start_date' => Carbon::now()->format('Y-m-d'),
                        'end_date' => MemberSubscription::calculateAnnualEndDate(),
                        'status_class' => $totalPrice > 0
                            ? PendingPaymentMemberSubscriptionState::class
                            : ActiveMemberSubscriptionState::class,
                    ]);

                    // Create the subscription with custom requester if provided
                    if ($requester && $requestType) {
                        $subscription = $this->createSubscriptionWithRequester(
                            $subscriptionData,
                            $requester,
                            $requestType
                        );
                    } else {
                        // Create the subscription normally (determines requester from auth)
                        $subscription = ($this->createMemberSubscriptionAction)($subscriptionData);
                    }

                    // Don't create individual documents here - we'll create a consolidated one later
                    // Store subscription object for potential consolidated document
                    $results['success'][] = [
                        'individual_id' => $individual->id,
                        'name' => $individual->name,
                        'subscription_id' => $subscription->id,
                        'subscription' => $subscription, // Keep subscription object for consolidated document
                        'requires_payment' => $totalPrice > 0,
                        'total_price' => $totalPrice,
                    ];

                    // Log successful subscription creation
                    activity()
                        ->performedOn($subscription)
                        ->causedBy(auth()->user())
                        ->log("Bulk subscription created for individual {$individual->name}");

                } catch (\Exception $e) {
                    Log::error("Failed to create subscription for individual {$individualId}: " . $e->getMessage());

                    $results['failed'][] = [
                        'individual_id' => $individualId,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // After all subscriptions are created, create consolidated document if entity is paying
            if (auth()->user()->isEntity() && ! empty($results['success'])) {
                $this->createConsolidatedDocumentIfNeeded($results, $package);
            }

            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a subscription with a specific requester
     */
    private function createSubscriptionWithRequester(
        MemberSubscriptionData $data,
        $requester,
        string $requestType
    ): MemberSubscription {
        // Get the morph alias for the requester
        $requesterType = match (true) {
            $requester instanceof \Domain\Entities\Models\Entity => 'entity',
            $requester instanceof \Domain\Federations\Models\Federation => 'federation',
            $requester instanceof Individual => 'individual',
            default => throw new \Exception('Invalid requester type')
        };

        // Create the subscription directly with requester info
        $subscription = MemberSubscription::create([
            'membership_package_id' => $data->membership_package_id,
            'member_type' => 'individual', // Always individual for this action
            'member_id' => $data->member_id,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'status_class' => $data->status_class,
            'requester_type' => $requesterType,
            'requester_id' => $requester->id,
            'request_type' => $requestType,
        ]);

        // Create associated Affiliation and Insurance
        $this->createAffiliations($subscription, $data);
        $this->createInsurances($subscription, $data);

        return $subscription;
    }

    /**
     * Create affiliations for a subscription
     */
    private function createAffiliations(MemberSubscription $subscription, MemberSubscriptionData $data): void
    {
        $subscription->membershipPackage->load('affiliationPlans.federation');

        foreach ($subscription->membershipPackage->affiliationPlans as $affiliationPlan) {
            $this->createAffiliationAction->execute(
                $subscription,
                $affiliationPlan,
                $data->member_type,
                $data->member_id
            );
        }
    }

    /**
     * Create insurances for a subscription
     */
    private function createInsurances(MemberSubscription $subscription, MemberSubscriptionData $data): void
    {
        $subscription->membershipPackage->load('insurancePlans');

        foreach ($subscription->membershipPackage->insurancePlans as $insurancePlan) {
            $this->createInsuranceAction->execute(
                $subscription,
                $insurancePlan,
                $data->member_type,
                $data->member_id
            );
        }
    }

    /**
     * Create a consolidated document for all successful subscriptions that require payment
     */
    private function createConsolidatedDocumentIfNeeded(array &$results, MembershipPackage $package): void
    {
        // Filter only subscriptions that require payment
        $paidSubscriptions = collect($results['success'])->where('requires_payment', true);

        if ($paidSubscriptions->isEmpty()) {
            return; // No paid subscriptions, no document needed
        }

        $entity = auth()->user()->getEntity();
        if (! $entity) {
            Log::warning('Could not create consolidated document: Entity not found for user', [
                'user_id' => auth()->id(),
            ]);

            return;
        }

        try {
            $documentDetails = [];
            $totalAmount = 0;

            // Eager load affiliationPlans with federation relationship
            $package->load('affiliationPlans.federation', 'insurancePlans');

            // Create document details for each subscription
            foreach ($paidSubscriptions as $index => $subscriptionData) {
                $subscription = $subscriptionData['subscription'];
                $individual = Individual::find($subscriptionData['individual_id']);

                if (! $individual) {
                    continue;
                }

                // Add affiliation plan fees
                foreach ($package->affiliationPlans as $affiliationPlan) {
                    $fee = $affiliationPlan->entity_fee ?? $affiliationPlan->individual_fee;

                    if ($fee > 0) {
                        $documentDetails[] = DocumentDetailData::fromArray([
                            'owner_id' => $subscription->id,
                            'owner_type' => MemberSubscription::class,
                            'description' => __('memberships.affiliation_description', [
                                'name' => $affiliationPlan->name,
                                'federation' => $affiliationPlan->federation->name ?? '',
                            ]) . ' - ' . $individual->name,
                            'reference' => $affiliationPlan->moloni_reference,
                            'unit_value' => $fee,
                            'quantity' => 1,
                            'customer_name' => $individual->name,
                            'tax_percentage' => $affiliationPlan->getVatRatePercentage() ?? 23,
                        ]);
                        $totalAmount += $fee;
                    }
                }

                // Add insurance plan fees
                foreach ($package->insurancePlans as $insurancePlan) {
                    $fee = $insurancePlan->entity_fee ?? $insurancePlan->individual_fee;

                    if ($fee > 0) {
                        $documentDetails[] = DocumentDetailData::fromArray([
                            'owner_id' => $subscription->id,
                            'owner_type' => MemberSubscription::class,
                            'description' => __('memberships.insurance_description', [
                                'name' => $insurancePlan->name,
                            ]) . ' - ' . $individual->name,
                            'reference' => $insurancePlan->moloni_reference,
                            'unit_value' => $fee,
                            'quantity' => 1,
                            'customer_name' => $individual->name,
                            'tax_percentage' => $insurancePlan->getVatRatePercentage() ?? 23,
                        ]);
                        $totalAmount += $fee;
                    }
                }
            }

            if (empty($documentDetails)) {
                Log::warning('No document details created for consolidated document', [
                    'entity_id' => $entity->id,
                    'package_id' => $package->id,
                    'subscriptions_count' => $paidSubscriptions->count(),
                ]);

                return;
            }

            // Create the consolidated document
            // Use morph alias 'entity' to match the morphMap in AppServiceProvider
            $document = (new CreateDocumentWithDetailsAction)(
                $documentDetails,
                'ORD',
                $entity->id,
                'entity',
                __('memberships.bulk_subscription_document_note', [
                    'count' => $paidSubscriptions->count(),
                    'package' => $package->name,
                ])
            );

            if ($document === null) {
                Log::info('No consolidated document created - total value is zero', [
                    'entity_id' => $entity->id,
                    'package_id' => $package->id,
                    'subscriptions_count' => $paidSubscriptions->count(),
                ]);

                return;
            }

            // Update results to include the consolidated document ID
            foreach ($results['success'] as &$result) {
                if ($result['requires_payment']) {
                    $result['document_id'] = $document->id;
                }
            }

            Log::info('Consolidated document created for bulk subscriptions', [
                'document_id' => $document->id,
                'entity_id' => $entity->id,
                'total_subscriptions' => $paidSubscriptions->count(),
                'total_amount' => $totalAmount,
            ]);

            activity()
                ->performedOn($document)
                ->causedBy(auth()->user())
                ->log("Consolidated document created for {$paidSubscriptions->count()} member subscriptions");

        } catch (\Exception $e) {
            Log::error('Failed to create consolidated document', [
                'error' => $e->getMessage(),
                'entity_id' => $entity->id ?? null,
                'package_id' => $package->id,
            ]);
            // Don't throw - subscriptions were created successfully, just log the error
        }
    }
}
