<?php

namespace Domain\Memberships\DataTransferObject;

class PackagePricingData
{
    public function __construct(
        public readonly int $membership_package_id,
        public readonly float $price,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            membership_package_id: (int) $data['membership_package_id'],
            price: (float) $data['price'],
        );
    }
}
