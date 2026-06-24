<?php

namespace Domain\Licenses\DataTransferObject;

class LicenseData
{
    public function __construct(
        public int $committee_id,
        public int $type_id,
        public string $name,
        public ?int $professional_role_id,
        public ?int $sport_id,
        public ?float $unit_value,
        public ?float $unit_value_individual,
        public ?float $unit_value_entity,
        public ?float $unit_value_federation,
        public ?float $tax_value,
        public ?float $tax_percentage,
        public ?string $moloni_reference,
        public $logo,
        public ?string $license_code,
        public ?float $interval,
        public ?string $interval_unit,
        public ?string $validity_type,
        public ?array $requester_model,
        public readonly bool $is_school_license = false,
        public readonly bool $requires_cmas_approval = false,
        public readonly bool $requires_official_documents = false,
        public readonly bool $allow_entity_group_request = false,
        public readonly ?array $required_document_types = null,
        public readonly ?array $required_athlete_documents = null,
        public readonly ?array $required_coach_documents = null,
        public readonly ?array $required_official_documents = null,
        public readonly ?array $required_diving_professional_documents = null,
        public readonly ?array $required_certifications = null,
        public readonly ?array $roles = null,
        public readonly ?array $federation_ids = null,
        public readonly ?array $sport_ids = null,
    ) {

        if ($this->tax_percentage != 0 && $this->unit_value != 0) {
            $this->tax_value = $this->unit_value * ($this->tax_percentage / 100);
        }
    }

    public static function fromArray(array $data): LicenseData
    {
        return new self(
            $data['committee_id'],
            $data['type_id'],
            $data['name'],
            $data['function_id'] ?? null,
            ! empty($data['sport_ids']) ? $data['sport_ids'][0] : ($data['sport_id'] ?? null),
            $data['unit_value'] ?? null,
            $data['unit_value_individual'] ?? null,
            $data['unit_value_entity'] ?? null,
            $data['unit_value_federation'] ?? null,
            $data['tax_value'] ?? null,
            $data['tax_percentage'] ?? null,
            $data['moloni_reference'] ?? null,
            $data['logo'] ?? null,
            $data['license_code'] ?? null,
            $data['interval'] ?? null,
            $data['interval_unit'] ?? null,
            $data['validity_type'] ?? null,
            $data['requester_model'] ?? null,
            $data['is_school_license'] ?? false,
            $data['requires_cmas_approval'] ?? false,
            $data['requires_official_documents'] ?? false,
            $data['allow_entity_group_request'] ?? false,
            $data['required_document_types'] ?? null,
            $data['required_athlete_documents'] ?? null,
            $data['required_coach_documents'] ?? null,
            $data['required_official_documents'] ?? null,
            $data['required_diving_professional_documents'] ?? null,
            $data['required_certifications'] ?? null,
            $data['roles'] ?? null,
            $data['federation_ids'] ?? null,
            $data['sport_ids'] ?? null,
        );
    }
}
