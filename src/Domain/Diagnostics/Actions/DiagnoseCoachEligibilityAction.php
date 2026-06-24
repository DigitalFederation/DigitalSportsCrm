<?php

declare(strict_types=1);

namespace Domain\Diagnostics\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Diagnostics\Data\DiagnosticResult;
use Domain\Diagnostics\Data\EligibilityCheck;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;

class DiagnoseCoachEligibilityAction
{
    public function execute(Individual $individual, Event $event, ?Competition $competition = null): DiagnosticResult
    {
        $individual->load([
            'individualFederations.federation',
            'individualEntities.entity',
            'professionalRoles',
            'professionalRoleEntities.professionalRole',
            'certificationsAttributed.certification',
        ]);

        $checks = [];
        $suggestions = [];
        $failedChecks = [];

        // Check 1: Active Federation Membership
        $federationCheck = $this->checkFederationMembership($individual);
        $checks[] = $federationCheck;
        if (! $federationCheck->passed) {
            $failedChecks[] = $federationCheck->key;
            if ($federationCheck->suggestion) {
                $suggestions[] = $federationCheck->suggestion;
            }
        }

        // Check 2: Active Entity Membership
        $entityCheck = $this->checkEntityMembership($individual);
        $checks[] = $entityCheck;
        if (! $entityCheck->passed) {
            $failedChecks[] = $entityCheck->key;
            if ($entityCheck->suggestion) {
                $suggestions[] = $entityCheck->suggestion;
            }
        }

        // Check 3: Has COACH Professional Role
        $roleCheck = $this->checkCoachRole($individual);
        $checks[] = $roleCheck;
        if (! $roleCheck->passed) {
            $failedChecks[] = $roleCheck->key;
            if ($roleCheck->suggestion) {
                $suggestions[] = $roleCheck->suggestion;
            }
        }

        // Check 4: Required Certifications (if competition has requirements)
        if ($competition && ! empty($competition->requiredCoachCertifications)) {
            $certCheck = $this->checkRequiredCertifications($individual, $competition);
            $checks[] = $certCheck;
            if (! $certCheck->passed) {
                $failedChecks[] = $certCheck->key;
                if ($certCheck->suggestion) {
                    $suggestions[] = $certCheck->suggestion;
                }
            }
        }

        // Check 5: Not already enrolled
        $notEnrolledCheck = $this->checkNotAlreadyEnrolled($individual, $event);
        $checks[] = $notEnrolledCheck;
        if (! $notEnrolledCheck->passed) {
            $failedChecks[] = $notEnrolledCheck->key;
            if ($notEnrolledCheck->suggestion) {
                $suggestions[] = $notEnrolledCheck->suggestion;
            }
        }

        $isEligible = empty($failedChecks);

        return new DiagnosticResult(
            isEligible: $isEligible,
            checks: $checks,
            failedChecks: $failedChecks,
            suggestions: array_unique($suggestions)
        );
    }

    private function checkFederationMembership(Individual $individual): EligibilityCheck
    {
        $activeMemberships = $individual->individualFederations
            ->where('status_class', ActiveIndividualFederationState::class);

        if ($activeMemberships->isNotEmpty()) {
            return new EligibilityCheck(
                key: 'federation_membership',
                label: __('diagnostics.check_federation_membership'),
                passed: true,
                message: __('diagnostics.check_federation_membership_coach_passed'),
            );
        }

        return new EligibilityCheck(
            key: 'federation_membership',
            label: __('diagnostics.check_federation_membership'),
            passed: false,
            message: __('diagnostics.check_federation_membership_failed'),
            suggestion: __('diagnostics.suggestion_activate_membership'),
        );
    }

    private function checkEntityMembership(Individual $individual): EligibilityCheck
    {
        $activeEntityMemberships = $individual->individualEntities
            ->where('status_class', ActiveIndividualEntityState::class);

        if ($activeEntityMemberships->isNotEmpty()) {
            return new EligibilityCheck(
                key: 'entity_membership',
                label: __('diagnostics.check_entity_membership'),
                passed: true,
                message: __('diagnostics.check_entity_membership_passed_coach'),
            );
        }

        return new EligibilityCheck(
            key: 'entity_membership',
            label: __('diagnostics.check_entity_membership'),
            passed: false,
            message: __('diagnostics.check_entity_membership_failed'),
            suggestion: __('diagnostics.suggestion_join_entity'),
        );
    }

    private function checkCoachRole(Individual $individual): EligibilityCheck
    {
        // Check direct professional roles
        $hasDirectCoachRole = $individual->professionalRoles
            ->where('role', 'COACH')
            ->isNotEmpty();

        // Check entity-assigned coach roles
        $hasEntityCoachRole = $individual->professionalRoleEntities
            ->filter(function ($entityRole) {
                return $entityRole->professionalRole?->role === 'COACH';
            })
            ->isNotEmpty();

        if ($hasDirectCoachRole || $hasEntityCoachRole) {
            return new EligibilityCheck(
                key: 'coach_role',
                label: __('diagnostics.check_coach_role'),
                passed: true,
                message: __('diagnostics.check_coach_role_passed'),
            );
        }

        return new EligibilityCheck(
            key: 'coach_role',
            label: __('diagnostics.check_coach_role'),
            passed: false,
            message: __('diagnostics.check_coach_role_failed'),
            suggestion: __('diagnostics.suggestion_assign_coach_role'),
        );
    }

    private function checkRequiredCertifications(Individual $individual, Competition $competition): EligibilityCheck
    {
        $requiredCertIds = $competition->requiredCoachCertifications->pluck('id')->toArray();
        $requiredNames = $competition->requiredCoachCertifications->pluck('name')->implode(', ');

        $activeCertIds = $individual->certificationsAttributed
            ->where('status_class', ActiveCertificationAttributedState::class)
            ->pluck('certification_id')
            ->toArray();

        // Must have ALL required certifications
        $missingCerts = array_diff($requiredCertIds, $activeCertIds);

        if (empty($missingCerts)) {
            return new EligibilityCheck(
                key: 'required_certifications',
                label: __('diagnostics.check_required_certs'),
                passed: true,
                message: __('diagnostics.check_required_certs_passed'),
            );
        }

        return new EligibilityCheck(
            key: 'required_certifications',
            label: __('diagnostics.check_required_certs'),
            passed: false,
            message: __('diagnostics.check_required_certs_failed', ['certs' => $requiredNames]),
            suggestion: __('diagnostics.suggestion_obtain_required_cert'),
        );
    }

    private function checkNotAlreadyEnrolled(Individual $individual, Event $event): EligibilityCheck
    {
        $isAlreadyEnrolled = $individual->coachEnrollments()
            ->where('event_id', $event->id)
            ->exists();

        if ($isAlreadyEnrolled) {
            return new EligibilityCheck(
                key: 'not_enrolled',
                label: __('diagnostics.check_not_enrolled'),
                passed: false,
                message: __('diagnostics.check_already_enrolled'),
            );
        }

        return new EligibilityCheck(
            key: 'not_enrolled',
            label: __('diagnostics.check_not_enrolled'),
            passed: true,
            message: __('diagnostics.check_not_enrolled_passed'),
        );
    }
}
