<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class EditIndividualProfileAction
{
    public function __invoke(Individual $individual, array $data): Individual
    {

        $updated = $individual->update([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'first_name_latin' => $data['first_name_latin'] ?? null,
            'last_name_latin' => $data['last_name_latin'] ?? null,
            'native_name' => $data['native_name'],
            'country_id' => $data['country_id'],
            'birthdate' => $data['birthdate'],
            'gender' => $data['gender'],
            'district_id' => $data['district_id'] === 'outside_portugal' ? null : $data['district_id'],
            'vat_number' => $data['vat_number'],
            'address' => $data['address'] ?? null,
            'location' => $data['location'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'doc_ref_type' => $data['doc_ref_type'],
            'doc_ref' => $data['doc_ref'],
            'doc_ref_validation_date' => $data['doc_ref_validation_date'],
            'national_federation_number' => $data['national_federation_number'] ?? null,
            'facebook_url' => $data['facebook_url'] ?? null,
            'x_url' => $data['x_url'] ?? null,
            'instagram_url' => $data['instagram_url'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'visible_in_coach_registry' => (bool) ($data['visible_in_coach_registry'] ?? false),
            'visible_in_technical_official_registry' => (bool) ($data['visible_in_technical_official_registry'] ?? false),
            'visible_in_diving_professional_registry' => (bool) ($data['visible_in_diving_professional_registry'] ?? false),
        ]);

        if ($updated === false) {
            throw new \RuntimeException(__('individual.error_saving_data'));
        }

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            try {
                // Verify file exists and is readable
                if (! $data['logo']->isValid()) {
                    throw new \RuntimeException(__('individual.invalid_file_upload'));
                }
                $path = $data['logo']->getRealPath();

                $individual->clearMediaCollection('profile');

                if (! file_exists($path)) {
                    throw new \Exception("Upload file not found at path: $path");
                }

                $individual->addMedia($data['logo'])
                    ->preservingOriginal()
                    ->sanitizingFileName(function ($fileName) {
                        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                    })
                    ->toMediaCollection('profile');

            } catch (\Exception $e) {
                Log::error('Media upload failed: ' . $e->getMessage());
                throw new \Exception(__('individual.image_upload_failed'));
            }
        }

        return $individual;
    }
}
