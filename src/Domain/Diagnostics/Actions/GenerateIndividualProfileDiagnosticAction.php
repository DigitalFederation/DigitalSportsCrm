<?php

declare(strict_types=1);

namespace Domain\Diagnostics\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Diagnostics\Data\IndividualProfileDiagnostic;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\States\ActiveLicenseAttributedState;

class GenerateIndividualProfileDiagnosticAction
{
    private const STATE_SUFFIXES = [
        'IndividualFederationState',
        'IndividualEntityState',
        'CertificationAttributedState',
    ];

    public function execute(Individual $individual): IndividualProfileDiagnostic
    {
        $individual->load([
            'individualFederations.federation',
            'individualEntities.entity',
            'professionalRoles',
            'licenses.license',
            'licenses.federation',
            'certificationsAttributed.certification.professionalRole',
            'entityAthletes.sport',
            'professionalRoleEntities.professionalRole',
        ]);

        $federationMemberships = $this->getFederationMemberships($individual);
        $entityMemberships = $this->getEntityMemberships($individual);
        $professionalRoles = $this->getProfessionalRoles($individual);
        $activeLicenses = $this->getActiveLicenses($individual);
        $certifications = $this->getCertifications($individual);
        $quickStatus = $this->generateQuickStatus($individual, $federationMemberships, $entityMemberships, $professionalRoles, $certifications);

        return new IndividualProfileDiagnostic(
            individual: $individual,
            federationMemberships: $federationMemberships,
            entityMemberships: $entityMemberships,
            professionalRoles: $professionalRoles,
            activeLicenses: $activeLicenses,
            certifications: $certifications,
            quickStatus: $quickStatus
        );
    }

    private function getFederationMemberships(Individual $individual): array
    {
        return $individual->individualFederations->map(fn ($membership) => [
            'id' => $membership->federation?->id,
            'name' => $membership->federation?->name ?? __('diagnostics.unknown_federation'),
            'status' => $this->extractStatusName($membership->status_class),
            'is_active' => $membership->status_class === ActiveIndividualFederationState::class,
            'is_local' => $membership->federation?->is_local ?? false,
            'is_default' => $membership->federation?->is_default_federation ?? false,
            'since' => $membership->created_at?->format('Y-m-d'),
        ])->toArray();
    }

    private function getEntityMemberships(Individual $individual): array
    {
        return $individual->individualEntities->map(function ($membership) use ($individual) {
            $sports = $individual->entityAthletes
                ->where('entity_id', $membership->entity_id)
                ->pluck('sport.name')
                ->filter()
                ->implode(', ');

            return [
                'id' => $membership->entity?->id,
                'name' => $membership->entity?->name ?? __('diagnostics.unknown_entity'),
                'status' => $this->extractStatusName($membership->status_class),
                'is_active' => $membership->status_class === ActiveIndividualEntityState::class,
                'sports' => $sports ?: '-',
            ];
        })->toArray();
    }

    private function getProfessionalRoles(Individual $individual): array
    {
        $roles = [];

        // Direct professional roles (from individual_professional_role)
        foreach ($individual->professionalRoles as $role) {
            $roles[] = [
                'role' => strtoupper($role->role),
                'display_name' => $role->name ?? $role->role,
                'source' => __('diagnostics.source_direct_assignment'),
                'status' => 'Active',
            ];
        }

        // Professional roles from entity assignments
        foreach ($individual->professionalRoleEntities as $entityRole) {
            $roles[] = [
                'role' => strtoupper($entityRole->professionalRole?->role ?? ''),
                'display_name' => $entityRole->professionalRole?->name ?? $entityRole->professionalRole?->role,
                'source' => __('diagnostics.source_entity_assignment'),
                'status' => 'Active',
            ];
        }

        return $roles;
    }

    private function getActiveLicenses(Individual $individual): array
    {
        return $individual->licenses
            ->filter(fn ($license) => $license->status_class === ActiveLicenseAttributedState::class)
            ->map(function ($licenseAttr) {
                return [
                    'id' => $licenseAttr->id,
                    'name' => $licenseAttr->license?->name ?? __('diagnostics.unknown_license'),
                    'status' => 'Active',
                    'expires' => $licenseAttr->current_term_ends_at?->format('Y-m-d'),
                    'federation' => $licenseAttr->federation?->name ?? '-',
                ];
            })->values()->toArray();
    }

