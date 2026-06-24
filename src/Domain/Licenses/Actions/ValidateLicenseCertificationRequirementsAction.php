<?php

namespace Domain\Licenses\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;

class ValidateLicenseCertificationRequirementsAction
{
    /**
     * Validate if the purchaser has all required certifications for the license
     *
     * @param  Individual|Entity  $purchaser
     * @return array{is_valid: bool, missing_certifications: array}
     */
    public function __invoke(License $license, $purchaser): array
    {
        // Check if this license type is for individuals
        // If it's not an individual license type, no certification validation is needed
        if (! $license->type || ! $license->type->is_individual) {
            return [
                'is_valid' => true,
                'missing_certifications' => [],
            ];
        }

        // Only validate certifications for individual purchasers
        // Entities don't have certifications
        if ($purchaser instanceof Entity) {
            return [
                'is_valid' => true,
                'missing_certifications' => [],
            ];
        }

        // Get required certifications for this license
        $requiredCertifications = $license->requiredCertifications()->get();

        // If no certifications are required, validation passes
        if ($requiredCertifications->isEmpty()) {
            return [
                'is_valid' => true,
                'missing_certifications' => [],
            ];
        }

        // Get the individual's active certifications
        $individualCertificationIds = CertificationAttributed::where('individual_id', $purchaser->id)
            ->whereIn('status_class', [
                \Domain\Certifications\States\ActiveCertificationAttributedState::class,
                \Domain\Certifications\States\ProvisionalCertificationAttributedState::class,
            ]) // Only consider active/provisional certifications
            ->pluck('certification_id')
            ->toArray();

        // Check if the individual has AT LEAST ONE of the required certifications (OR logic)
        // Example: A coach with Grade I OR Grade II OR Grade III can request the coach license
        $requiredCertificationIds = $requiredCertifications->pluck('id')->toArray();
        $matchingCertifications = array_intersect($requiredCertificationIds, $individualCertificationIds);

        // Valid if the individual has at least one of the required certifications
        $isValid = ! empty($matchingCertifications);

        // If not valid, show all required certifications as options (not "missing" but "required options")
        $missingCertifications = [];
        if (! $isValid) {
            $missingCertifications = $requiredCertifications
                ->map(function ($cert) {
                    return [
                        'id' => $cert->id,
                        'name' => $cert->name,
                        'acronym' => $cert->acronym,
                    ];
                })
                ->toArray();
        }

        return [
            'is_valid' => $isValid,
            'missing_certifications' => $missingCertifications,
        ];
    }
}
