<?php

namespace Domain\Memberships\DataTransferObject;

use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;

class MemberSubscriptionData
{
    public function __construct(
        public readonly int $membership_package_id,
        public readonly string $member_type,
        public readonly ?string $member_id,
        public readonly ?int $entity_id,
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly string $status_class
    ) {}

    public static function fromArray(array $data): self
    {
        // Set member_id based on member_type (support both legacy strings and full class names)
        $member_id = null;
        if (($data['member_type'] === 'individual' || str_contains($data['member_type'], 'Individual')) && isset($data['individual_id'])) {
            $member_id = $data['individual_id'];
        }
        if (($data['member_type'] === 'entity' || str_contains($data['member_type'], 'Entity')) && isset($data['entity_id'])) {
            $member_id = $data['entity_id'];
        }

        // Fallback to direct member_id if provided
        if ($member_id === null && isset($data['member_id'])) {
            $member_id = $data['member_id'];
        }

        return new self(
            membership_package_id: $data['membership_package_id'],
            member_type: $data['member_type'],
            member_id: $member_id,
            entity_id: $data['entity_id'] ?? null,
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            status_class: $data['status_class'] ?? PendingPaymentMemberSubscriptionState::class
        );
    }
}
