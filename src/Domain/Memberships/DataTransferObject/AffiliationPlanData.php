<?php

namespace Domain\Memberships\DataTransferObject;

class AffiliationPlanData
{
    public function __construct(
        public readonly ?int $federation_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $duration_months,
        public readonly ?float $individual_fee,
        public readonly ?float $entity_fee,
        public readonly ?string $moloni_reference,
        public readonly string $type,
        public readonly int $vat_rate = 23,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly bool $is_validation_plan = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            federation_id: $data['federation_id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            duration_months: $data['duration_months'],
            individual_fee: $data['individual_fee'] ?? null,
            entity_fee: $data['entity_fee'] ?? null,
            moloni_reference: $data['moloni_reference'] ?? null,
            type: $data['type'],
            vat_rate: $data['vat_rate'] ?? 23,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            is_validation_plan: $data['is_validation_plan'] ?? false
        );
    }
}
