<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\Certification;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;

class GetCertificationsFromInstructorAction
{
    public function __invoke(?Individual $individual, int $federation_id, ?string $committee_code = null)
    {
        $professional_role_code = $this->getProfessionalRoleCodeFromCommitteeCode($committee_code);

        // Return all Certifications associated with above Individual
        if (! empty($individual)) {
            // For instructor certifications, check the main federation and all its children
            // Instructor certifications can be issued by child or modality federations.
            $mainFederation = Federation::where('is_default_federation', true)->first();
            $federationIds = [];
            if ($mainFederation) {
                $federationIds[] = $mainFederation->id;
                // Include all child federations.
                $childFederationIds = Federation::where('parent_id', $mainFederation->id)->pluck('id')->toArray();
                $federationIds = array_merge($federationIds, $childFederationIds);
            } else {
                $federationIds[] = $federation_id;
            }

            // Get all Certifications ID that are attributed to the Individual and are Active
            $parent_certifications = Certification::whereHas('certificationsAttributed', function ($q) use ($individual, $federationIds) {
                return $q->where('individual_id', $individual->id)
                    ->whereIn('federation_id', $federationIds)
                    ->where('status_class', ActiveCertificationAttributedState::class);
            })->whereHas('professionalRole', function ($query) use ($professional_role_code) {
                return $query->where('code', $professional_role_code);
            })->pluck('id');

            // Get all Certifications that are children of the instructor's certifications
            // Use whereKey() to filter parent certifications by their IDs (handles table aliasing correctly)
            $certifications = Certification::whereHas('parents', function ($q) use ($parent_certifications) {
                return $q->whereKey($parent_certifications);
            })->get();
        }

        return $certifications ?? null;
    }

    /**
     * Resolve the professional-role code whose certifications a committee's
     * instructors issue, from config/committees.php (`instructor_role_code`).
     * Matches the committee code case-insensitively so callers may pass either
     * the stored code or a lowercased variant.
     */
    private function getProfessionalRoleCodeFromCommitteeCode(?string $committee_code): ?string
    {
        if (! $committee_code) {
            return null;
        }

        foreach (config('committees.list', []) as $committee) {
            if (strcasecmp($committee['code'] ?? '', $committee_code) === 0) {
                return $committee['instructor_role_code'] ?? null;
            }
        }

        return null;
    }
}
