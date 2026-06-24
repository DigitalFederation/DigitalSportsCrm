<?php

namespace Domain\Insurance\DataTransferObject;

use Carbon\Carbon;

class InsuranceData
{
    public function __construct(
        public readonly ?int $insurance_plan_id,
        public readonly ?string $member_type,
        public readonly ?int $member_id,
        public readonly ?int $member_subscription_id,
        public readonly Carbon $start_date,
        public readonly Carbon $end_date,
        public readonly ?float $individual_fee,
        public readonly ?float $entity_fee,
        public readonly bool $is_external,
        public readonly ?string $policy_number
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            insurance_plan_id: $data['insurance_plan_id'] ?? null,
            member_type: $data['member_type'] ?? null,
            member_id: $data['member_id'] ?? null,
            member_subscription_id: $data['member_subscription_id'] ?? null,
            start_date: Carbon::parse($data['start_date']),
            end_date: Carbon::parse($data['end_date']),
            individual_fee: $data['member_type'] === 'individual' ? $data['fee'] : null,
            entity_fee: $data['member_type'] === 'entity' ? $data['fee'] : null,
            is_external: $data['is_external'] ?? false,
            policy_number: $data['policy_number'] ?? null
        );
    }
}
