<?php

namespace Domain\Memberships\Actions;

use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Support\Facades\Log;

class CreateSubscriptionDocumentAction
{
    public function execute(MemberSubscription $subscription): ?Document
    {
        Log::info('Starting document creation for MemberSubscription', [
            'subscription_id' => $subscription->id,
            'member_type' => $subscription->member_type,
            'member_id' => $subscription->member_id,
            'package_id' => $subscription->membership_package_id,
        ]);

        $documentDetailsData = $this->generateDocumentDetailsData($subscription);

        // Check if we have any document details
        if (empty($documentDetailsData)) {
            throw new \Exception('Cannot create document: No fees found for the selected package. Please check that the affiliation and insurance plans have proper fee values configured.');
        }

        // Determine who should receive the payment document
        $this->determineDocumentRecipient($subscription, $documentOwnerId, $documentOwnerType);

        $document = (new CreateDocumentWithDetailsAction)(
            $documentDetailsData,
            'ORD',
            $documentOwnerId,
            $documentOwnerType
        );

        if ($document === null) {
            Log::info('No document created - total value is zero', [
                'subscription_id' => $subscription->id,
                'document_owner_id' => $documentOwnerId,
                'document_owner_type' => $documentOwnerType,
            ]);

            return null;
        }

        Log::info('Finished document creation', [
            'document_id' => $document->id,
            'total_cost' => $document->total_value,
            'document_owner_id' => $documentOwnerId,
            'document_owner_type' => $documentOwnerType,
        ]);

        return $document;
    }

    private function generateDocumentDetailsData(MemberSubscription $subscription): array
    {
        $documentDetailsData = [];
        $package = $subscription->membershipPackage;
        $member = $subscription->member;

        // Add affiliation plan fees
        $this->addAffiliationFees($subscription, $package, $documentDetailsData);

        // Add insurance plan fees
        $this->addInsuranceFees($subscription, $package, $documentDetailsData);

        // Log warning if no document details were created
        if (empty($documentDetailsData)) {
            Log::warning('No document details created for subscription', [
                'subscription_id' => $subscription->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'member_type' => $subscription->member_type,
                'affiliation_plans_count' => $package->affiliationPlans->count(),
                'insurance_plans_count' => $package->insurancePlans->count(),
            ]);
        }

        return $documentDetailsData;
    }

    private function addAffiliationFees(
        MemberSubscription $subscription,
        $package,
        array &$documentDetailsData
    ): void {
        // Ensure federation relationship is loaded
        $package->load('affiliationPlans.federation');

        foreach ($package->affiliationPlans as $affiliationPlan) {
            // Determine who is paying
            $payerIsEntity = $this->isEntityPaying($subscription);

            // Use entity fee when entity is paying, individual fee when individual is paying
            $fee = $payerIsEntity
                ? $affiliationPlan->entity_fee
                : $affiliationPlan->individual_fee;

            Log::info('Affiliation fee calculation', [
                'plan_name' => $affiliationPlan->name,
                'payer_is_entity' => $payerIsEntity,
                'subscription_member_type' => $subscription->member_type,
                'entity_fee' => $affiliationPlan->entity_fee,
                'individual_fee' => $affiliationPlan->individual_fee,
                'selected_fee' => $fee,
            ]);

            if ($fee > 0) {
                $documentDetailsData[] = $this->createDocumentDetailData(
                    $subscription,
                    __('memberships.affiliation_description', [
                        'name' => $affiliationPlan->name,
                        'federation' => $affiliationPlan->federation->name,
                    ]),
                    $fee,
                    1,
                    $affiliationPlan->getVatRatePercentage(),
                    $affiliationPlan->moloni_reference
                );
            }
        }
    }

