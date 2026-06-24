<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\DataTransferObject\LicenseData;
use Domain\Licenses\Models\License;
use Illuminate\Http\UploadedFile;
use Support\UtilityMethods;

class EditLicenseAction
{
    public function __invoke(LicenseData $data, int $id)
    {
        $license = License::withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
            ->find($id);

        if (! $license) {
            throw new \Exception('License not found');
        }

        // 1. Prepare data for database update (exclude logo and required_certifications)
        $updateData = (array) $data;
        unset($updateData['logo']); // Remove logo from data to be saved in DB

        // Extract required certifications if present
        $requiredCertifications = $updateData['required_certifications'] ?? [];
        unset($updateData['required_certifications']);

        // Extract roles if present
        $roles = $updateData['roles'] ?? [];
        unset($updateData['roles']);

        // Extract federation IDs if present
        $federationIds = $updateData['federation_ids'] ?? [];
        unset($updateData['federation_ids']);

        // Extract sport IDs if present
        $sportIds = $updateData['sport_ids'] ?? [];
        unset($updateData['sport_ids']);

        // Set legacy sport_id from first sport
        $updateData['sport_id'] = ! empty($sportIds) ? $sportIds[0] : null;

        // 2. Update the license model
        $updated = $license->update($updateData);

        // 3. Sync required certifications and roles
        if ($updated) {
            // Sync certifications
            $syncData = [];
            if (! empty($requiredCertifications)) {
                foreach ($requiredCertifications as $certificationId) {
                    // Set requester_type based on the license requester_model
                    $requesterType = null;
                    if ($data->requester_model && in_array('Individual', $data->requester_model)) {
                        $requesterType = \Domain\Individuals\Models\Individual::class;
                    }
                    $syncData[$certificationId] = ['requester_type' => $requesterType];
                }
            }
            $license->requiredCertifications()->sync($syncData);

            // Sync roles
            $license->roles()->sync($roles);

            // Sync federations
            $license->federations()->sync($federationIds);

            // Sync sports
            $license->sports()->sync($sportIds);
        }

        // 3. Handle logo upload if present and update was successful (or handle partial success)
        if ($updated && ! empty($data->logo) && $data->logo instanceof UploadedFile) {
            try {
                UtilityMethods::addUploadedImageToMediaCollection($license, 'logo', $data->logo);
            } catch (\Exception $e) {
                // Log the image upload error specifically
                \Log::error('Error saving license logo for ID ' . $id . ': ' . $e->getMessage());
                // Decide if this should revert the update or just report error
                // For now, let's proceed but the update might be considered partially failed
            }
        }

        if ($updated) {
            activity('License')
                ->performedOn($license)
                ->event('updated')
                // Use the actual updated data, not the original DTO cast
                ->withProperties($license->getChanges()) // Log only changed attributes
                ->log('License updated');
        }

        return $updated;
    }
}
