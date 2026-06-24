<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\DataTransferObject\CertificationData;
use Domain\Certifications\Models\Certification;

class EditCertificationAction
{
    public function __invoke(CertificationData $certificationData, int $certificationId): bool
    {
        \Log::debug('EditCertificationAction - Received data:', [
            'certification_id' => $certificationId,
            'all_data' => (array) $certificationData,
        ]);

        $certification = Certification::find($certificationId);

        if (! $certification) {
            \Log::error('EditCertificationAction - Certification not found', ['certification_id' => $certificationId]);

            return false;
        }

        $updated = $certification->update([
            'committee_id' => $certificationData->committee_id,
            'professional_role_id' => $certificationData->professional_role_id,
            'name' => $certificationData->name,
            'acronym' => $certificationData->acronym,
            'license_id' => $certificationData->license_id,
            'certification_category' => $certificationData->certification_category,
            'offset_initial' => $certificationData->offset_initial,
            'offset_current' => $certificationData->offset_current,
            'minimum_age' => $certificationData->minimum_age,
            'confined_water_sessions' => $certificationData->confined_water_sessions,
            'open_water_sessions' => $certificationData->open_water_sessions,
            'theoretical_sessions' => $certificationData->theoretical_sessions,
            // Pricing fields
            'is_available' => $certificationData->is_available ?? true,
            'unit_value' => $certificationData->unit_value,
            'unit_value_individual' => $certificationData->unit_value_individual,
            'unit_value_entity' => $certificationData->unit_value_entity,
            'tax_value' => $certificationData->tax_value,
            'tax_percentage' => $certificationData->tax_percentage,
            'moloni_reference' => $certificationData->moloni_reference,
            // New pricing fields
            'digital_price' => $certificationData->digital_price,
            'digital_plus_card_price' => $certificationData->digital_plus_card_price,
            'requester_model' => $certificationData->requester_model,
            'allow_entity_group_request' => $certificationData->allow_entity_group_request ?? false,
            'requires_admin_validation' => $certificationData->requires_admin_validation ?? false,
        ]);

        if ($updated && $certificationData->parent_id !== null) {
            $certification->parents()->sync($certificationData->parent_id);
        }

        if ($updated && $certificationData->roles !== null) {
            $certification->roles()->sync($certificationData->roles);
        }

        if ($updated) {
            activity('Certification')
                ->performedOn($certification)
                ->event('updated')
                ->withProperties((array) $certificationData)
                ->log('Certification updated: '.$certification->name);
        }

        return $updated;
    }
}
