<?php

namespace Domain\Licenses\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Support\Facades\Cache;

class FilterLicensesByCertificationsAndRolesAction
{
    public function __invoke($licenses, $individual)
    {
        $restrictedRolesCacheKey = "restricted_roles_{$individual->id}";

        // Do to limitations on the available certifications, we need to filter the licenses
        // Fetch the ProfessionalRole IDs for Coach and Judge
        // Cache restricted roles
        $restrictedRoles = Cache::remember($restrictedRolesCacheKey, 60, function () {
            return ProfessionalRole::whereIn('role', ['COACH', 'JUDGE'])->pluck('id');
        });

        // Fetch the IDs for roles for which the individual has valid certifications
        $validCertificationRoles = CertificationAttributed::where('individual_id', $individual->id)
            ->where('status_class', ActiveCertificationAttributedState::class)
            ->with('certification')
            ->get()
            ->unique();

        // Get the individual's active certification IDs
        $individualCertificationIds = CertificationAttributed::where('individual_id', $individual->id)
            ->where('status_class', ActiveCertificationAttributedState::class)
            ->pluck('certification_id')
            ->unique();

        // Filter the licenses
        $licensesFiltered = $licenses->filter(function ($license) use ($restrictedRoles, $validCertificationRoles, $individualCertificationIds) {
            // First check mandatory certifications
            // Get required certifications for this license (for Individual requester type)
            $requiredCertifications = $license->requiredCertificationsForRequester(Individual::class)->pluck('certification_id');

            // If there are required certifications, the individual must have AT LEAST ONE of them (OR logic)
            // Example: A coach with Grade I OR Grade II OR Grade III can request the coach license
            if ($requiredCertifications->isNotEmpty()) {
                $hasAtLeastOne = $requiredCertifications->some(function ($certId) use ($individualCertificationIds) {
                    return $individualCertificationIds->contains($certId);
                });

                if (! $hasAtLeastOne) {
                    return false;
                }
            }

            // Then check role-based restrictions (existing logic for COACH/JUDGE)
            $licenseRoleId = $license->professional_role_id;

            $validRoles = $validCertificationRoles->map(function ($certificationAttributed) {
                return $certificationAttributed->certification->professional_role_id;
            });

            // If the role is restricted and the individual does not have a valid certification for it, remove it
            if ($restrictedRoles->contains($licenseRoleId) && ! $validRoles->contains($licenseRoleId)) {
                return false;
            }

            return true;
        });

        // Filter the licenses
        return $licensesFiltered;
    }
}
