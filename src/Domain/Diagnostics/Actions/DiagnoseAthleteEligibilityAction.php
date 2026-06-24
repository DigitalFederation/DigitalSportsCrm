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
use Domain\Licenses\States\ActiveLicenseAttributedState;

class DiagnoseAthleteEligibilityAction
{
    public function execute(Individual $individual, Event $event, ?Competition $competition = null): DiagnosticResult
    {
        $individual->load([
            'individualFederations.federation',
            'individualEntities.entity',
            'entityAthletes.sport',
            'licenses.license',
        ]);

        $checks = [];
        $suggestions = [];
        $failedChecks = [];

        // Check 1: Active Federation Membership
        $federationCheck = $this->checkFederationMembership($individual, $event);
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

        // Check 3: Registered as Athlete for Sport
        $athleteCheck = $this->checkAthleteRegistration($individual, $event);
        $checks[] = $athleteCheck;
        if (! $athleteCheck->passed) {
            $failedChecks[] = $athleteCheck->key;
            if ($athleteCheck->suggestion) {
                $suggestions[] = $athleteCheck->suggestion;
            }
        }

        // Check 4: Required Licenses (if competition has requirements)
        if ($competition && ! empty($competition->required_athlete_licenses)) {
            $licenseCheck = $this->checkRequiredLicenses($individual, $competition);
            $checks[] = $licenseCheck;
            if (! $licenseCheck->passed) {
                $failedChecks[] = $licenseCheck->key;
                if ($licenseCheck->suggestion) {
                    $suggestions[] = $licenseCheck->suggestion;
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

    private function checkFederationMembership(Individual $individual, Event $event): EligibilityCheck
    {
        $activeMemberships = $individual->individualFederations
            ->where('status_class', ActiveIndividualFederationState::class);

        $hasActiveMembership = $activeMemberships->isNotEmpty();

        if ($hasActiveMembership) {
            return new EligibilityCheck(
                key: 'federation_membership',
                label: __('diagnostics.check_federation_membership'),
                passed: true,
                message: __('diagnostics.check_federation_membership_athlete_passed'),
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
            $entityNames = $activeEntityMemberships->pluck('entity.name')->filter()->implode(', ');

            return new EligibilityCheck(
                key: 'entity_membership',
                label: __('diagnostics.check_entity_membership'),
                passed: true,
                message: __('diagnostics.check_entity_membership_passed', ['entities' => $entityNames]),
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

    private function checkAthleteRegistration(Individual $individual, Event $event): EligibilityCheck
    {
        $sportId = $event->sport_id;
        $athleteRegistrations = $individual->entityAthletes;

        if ($athleteRegistrations->isEmpty()) {
            return new EligibilityCheck(
                key: 'athlete_registration',
                label: __('diagnostics.check_athlete_registration'),
                passed: false,
                message: __('diagnostics.check_athlete_registration_failed'),
                suggestion: __('diagnostics.suggestion_register_as_athlete'),
            );
        }

        // Check if registered for the event's sport
        $registeredForSport = $athleteRegistrations->where('sport_id', $sportId)->isNotEmpty();

        if (! $registeredForSport) {
            $registeredSports = $athleteRegistrations->pluck('sport.name')->filter()->implode(', ');

            return new EligibilityCheck(
                key: 'athlete_registration',
                label: __('diagnostics.check_athlete_registration'),
                passed: false,
                message: __('diagnostics.check_athlete_wrong_sport', ['registered' => $registeredSports, 'required' => $event->sport?->name]),
                suggestion: __('diagnostics.suggestion_register_for_sport'),
            );
        }

        return new EligibilityCheck(
            key: 'athlete_registration',
            label: __('diagnostics.check_athlete_registration'),
            passed: true,
            message: __('diagnostics.check_athlete_registration_passed', ['sport' => $event->sport?->name]),
        );
    }

    private function checkRequiredLicenses(Individual $individual, Competition $competition): EligibilityCheck
    {
        $requiredLicenseIds = $competition->required_athlete_licenses;
        $competition->load('requiredAthleteLicenses');
        $requiredNames = $competition->requiredAthleteLicenses?->pluck('name')->implode(', ') ?? '';

        $activeLicenseIds = $individual->licenses
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->pluck('license_id')
            ->toArray();

        $hasAllRequired = ! empty(array_intersect($requiredLicenseIds, $activeLicenseIds));

        if ($hasAllRequired) {
            return new EligibilityCheck(
                key: 'required_licenses',
                label: __('diagnostics.check_required_licenses'),
                passed: true,
                message: __('diagnostics.check_required_licenses_passed'),
            );
        }

        return new EligibilityCheck(
            key: 'required_licenses',
            label: __('diagnostics.check_required_licenses'),
            passed: false,
            message: __('diagnostics.check_required_licenses_failed', ['licenses' => $requiredNames]),
            suggestion: __('diagnostics.suggestion_obtain_required_license'),
        );
    }

    private function checkNotAlreadyEnrolled(Individual $individual, Event $event): EligibilityCheck
    {
        $isAlreadyEnrolled = $individual->athleteEnrollments()
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
