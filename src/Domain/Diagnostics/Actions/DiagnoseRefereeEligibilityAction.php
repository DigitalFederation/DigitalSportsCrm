<?php

declare(strict_types=1);

namespace Domain\Diagnostics\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Diagnostics\Data\DiagnosticResult;
use Domain\Diagnostics\Data\EligibilityCheck;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;

class DiagnoseRefereeEligibilityAction
{
    public function execute(Individual $individual, Event $event, ?Competition $competition = null): DiagnosticResult
    {
        $individual->load([
            'individualFederations.federation',
            'professionalRoles',
            'certificationsAttributed.certification.professionalRole',
        ]);

        $checks = [];
        $suggestions = [];
        $failedChecks = [];

        $eligibilityChecks = [
            $this->checkFederationMembership($individual),
            $this->checkRefereeRole($individual),
            $this->checkRefereeCertificationExists($individual),
            $this->checkCertificationIsActive($individual),
            $this->checkNotAlreadyEnrolled($individual, $event),
        ];

        if ($competition && ! empty($competition->required_referee_certifications)) {
            $eligibilityChecks[] = $this->checkRequiredCertifications($individual, $competition);
        }

        foreach ($eligibilityChecks as $check) {
            $checks[] = $check;
            if (! $check->passed) {
                $failedChecks[] = $check->key;
                if ($check->suggestion) {
                    $suggestions[] = $check->suggestion;
                }
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
            $federation = $activeMemberships->first()?->federation;

            return new EligibilityCheck(
                key: 'federation_membership',
                label: __('diagnostics.check_federation_membership'),
                passed: true,
                message: __('diagnostics.check_federation_membership_passed', ['federation' => $federation?->name]),
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

    private function checkRefereeRole(Individual $individual): EligibilityCheck
    {
        $hasRefereeRole = $individual->professionalRoles
            ->whereIn('role', ['TECHNICAL_OFFICIAL'])
            ->isNotEmpty();

        if ($hasRefereeRole) {
            return new EligibilityCheck(
                key: 'referee_role',
                label: __('diagnostics.check_referee_role'),
                passed: true,
                message: __('diagnostics.check_referee_role_passed'),
            );
        }

        // Check if there's a pending certification that would grant the role
        $pendingRefereeCert = $individual->certificationsAttributed
            ->filter(function ($cert) {
                return $cert->status_class === PendingCertificationAttributedState::class
                    && in_array($cert->certification?->professionalRole?->role, ['TECHNICAL_OFFICIAL'], true);
            })
            ->first();

        if ($pendingRefereeCert) {
            return new EligibilityCheck(
                key: 'referee_role',
                label: __('diagnostics.check_referee_role'),
                passed: false,
                message: __('diagnostics.check_referee_role_cert_pending', ['cert' => $pendingRefereeCert->certification?->name]),
                suggestion: __('diagnostics.suggestion_activate_certification'),
            );
        }

        return new EligibilityCheck(
            key: 'referee_role',
            label: __('diagnostics.check_referee_role'),
            passed: false,
            message: __('diagnostics.check_referee_role_failed'),
            suggestion: __('diagnostics.suggestion_attribute_referee_cert'),
        );
    }

    private function checkRefereeCertificationExists(Individual $individual): EligibilityCheck
    {
        $refereeCerts = $individual->certificationsAttributed
            ->filter(function ($cert) {
                return in_array($cert->certification?->professionalRole?->role, ['TECHNICAL_OFFICIAL'], true);
            });

        if ($refereeCerts->isEmpty()) {
            return new EligibilityCheck(
                key: 'referee_cert_exists',
                label: __('diagnostics.check_referee_cert_exists'),
                passed: false,
                message: __('diagnostics.check_referee_cert_exists_failed'),
                suggestion: __('diagnostics.suggestion_attribute_referee_cert'),
            );
        }

        $certNames = $refereeCerts->pluck('certification.name')->filter()->implode(', ');

        return new EligibilityCheck(
            key: 'referee_cert_exists',
            label: __('diagnostics.check_referee_cert_exists'),
            passed: true,
            message: __('diagnostics.check_referee_cert_exists_passed', ['certs' => $certNames]),
        );
    }

    private function checkCertificationIsActive(Individual $individual): EligibilityCheck
    {
        $refereeCerts = $individual->certificationsAttributed
            ->filter(function ($cert) {
                return in_array($cert->certification?->professionalRole?->role, ['TECHNICAL_OFFICIAL'], true);
            });

        if ($refereeCerts->isEmpty()) {
            return new EligibilityCheck(
                key: 'referee_cert_active',
                label: __('diagnostics.check_referee_cert_active'),
                passed: false,
                message: __('diagnostics.check_referee_cert_no_certs'),
            );
        }

        $activeCerts = $refereeCerts->where('status_class', ActiveCertificationAttributedState::class);
        $pendingCerts = $refereeCerts->where('status_class', PendingCertificationAttributedState::class);

        if ($activeCerts->isNotEmpty()) {
            return new EligibilityCheck(
                key: 'referee_cert_active',
                label: __('diagnostics.check_referee_cert_active'),
                passed: true,
                message: __('diagnostics.check_referee_cert_active_passed'),
            );
        }

        if ($pendingCerts->isNotEmpty()) {
            $pendingNames = $pendingCerts->pluck('certification.name')->filter()->implode(', ');

            return new EligibilityCheck(
                key: 'referee_cert_active',
                label: __('diagnostics.check_referee_cert_active'),
                passed: false,
                message: __('diagnostics.check_referee_cert_pending', ['certs' => $pendingNames]),
                suggestion: __('diagnostics.suggestion_activate_certification'),
            );
        }

        return new EligibilityCheck(
            key: 'referee_cert_active',
            label: __('diagnostics.check_referee_cert_active'),
            passed: false,
            message: __('diagnostics.check_referee_cert_inactive'),
            suggestion: __('diagnostics.suggestion_check_cert_status'),
        );
    }

    private function checkRequiredCertifications(Individual $individual, Competition $competition): EligibilityCheck
    {
        $requiredCertIds = $competition->required_referee_certifications;
        $competition->load('requiredRefereeCertifications');
        $requiredNames = $competition->requiredRefereeCertifications?->pluck('name')->implode(', ') ?? '';

        $activeCertIds = $individual->certificationsAttributed
            ->where('status_class', ActiveCertificationAttributedState::class)
            ->pluck('certification_id')
            ->toArray();

        $hasAllRequired = ! empty(array_intersect($requiredCertIds, $activeCertIds));

        if ($hasAllRequired) {
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
        $isAlreadyEnrolled = $individual->refereeEnrollments()
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