    private function addInsuranceFees(
        MemberSubscription $subscription,
        $package,
        array &$documentDetailsData
    ): void {
        // Ensure insurance plans are loaded
        $package->load('insurancePlans');

        foreach ($package->insurancePlans as $insurancePlan) {
            // Determine who is paying
            $payerIsEntity = $this->isEntityPaying($subscription);

            // Use entity fee when entity is paying, individual fee when individual is paying
            $fee = $payerIsEntity
                ? $insurancePlan->entity_fee
                : $insurancePlan->individual_fee;

            Log::info('Insurance fee calculation', [
                'plan_name' => $insurancePlan->name,
                'payer_is_entity' => $payerIsEntity,
                'subscription_member_type' => $subscription->member_type,
                'entity_fee' => $insurancePlan->entity_fee,
                'individual_fee' => $insurancePlan->individual_fee,
                'selected_fee' => $fee,
            ]);

            if ($fee > 0) {
                $documentDetailsData[] = $this->createDocumentDetailData(
                    $subscription,
                    __('memberships.insurance_description', ['name' => $insurancePlan->name]),
                    $fee,
                    1,
                    $insurancePlan->getVatRatePercentage(),
                    $insurancePlan->moloni_reference
                );
            }
        }
    }

    private function createDocumentDetailData(
        MemberSubscription $subscription,
        string $description,
        float $unitValue,
        int $quantity,
        int $taxPercentage = 23,
        ?string $reference = null
    ): DocumentDetailData {
        $member = $subscription->member;

        return DocumentDetailData::fromArray([
            'owner_id' => $subscription->id,
            'owner_type' => MemberSubscription::class,
            'description' => $description,
            'reference' => $reference,
            'unit_value' => $unitValue,
            'quantity' => $quantity,
            'customer_name' => $member->name ?? '',
            'tax_percentage' => $taxPercentage,
        ]);
    }

    private function isEntitySubscription(MemberSubscription $subscription): bool
    {
        return $subscription->member_type === 'entity' || $subscription->member_type === Entity::class;
    }

    private function determineDocumentRecipient(MemberSubscription $subscription, &$documentOwnerId, &$documentOwnerType): void
    {
        // Debug logging
        Log::info('Determining document recipient - Full subscription data', [
            'subscription_id' => $subscription->id,
            'member_type' => $subscription->member_type,
            'member_id' => $subscription->member_id,
            'requester_type' => $subscription->requester_type,
            'requester_id' => $subscription->requester_id,
            'request_type' => $subscription->request_type,
        ]);

        // Check if this is an individual subscription
        $isIndividualSubscription = $subscription->member_type === Individual::class ||
                                   $subscription->member_type === 'individual' ||
                                   $subscription->member_type === 'Domain\Individuals\Models\Individual';

        // If the requester is an entity and this is an individual subscription,
        // the payment document should go to the entity
        if ($isIndividualSubscription && $subscription->requester_type === 'entity') {
            $documentOwnerId = $subscription->requester_id;
            $documentOwnerType = 'entity'; // Use morph alias to match query in DocumentController

            Log::info('Document assigned to entity requester', [
                'subscription_id' => $subscription->id,
                'entity_id' => $documentOwnerId,
                'request_type' => $subscription->request_type,
                'member_id' => $subscription->member_id,
            ]);

            return;
        }

        // Default: assign to the subscription member (individual or entity)
        $documentOwnerId = $subscription->member_id;
        $documentOwnerType = $this->getMorphAliasMemberType($subscription->member_type);

        Log::info('Document assigned to subscription member (default path)', [
            'subscription_id' => $subscription->id,
            'owner_id' => $documentOwnerId,
            'owner_type' => $documentOwnerType,
            'requester_type' => $subscription->requester_type,
            'requester_id' => $subscription->requester_id,
            'request_type' => $subscription->request_type,
        ]);
    }

    private function isEntityPaying(MemberSubscription $subscription): bool
    {
        // Entity pays when:
        // 1. It's an entity subscription (entity buying for itself)
        // 2. The requester is an entity (entity buying for individuals)

        if ($this->isEntitySubscription($subscription)) {
            return true;
        }

        // Check if the requester is an entity (entity paying for individuals)
        if ($subscription->requester_type === 'entity') {
            return true;
        }

        return false;
    }

    private function getMorphAliasMemberType(string $memberType): string
    {
        // Return morph aliases to match the morphMap in AppServiceProvider
        return match ($memberType) {
            'individual', Individual::class, 'Domain\Individuals\Models\Individual' => 'individual',
            'entity', Entity::class, 'Domain\Entities\Models\Entity' => 'entity',
            default => $memberType,
        };
    }
}
