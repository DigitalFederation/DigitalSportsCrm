<?php

namespace Domain\Memberships\Actions;

use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Memberships\Models\Membership;

/**
 * @mixin \Domain\Memberships\Actions\BuildMembershipDocumentDetailAction
 */
class BuildMembershipDocumentDetailAction
{
    public function __invoke(Membership $membership): array
    {
        $membership->load('plans');

        $documentLines = [];

        // Determine customer name based on Membership association
        $customerName = $membership->individual?->name ?? $membership->entity?->name ?? 'Unknown Customer';

        foreach ($membership->plans as $plan) {
            $documentLines[] = DocumentDetailData::fromArray([
                'owner_id' => $membership->id,
                'owner_type' => Membership::class,
                'unit_value' => $plan->price,
                'tax_value' => $plan->tax_value,
                'tax_percentage' => $plan->tax_percentage,
                'description' => $plan->name,
                'customer_name' => $customerName,
            ]);
        }

        return $documentLines;
    }
}
