<?php

namespace Domain\Certifications\DataTransferObject;

use Illuminate\Http\UploadedFile;

class CertificationData
{
    public function __construct(
        public int $committee_id,
        public ?int $professional_role_id,
        public ?array $parent_id,
        public string $name,
        public ?string $acronym,
        public ?int $license_id,
        public readonly ?string $certification_category = null,
        public readonly ?int $offset_initial = null,
        public readonly ?int $offset_current = null,
        public readonly ?string $minimum_age = null,
        public readonly ?string $confined_water_sessions = null,
        public readonly ?string $open_water_sessions = null,
        public readonly ?string $theoretical_sessions = null,
        public readonly ?array $parents = null,
        public readonly UploadedFile|string|null $certification_view = null,
        // Pricing fields (legacy)
        public readonly bool $is_available = true,
        public readonly ?float $unit_value = null,
        public readonly ?float $unit_value_individual = null,
        public readonly ?float $unit_value_entity = null,
        public readonly ?float $tax_value = null,
        public readonly ?float $tax_percentage = null,
        public readonly ?string $moloni_reference = null,
        // New pricing fields
        public readonly ?float $digital_price = null,
        public readonly ?float $digital_plus_card_price = null,
        public readonly ?string $requester_model = null,
        public readonly bool $allow_entity_group_request = false,
        public readonly bool $requires_admin_validation = false,
        public readonly ?array $roles = null
    ) {}

    public static function fromArray(array $data): CertificationData
    {
        // Handle boolean values
        $isAvailable = true;
        if (isset($data['is_available'])) {
            $isAvailable = is_bool($data['is_available']) ?
                $data['is_available'] :
                in_array(strtolower($data['is_available']), ['1', 'on', 'true', 'yes']);
        }

        $allowEntityGroupRequest = false;
        if (isset($data['allow_entity_group_request'])) {
            $allowEntityGroupRequest = is_bool($data['allow_entity_group_request']) ?
                $data['allow_entity_group_request'] :
                in_array(strtolower($data['allow_entity_group_request']), ['1', 'on', 'true', 'yes']);
        }

        $requiresAdminValidation = false;
        if (isset($data['requires_admin_validation'])) {
            $requiresAdminValidation = is_bool($data['requires_admin_validation']) ?
                $data['requires_admin_validation'] :
                in_array(strtolower($data['requires_admin_validation']), ['1', 'on', 'true', 'yes']);
        }

        $args = [
            'committee_id' => (int) $data['committee_id'],
            'professional_role_id' => isset($data['professional_role_id']) && $data['professional_role_id'] !== '' ? (int) $data['professional_role_id'] : null,
            'parent_id' => $data['parent_id'] ?? null,
            'name' => (string) $data['name'],
            'acronym' => $data['acronym'] ?? null,
            'license_id' => isset($data['license_id']) && $data['license_id'] !== '' ? (int) $data['license_id'] : null,
            'certification_category' => $data['certification_category'] ?? null,
            'offset_initial' => empty($data['offset_initial']) ? 0 : (int) $data['offset_initial'],
            'offset_current' => empty($data['offset_current']) ? 0 : (int) $data['offset_current'],
            'minimum_age' => isset($data['minimum_age']) && $data['minimum_age'] !== '' ? (string) $data['minimum_age'] : null,
            'confined_water_sessions' => isset($data['confined_water_sessions']) && $data['confined_water_sessions'] !== '' ? (string) $data['confined_water_sessions'] : null,
            'open_water_sessions' => isset($data['open_water_sessions']) && $data['open_water_sessions'] !== '' ? (string) $data['open_water_sessions'] : null,
            'theoretical_sessions' => isset($data['theoretical_sessions']) && $data['theoretical_sessions'] !== '' ? (string) $data['theoretical_sessions'] : null,
            'parents' => $data['parents'] ?? null,
            'certification_view' => $data['certification_view'] ?? null,
            // Pricing fields
            'is_available' => $isAvailable,
            'unit_value' => isset($data['unit_value']) && $data['unit_value'] !== '' ? (float) $data['unit_value'] : null,
            'unit_value_individual' => isset($data['unit_value_individual']) && $data['unit_value_individual'] !== '' ? (float) $data['unit_value_individual'] : null,
            'unit_value_entity' => isset($data['unit_value_entity']) && $data['unit_value_entity'] !== '' ? (float) $data['unit_value_entity'] : null,
            'tax_value' => isset($data['tax_value']) && $data['tax_value'] !== '' ? (float) $data['tax_value'] : null,
            'tax_percentage' => isset($data['tax_percentage']) && $data['tax_percentage'] !== '' ? (float) $data['tax_percentage'] : null,
            'moloni_reference' => $data['moloni_reference'] ?? null,
            // New pricing fields
            'digital_price' => isset($data['digital_price']) && $data['digital_price'] !== '' ? (float) $data['digital_price'] : null,
            'digital_plus_card_price' => isset($data['digital_plus_card_price']) && $data['digital_plus_card_price'] !== '' ? (float) $data['digital_plus_card_price'] : null,
            'requester_model' => $data['requester_model'] ?? null,
            'allow_entity_group_request' => $allowEntityGroupRequest,
            'requires_admin_validation' => $requiresAdminValidation,
            'roles' => $data['roles'] ?? null,
        ];

        \Log::debug('CertificationData::fromArray - Processed args:', [
            'is_available' => $isAvailable,
            'pricing' => [
                'unit_value' => $args['unit_value'],
                'unit_value_individual' => $args['unit_value_individual'],
                'unit_value_entity' => $args['unit_value_entity'],
            ],
        ]);

        // Ensure array types for parent_id and parents if they are set and not null,
        // consistent with constructor type hints (?array).
        // Validation rules should ideally ensure they are arrays if provided.
        if (isset($args['parent_id']) && ! is_array($args['parent_id'])) {
            // This case should ideally not happen if validation is correct ('nullable|array').
            // If it can be a single ID that needs to be wrapped, adjust as necessary.
            // For now, assuming validation provides an array or null.
        }
        if (isset($args['parents']) && ! is_array($args['parents'])) {
            // Similar to parent_id, assuming validation provides an array or null.
        }

        return new self(...$args);
    }
}
