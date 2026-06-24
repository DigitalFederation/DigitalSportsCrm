<?php

namespace Domain\Memberships\DataTransferObject;

use Domain\Memberships\States\PendingMembershipState;

class MembershipData
{
    public function __construct(
        public readonly int $federation_id,
        public readonly array $plans,
        public string $name,
        public string $status_class,
        public ?string $current_term_starts_at,
        public ?string $current_term_ends_at
    ) {}

    public static function fromArray(array $data): MembershipData
    {
        return new self(
            $data['federation_id'],
            $data['plans'] ?? [],
            $data['name'],
            PendingMembershipState::class,
            $data['current_term_starts_at'] ?? null,
            $data['current_term_ends_at'] ?? null
        );
    }
}
