<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Illuminate\Support\Collection;

class GetEligibleMembersForLicenseAction
{
    private ValidateLicenseCertificationRequirementsAction $validateCertificationRequirementsAction;
    private ValidateLicenseDocumentRequirementsAction $validateDocumentRequirementsAction;

    public function __construct(
        ?ValidateLicenseCertificationRequirementsAction $validateCertificationRequirementsAction = null,
        ?ValidateLicenseDocumentRequirementsAction $validateDocumentRequirementsAction = null
    ) {
        $this->validateCertificationRequirementsAction = $validateCertificationRequirementsAction ?: new ValidateLicenseCertificationRequirementsAction;
        $this->validateDocumentRequirementsAction = $validateDocumentRequirementsAction ?: new ValidateLicenseDocumentRequirementsAction;
    }

    /**
     * Get eligibility information for entity members for a specific license
     *
     * @param  License  $license  The license to check eligibility for
     * @param  Entity  $entity  The entity whose members to check
     * @param  Collection  $individuals  Collection of individuals to check
     * @return Collection Returns collection with eligibility data for each member
     */
    public function __invoke(License $license, Entity $entity, Collection $individuals): Collection
    {
        // Ensure license has required relationships loaded
        if (! $license->relationLoaded('requiredCertifications')) {
            $license->load('requiredCertifications');
        }

        return $individuals->map(function ($individual) use ($license) {
            $missingCertifications = [];
            $missingDocuments = [];
            $eligibilityMessages = [];

            // Check certification requirements
            $certificationValidation = ($this->validateCertificationRequirementsAction)($license, $individual);
            if (! $certificationValidation['is_valid']) {
                $missingCertifications = $certificationValidation['missing_certifications'];

                $certNames = array_map(function ($cert) {
                    return $cert['acronym'] ? "{$cert['name']} ({$cert['acronym']})" : $cert['name'];
                }, $missingCertifications);

                if (! empty($certNames)) {
                    $eligibilityMessages[] = __('licenses.member_missing_certifications', [
                        'certifications' => implode(', ', $certNames),
                    ]);
                }
            }

            // Check document requirements
            $documentValidation = ($this->validateDocumentRequirementsAction)($license, $individual);
            if (! $documentValidation['is_valid']) {
                $missingDocuments = $documentValidation['missing_documents'];

                $docNames = array_map(function ($doc) {
                    return \App\Enums\OfficialDocumentTypeEnum::toString($doc);
                }, $missingDocuments);

                if (! empty($docNames)) {
                    $eligibilityMessages[] = __('licenses.member_missing_documents', [
                        'documents' => implode(', ', $docNames),
                    ]);
                }
            }

            // Check if individual has active affiliation
            $hasActiveAffiliation = $individual->hasActiveAffiliation();
            if (! $hasActiveAffiliation) {
                $eligibilityMessages[] = __('licenses.member_must_have_active_affiliation');
            }

            $isEligible = empty($missingCertifications) && empty($missingDocuments) && $hasActiveAffiliation;

            return [
                'individual' => $individual,
                'is_eligible' => $isEligible,
                'missing_certifications' => $missingCertifications,
                'missing_documents' => $missingDocuments,
                'has_active_affiliation' => $hasActiveAffiliation,
                'eligibility_message' => implode('. ', $eligibilityMessages),
            ];
        });
    }

    /**
     * Get license requirements summary
     *
     * @param  License  $license  The license to get requirements for
     * @return array Returns array with formatted requirements
     */
    public function getLicenseRequirements(License $license): array
    {
        // Ensure license has required relationships loaded
        if (! $license->relationLoaded('requiredCertifications')) {
            $license->load('requiredCertifications');
        }

        $requirements = [
            'certifications' => [],
            'documents' => [],
            'has_requirements' => false,
        ];

        // Get required certifications
        if ($license->requiredCertifications->isNotEmpty()) {
            $requirements['certifications'] = $license->requiredCertifications->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'name' => $cert->name,
                    'acronym' => $cert->acronym,
                    'display' => $cert->acronym ? "{$cert->name} ({$cert->acronym})" : $cert->name,
                ];
            })->toArray();
            $requirements['has_requirements'] = true;
        }

        // Get required documents
        if ($license->requires_official_documents && ! empty($license->required_document_types)) {
            $requirements['documents'] = array_map(function ($docType) {
                return [
                    'type' => $docType,
                    'name' => \App\Enums\OfficialDocumentTypeEnum::toString($docType),
                ];
            }, $license->required_document_types);
            $requirements['has_requirements'] = true;
        }

        return $requirements;
    }
}
