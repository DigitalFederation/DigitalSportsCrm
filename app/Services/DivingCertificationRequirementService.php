<?php

namespace App\Services;

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DivingCertificationRequirementService
{
    /**
     * Check if an individual meets the certification requirements for a specific license as technical director.
     */
    public function individualMeetsCertificationRequirements(Individual $individual, License $license): bool
    {
        // Get required certification levels for this license as technical director
        $requiredCertificationLevels = $this->getRequiredCertificationLevels($license);

        if ($requiredCertificationLevels->isEmpty()) {
            // No specific requirements, individual is eligible
            return true;
        }

        // Get individual's active diving certifications with their national certification levels
        $individualCertificationLevels = $this->getIndividualCertificationLevels($individual);

        // Check if individual has any of the required certification levels
        foreach ($requiredCertificationLevels as $requiredLevel) {
            if ($individualCertificationLevels->contains($requiredLevel)) {
                return true;
            }
        }

        // No matching national certification level found
        return false;
    }

    /**
     * Get required certification levels for a license as technical director.
     */
    public function getRequiredCertificationLevels(License $license): Collection
    {
        return DB::table('license_required_certifications')
            ->where('license_id', $license->id)
            ->where('requester_type', 'technical_director')
            ->whereNotNull('certification_level')
            ->pluck('certification_level');
    }

    /**
     * Get an individual's active diving certification levels.
     */
    public function getIndividualCertificationLevels(Individual $individual): Collection
    {
        return DivingProfessionalCertification::where('individual_id', $individual->id)
            ->where('status_class', ActiveDivingCertificationState::class)
            ->whereNotNull('national_equivalency')
            ->pluck('national_equivalency');
    }

    /**
     * Check if individual has valid international certifications for the license.
     * This maintains compatibility with existing international certification checking.
     */
    public function individualHasValidCMAScertifications(Individual $individual, License $license): bool
    {
        // Check if individual has active international certifications
        $hasActiveCMAScertifications = $individual->certificationsAttributed()
            ->certificationAttributedStatus('active')
            ->whereHas('certification', function ($query) {
                $query->whereHas('committee', function ($commQ) {
                    $commQ->where('code', 'DIVING');
                });
            })
            ->exists();

        return $hasActiveCMAScertifications;
    }

    /**
     * Get individuals who meet certification requirements for a license.
     */
    public function getQualifiedIndividuals(License $license, int $entityId): Collection
    {
        // Get all diving professionals associated with the entity
        $potentialDirectors = Individual::whereHas('professionalRoleEntities', function ($query) use ($entityId) {
            $query->where('entity_id', $entityId)
                ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class)
                ->whereHas('professionalRole', function ($roleQuery) {
                    $roleQuery->where('role', 'DIVINGPROFESSIONAL')
                        ->where('committee_id', function ($q) {
                            $q->select('id')
                                ->from('committee')
                                ->where('code', 'DIVING');
                        });
                });
        })->get();

        // Filter individuals who meet certification requirements
        return $potentialDirectors->filter(function ($individual) use ($license) {
            return $this->individualMeetsCertificationRequirements($individual, $license);
        });
    }

    /**
     * Get missing certification levels for an individual for a specific license.
     */
    public function getMissingCertificationLevels(Individual $individual, License $license): Collection
    {
        $requiredLevels = $this->getRequiredCertificationLevels($license);
        $individualLevels = $this->getIndividualCertificationLevels($individual);

        return $requiredLevels->diff($individualLevels);
    }

    /**
     * Get human-readable certification level names.
     */
    public function getCertificationLevelDisplayNames(): array
    {
        return [
            'diver_level_3' => __('diving.diver_level_3_dive_leader'),
            'instructor_level_1' => __('diving.instructor_level_1'),
            'instructor_level_2' => __('diving.instructor_level_2'),
            'instructor_level_3' => __('diving.instructor_level_3'),
            'first_aid_bls_oxygen' => __('diving.first_aid_bls_oxygen'),
            'compressor_operator' => __('diving.compressor_operator'),
        ];
    }

    /**
     * Get formatted requirements text for display.
     */
    public function getFormattedRequirementsText(License $license): string
    {
        $requiredLevels = $this->getRequiredCertificationLevels($license);
        $displayNames = $this->getCertificationLevelDisplayNames();

        if ($requiredLevels->isEmpty()) {
            return __('diving.no_specific_certification_requirements');
        }

        $formattedLevels = $requiredLevels->map(function ($level) use ($displayNames) {
            return $displayNames[$level] ?? $level;
        })->toArray();

        return __('diving.required_certifications') . ': ' . implode(', ', $formattedLevels);
    }
}
