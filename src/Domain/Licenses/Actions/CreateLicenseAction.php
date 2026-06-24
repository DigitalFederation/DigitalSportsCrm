<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\DataTransferObject\LicenseData;
use Domain\Licenses\Models\License;
use Support\UtilityMethods;

class CreateLicenseAction
{
    public function __invoke(LicenseData $data)
    {
        $licenseData = (array) $data;

        // Extract required certifications if present
        $requiredCertifications = $licenseData['required_certifications'] ?? [];
        unset($licenseData['required_certifications']);

        // Extract roles if present
        $roles = $licenseData['roles'] ?? [];
        unset($licenseData['roles']);

        // Extract federation IDs if present
        $federationIds = $licenseData['federation_ids'] ?? [];
        unset($licenseData['federation_ids']);

        // Extract sport IDs if present
        $sportIds = $licenseData['sport_ids'] ?? [];
        unset($licenseData['sport_ids']);

        // Set legacy sport_id from first sport
        $licenseData['sport_id'] = ! empty($sportIds) ? $sportIds[0] : null;

        $license = License::create($licenseData);

        // Upload Image
        if (! empty($data->logo)) {
            UtilityMethods::addUploadedImageToMediaCollection($license, 'profile', $data->logo);
        }

        // Sync required certifications
        if (! empty($requiredCertifications)) {
            $syncData = [];
            foreach ($requiredCertifications as $certificationId) {
                // Set requester_type based on the license requester_model
                $requesterType = null;
                if ($data->requester_model && in_array('Individual', $data->requester_model)) {
                    $requesterType = \Domain\Individuals\Models\Individual::class;
                }
                $syncData[$certificationId] = ['requester_type' => $requesterType];
            }
            $license->requiredCertifications()->sync($syncData);
        }

        // Sync roles
        if (! empty($roles)) {
            $license->roles()->sync($roles);
        }

        // Sync federations
        if (! empty($federationIds)) {
            $license->federations()->sync($federationIds);
        }

        // Sync sports
        if (! empty($sportIds)) {
            $license->sports()->sync($sportIds);
        }

        return $license;
    }
}
