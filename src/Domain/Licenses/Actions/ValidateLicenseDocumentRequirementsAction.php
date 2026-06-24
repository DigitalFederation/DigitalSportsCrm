<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;

class ValidateLicenseDocumentRequirementsAction
{
    private array $errors = [];

    /**
     * Validate if the owner (Individual or Entity) has all required documents for a license
     *
     * @param  License  $license  The license to check requirements for
     * @param  Individual|Entity  $owner  The owner requesting the license
     * @return array ['is_valid' => bool, 'errors' => array, 'missing_documents' => array]
     */
    public function __invoke(License $license, $owner): array
    {
        $this->errors = [];
        $missingDocuments = [];

        // Entities do NOT require documents - document requirements only apply to Individuals
        if ($owner instanceof Entity) {
            return [
                'is_valid' => true,
                'errors' => [],
                'missing_documents' => [],
            ];
        }

        // If license doesn't require documents, it's valid
        if (! $license->requires_official_documents || empty($license->required_document_types)) {
            return [
                'is_valid' => true,
                'errors' => [],
                'missing_documents' => [],
            ];
        }

        // Only Individuals own personal documents. Entities returned early above;
        // guard any other owner type so misuse fails cleanly instead of erroring on
        // ->officialDocuments().
        if (! $owner instanceof Individual) {
            $this->errors[] = [
                'code' => 'INVALID_OWNER_TYPE',
                'message' => __('validation.invalid_owner_type'),
            ];

            return [
                'is_valid' => false,
                'errors' => $this->errors,
                'missing_documents' => [],
            ];
        }

        // Individual documents are stored against the individual_id column, so query
        // through the officialDocuments() relationship — consistent with
        // InsurancePlan::individualHasRequiredDocument() and the rest of the system.
        // The legacy owner_type/owner_id polymorphic columns are never populated by the
        // personal upload flow, so a polymorphic query reported every document as
        // missing and blocked individual license purchases.
        foreach ($license->required_document_types as $documentType) {
            $hasActiveDocument = $owner->officialDocuments()
                ->where('type', $documentType)
                ->where('status_class', ActiveOfficialDocumentState::class)
                ->where(function ($query) {
                    $query->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>', now());
                })
                ->exists();

            if (! $hasActiveDocument) {
                $missingDocuments[] = $documentType;
                $this->errors[] = [
                    'code' => 'MISSING_REQUIRED_DOCUMENT',
                    'document_type' => $documentType,
                    'message' => __('validation.missing_required_document', [
                        'document' => \App\Enums\OfficialDocumentTypeEnum::toString($documentType),
                    ]),
                ];
            }
        }

        return [
            'is_valid' => empty($this->errors),
            'errors' => $this->errors,
            'missing_documents' => $missingDocuments,
        ];
    }
}