    private function getCertifications(Individual $individual): array
    {
        return $individual->certificationsAttributed->map(function ($certAttr) {
            $grantsRole = $certAttr->certification?->professionalRole?->role;
            $isPending = $certAttr->status_class === PendingCertificationAttributedState::class;

            return [
                'id' => $certAttr->id,
                'name' => $certAttr->certification?->name ?? __('diagnostics.unknown_certification'),
                'status' => $this->extractStatusName($certAttr->status_class),
                'grants_role' => $grantsRole,
                'is_active' => $certAttr->status_class === ActiveCertificationAttributedState::class,
                'action_needed' => ($isPending && $grantsRole) ? __('diagnostics.action_activate_certification') : null,
            ];
        })->toArray();
    }

    private function generateQuickStatus(
        Individual $individual,
        array $federationMemberships,
        array $entityMemberships,
        array $professionalRoles,
        array $certifications
    ): array {
        $federations = collect($federationMemberships);
        $entities = collect($entityMemberships);
        $roles = collect($professionalRoles);
        $certs = collect($certifications);

        $hasActiveFederationMembership = $federations->contains('is_active', true);
        $hasActiveEntityMembership = $entities->contains('is_active', true);

        // Check for specific professional roles
        $roleTypes = $roles->pluck('role')->map(fn ($r) => strtoupper($r))->all();
        $hasCoachRole = in_array('COACH', $roleTypes);
        $hasRefereeRole = in_array('TECHNICAL_OFFICIAL', $roleTypes);

        // Check for entity athlete registration
        $isRegisteredAsAthlete = $individual->entityAthletes->isNotEmpty();

        // Check for technical official (referee/judge) certification status
        $refereeCerts = $certs->filter(fn ($c) => strtoupper($c['grants_role'] ?? '') === 'TECHNICAL_OFFICIAL');
        $hasActiveRefereeCert = $refereeCerts->contains('is_active', true);
        $hasPendingRefereeCert = $refereeCerts->contains('status', 'Pending');

        return [
            'athlete' => $this->getAthleteStatus($hasActiveFederationMembership, $hasActiveEntityMembership, $isRegisteredAsAthlete),
            'coach' => $this->getCoachStatus($hasActiveFederationMembership, $hasActiveEntityMembership, $hasCoachRole),
            'referee' => $this->getRefereeStatus($hasActiveFederationMembership, $hasRefereeRole, $hasActiveRefereeCert, $hasPendingRefereeCert),
            'official' => $this->getOfficialStatus($hasActiveFederationMembership, $hasActiveEntityMembership),
        ];
    }

    private function getAthleteStatus(bool $hasActiveFederationMembership, bool $hasActiveEntityMembership, bool $isRegisteredAsAthlete): array
    {
        if (! $hasActiveFederationMembership) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_active_federation')];
        }
        if (! $hasActiveEntityMembership) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_active_entity')];
        }
        if (! $isRegisteredAsAthlete) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_not_registered_athlete')];
        }

        return ['eligible' => true, 'reason' => __('diagnostics.reason_registered_athlete')];
    }

    private function getCoachStatus(bool $hasActiveFederationMembership, bool $hasActiveEntityMembership, bool $hasCoachRole): array
    {
        if (! $hasActiveFederationMembership) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_active_federation')];
        }
        if (! $hasActiveEntityMembership) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_active_entity')];
        }
        if (! $hasCoachRole) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_coach_role')];
        }

        return ['eligible' => true, 'reason' => __('diagnostics.reason_has_coach_role')];
    }

    private function getRefereeStatus(bool $hasActiveFederationMembership, bool $hasRefereeRole, bool $hasActiveRefereeCert, bool $hasPendingRefereeCert): array
    {
        if (! $hasActiveFederationMembership) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_active_federation')];
        }
        if (! $hasRefereeRole) {
            if ($hasPendingRefereeCert) {
                return ['eligible' => false, 'reason' => __('diagnostics.reason_cert_pending_activation')];
            }
            if (! $hasActiveRefereeCert) {
                return ['eligible' => false, 'reason' => __('diagnostics.reason_no_referee_cert')];
            }

            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_referee_role')];
        }

        return ['eligible' => true, 'reason' => __('diagnostics.reason_has_referee_role')];
    }

    private function getOfficialStatus(bool $hasActiveFederationMembership, bool $hasActiveEntityMembership): array
    {
        if (! $hasActiveFederationMembership && ! $hasActiveEntityMembership) {
            return ['eligible' => false, 'reason' => __('diagnostics.reason_no_active_membership')];
        }

        return ['eligible' => true, 'reason' => __('diagnostics.reason_active_member')];
    }

    private function extractStatusName(string $statusClass): string
    {
        $baseName = class_basename($statusClass);

        foreach (self::STATE_SUFFIXES as $suffix) {
            $baseName = str_replace($suffix, '', $baseName);
        }

        return $baseName;
    }
}
