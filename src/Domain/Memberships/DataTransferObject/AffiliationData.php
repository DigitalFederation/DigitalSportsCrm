<?php

namespace Domain\Memberships\DataTransferObject;

use Carbon\Carbon;

class AffiliationData
{
    public function __construct(
        public readonly int $federation_id,
        public readonly string $member_type,
        public readonly int $member_id,
        public readonly int $member_subscription_id,
        public readonly Carbon $start_date,
        public readonly Carbon $end_date,
        public readonly float $fee
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            federation_id: $data['federation_id'],
            member_type: $data['member_type'],
            member_id: $data['member_id'],
            member_subscription_id: $data['member_subscription_id'],
            start_date: Carbon::parse($data['start_date']),
            end_date: Carbon::parse($data['end_date']),
            fee: $data['fee']
        );
    }
}
