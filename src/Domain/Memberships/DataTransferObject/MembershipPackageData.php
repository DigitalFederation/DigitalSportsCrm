<?php

namespace Domain\Memberships\DataTransferObject;

use App\Enums\MembershipTargetType;

class MembershipPackageData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $federation_ids,
        public readonly MembershipTargetType $target_type,
        public readonly array $distribution_methods,
        public readonly bool $is_active,
        public readonly array $affiliation_plan_ids,
        public readonly array $insurance_plan_ids,
        public readonly array $license_ids
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            federation_ids: $data['federation_ids'] ?? [],
            target_type: MembershipTargetType::from($data['target_type']),
            distribution_methods: $data['distribution_methods'] ?? [],
            is_active: $data['is_active'],
            affiliation_plan_ids: $data['affiliation_plan_ids'] ?? [],
            insurance_plan_ids: $data['insurance_plan_ids'] ?? [],
            license_ids: $data['license_ids'] ?? []
        );
    }
}
