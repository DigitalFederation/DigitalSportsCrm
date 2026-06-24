<?php

namespace Domain\Memberships\DataTransferObject;

class MembershipPlanData
{
    public function __construct(
        public ?int $committee_id,
        public string $name,
        public ?string $friendly_name,
        public ?float $price,
        public ?int $interval,
        public ?string $interval_unit,
        public ?array $licenses
    ) {}

    public static function fromArray(array $data): MembershipPlanData
    {

        return new self(
            $data['committee_id'] ?? null,
            $data['name'],
            $data['friendly_name'] ?? null,
            $data['price'] ?? null,
            $data['interval'] ?? null,
            $data['interval_unit'] && array_key_exists($data['interval_unit'], config('enum.interval_unit')) ? $data['interval_unit'] : null,
            $data['licenses'] ?? null
        );
    }
}
