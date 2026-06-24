<?php

declare(strict_types=1);

namespace Domain\Diagnostics\Actions;

use Domain\Diagnostics\Data\DiagnosticResult;
use Domain\Diagnostics\Data\EligibilityCheck;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;

class DiagnoseOfficialEligibilityAction
{
    public function execute(Individual $individual, Event $event, ?Competition $competition = null): DiagnosticResult
    {
        $individual->load([
            'individualFederations.federation',
            'individualEntities.entity',
        ]);

        $checks = [];
        $suggestions = [];
        $failedChecks = [];

        // Check 1: Active Membership (Federation OR Entity)
        $membershipCheck = $this->checkActiveMembership($individual);
        $checks[] = $membershipCheck;
        if (! $membershipCheck->passed) {
            $failedChecks[] = $membershipCheck->key;
            if ($membershipCheck->suggestion) {
                $suggestions[] = $membershipCheck->suggestion;
            }
        }

        // Check 2: Not already enrolled
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

    private function checkActiveMembership(Individual $individual): EligibilityCheck
    {
        $activeFederationMemberships = $individual->individualFederations
            ->where('status_class', ActiveIndividualFederationState::class);

        $activeEntityMemberships = $individual->individualEntities
            ->where('status_class', ActiveIndividualEntityState::class);

        $hasActiveFederationMembership = $activeFederationMemberships->isNotEmpty();
        $hasActiveEntityMembership = $activeEntityMemberships->isNotEmpty();

        if ($hasActiveFederationMembership || $hasActiveEntityMembership) {
            $details = [];
            if ($hasActiveFederationMembership) {
                $details[] = __('diagnostics.member_of_federations', [
                    'federations' => $activeFederationMemberships->pluck('federation.name')->filter()->implode(', '),
                ]);
            }
            if ($hasActiveEntityMembership) {
                $details[] = __('diagnostics.member_of_entities', [
                    'entities' => $activeEntityMemberships->pluck('entity.name')->filter()->implode(', '),
                ]);
            }

            return new EligibilityCheck(
                key: 'active_membership',
                label: __('diagnostics.check_active_membership'),
                passed: true,
                message: __('diagnostics.check_active_membership_passed'),
                details: $details,
            );
        }

        return new EligibilityCheck(
            key: 'active_membership',
            label: __('diagnostics.check_active_membership'),
            passed: false,
            message: __('diagnostics.check_active_membership_failed'),
            suggestion: __('diagnostics.suggestion_activate_membership'),
        );
    }

    private function checkNotAlreadyEnrolled(Individual $individual, Event $event): EligibilityCheck
    {
        $isAlreadyEnrolled = $individual->officialsEnrollments()
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
