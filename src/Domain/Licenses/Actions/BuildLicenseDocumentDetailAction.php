<?php

namespace Domain\Licenses\Actions;

use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;

class BuildLicenseDocumentDetailAction
{
    public function __invoke(iterable $attributedLicenses): array
    {
        $data = [];

        foreach ($attributedLicenses as $attributedRecord) {
            // Handle both array and LicenseAttributed object formats
            if ($attributedRecord instanceof LicenseAttributed) {
                // Load license relationship if not already loaded
                if (! $attributedRecord->relationLoaded('license')) {
                    $attributedRecord->load('license');
                }

                $license = $attributedRecord->license;

                \Log::info('BuildLicenseDocumentDetailAction: Processing license attributed', [
                    'license_attributed_id' => $attributedRecord->id,
                    'has_license' => $license !== null,
                    'license_id' => $license?->id,
                    'license_name' => $license?->name,
                    'requester_model_type' => $attributedRecord->requester_model_type,
                ]);

                if (! $license) {
                    \Log::error('BuildLicenseDocumentDetailAction: License not found for attributed record', [
                        'license_attributed_id' => $attributedRecord->id,
                    ]);

                    continue; // Skip this record
                }

                $unitValue = $this->determineUnitValue($license, $attributedRecord->requester_model_type);

                // Fallback: if unit value is still null, use the total_value from license_attributed
                if ($unitValue === null && $attributedRecord->total_value) {
                    \Log::warning('BuildLicenseDocumentDetailAction: Using fallback total_value from license_attributed', [
                        'license_attributed_id' => $attributedRecord->id,
                        'total_value' => $attributedRecord->total_value,
                        'requester_model_type' => $attributedRecord->requester_model_type,
                    ]);
                    $unitValue = $attributedRecord->total_value;
                }

                \Log::info('BuildLicenseDocumentDetailAction: Final unit value', [
                    'unit_value' => $unitValue,
                    'license_unit_value' => $license->unit_value,
                    'license_unit_value_entity' => $license->unit_value_entity,
                    'license_unit_value_individual' => $license->unit_value_individual,
                    'requester_model_type' => $attributedRecord->requester_model_type,
                    'total_value_from_attributed' => $attributedRecord->total_value,
                ]);

                $holderName = ! empty($attributedRecord->holder_name) ? ' - ' . $attributedRecord->holder_name : '';
                $description = $license->name . $holderName;

                $data[] = DocumentDetailData::fromArray([
                    'owner_id' => $attributedRecord->id,
                    'owner_type' => LicenseAttributed::class,
                    'unit_value' => $unitValue,
                    'tax_value' => $license->tax_value,
                    'tax_percentage' => $license->tax_percentage,
                    'description' => $description,
                    'reference' => $license->moloni_reference,
                ]);
            } else {
                // Legacy array format
                $unitValue = $this->determineUnitValue($attributedRecord['license'], $attributedRecord['requester_model_type']);

                // Check if holder_name is set and not empty
                $holderName = isset($attributedRecord['holder_name']) && ! empty($attributedRecord['holder_name'])
                    ? ' - ' . $attributedRecord['holder_name']
                    : '';
                // Include holder's name in the document description
                $description = $attributedRecord['license']['name'] . $holderName;

                $data[] = DocumentDetailData::fromArray([
                    'owner_id' => $attributedRecord['id'],
                    'owner_type' => LicenseAttributed::class,
                    'unit_value' => $unitValue,
                    'tax_value' => $attributedRecord['license']['tax_value'],
                    'tax_percentage' => $attributedRecord['license']['tax_percentage'],
                    'description' => $description,
                    'reference' => $attributedRecord['license']['moloni_reference'] ?? null,
                ]);
            }
        }

        return $data;
    }

    protected function determineUnitValue($license, $requesterModel)
    {
        // Normalize the requester model to handle both full class names and morph aliases
        $normalizedModel = $this->normalizeRequesterModel($requesterModel);

        \Log::info('BuildLicenseDocumentDetailAction: Determining unit value', [
            'original_requester_model' => $requesterModel,
            'normalized_model' => $normalizedModel,
            'is_object' => is_object($license),
        ]);

        // Handle both array and License object formats
        if (is_object($license)) {
            switch ($normalizedModel) {
                case Entity::class:
                    $value = ($license->unit_value_entity && $license->unit_value_entity > 0)
                        ? $license->unit_value_entity
                        : $license->unit_value;
                    break;
                case Individual::class:
                    $value = ($license->unit_value_individual && $license->unit_value_individual > 0)
                        ? $license->unit_value_individual
                        : $license->unit_value;
                    break;
                default:
                    $value = $license->unit_value;
                    break;
            }

            \Log::info('BuildLicenseDocumentDetailAction: Unit value determined from object', [
                'unit_value' => $value,
                'unit_value_entity' => $license->unit_value_entity ?? null,
                'unit_value_individual' => $license->unit_value_individual ?? null,
                'unit_value_base' => $license->unit_value ?? null,
            ]);

            return $value;
        } else {
            // Legacy array format
            switch ($normalizedModel) {
                case Entity::class:
                    return $license['unit_value_entity'] ?? $license['unit_value'];
                case Individual::class:
                    return $license['unit_value_individual'] ?? $license['unit_value'];
                default:
                    return $license['unit_value'];
            }
        }
    }

    /**
     * Normalize the requester model to handle both full class names and morph aliases
     */
    protected function normalizeRequesterModel($requesterModel)
    {
        // Handle morph map aliases
        if ($requesterModel === 'entity') {
            return Entity::class;
        }

        if ($requesterModel === 'individual') {
            return Individual::class;
        }

        // Return as-is if it's already a full class name
        return $requesterModel;
    }
}
