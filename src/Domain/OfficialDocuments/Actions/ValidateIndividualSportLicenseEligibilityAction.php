<?php

namespace Domain\OfficialDocuments\Actions;

use App\Enums\OfficialDocumentTypeEnum;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;

class ValidateIndividualSportLicenseEligibilityAction
{
    private array $errors = [];

    public function __invoke(Individual $individual): array
    {
        $this->errors = [];

        // Validate individual fields
        $this->validateIndividualFields($individual);

        // TODO: Review the logic of the documents validation with the team
        // Validate documents
        // $this->validateDocuments($individual);

        return [
            'is_valid' => empty($this->errors),
            'errors' => $this->errors,
        ];
    }

    private function validateIndividualFields(Individual $individual): void
    {
        if (empty($individual->gender)) {
            $this->errors[] = [
                'code' => 'MISSING_GENDER',
                'message' => __('validation.missing_gender'),
            ];
        }

        if (! $individual->hasMedia('profile')) {
            $this->errors[] = [
                'code' => 'MISSING_PHOTO',
                'message' => __('validation.missing_photo'),
            ];
        }
    }

    private function validateDocuments(Individual $individual): void
    {
        // Only ADEL certificate validation is required
        $this->validateAdelCertificate($individual);
    }

    private function validateAdelCertificate(Individual $individual): void
    {
        $hasActiveAdel = OfficialDocument::where('individual_id', $individual->id)
            ->where('type', OfficialDocumentTypeEnum::ADELCertificate->value)
            ->where('status_class', ActiveOfficialDocumentState::class)
            ->exists();

        if (! $hasActiveAdel) {
            $this->errors[] = [
                'code' => 'MISSING_ACTIVE_ADEL',
                'message' => __('validation.missing_active_adel'),
            ];
        }
    }
}
