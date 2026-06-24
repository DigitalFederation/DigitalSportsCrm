<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CheckExistingEventEnrollmentAction
{
    /**
     * Check if an individual is already registered in an event or can be assigned a specific discipline.
     * This action handles two distinct scenarios:
     *
     * 1. Initial registration (empty disciplineId):
     *    - Checks organization-type conflicts (federation vs federation, entity vs entity)
     *    - Allows cross-organization registration (federation + entity)
     *
     * 2. Discipline assignment (non-empty disciplineId):
     *    - Only checks if the specific discipline is already assigned to the athlete
     *    - Prevents duplicate discipline assignments across organizations
     *
     * @param  Event  $event  The event to check registrations for
     * @param  Individual  $individual  The individual being registered
     * @param  string  $disciplineId  The discipline ID being registered for (empty string for initial registration)
     * @param  Model|null  $registeredBy  The entity/federation doing the registration (null for self-registration)
     * @return array Contains 'can_register' (bool) and 'message' (string) if registration isn't allowed
     */
    public function execute(
        Event $event,
        Individual $individual,
        string $disciplineId,
        ?Model $registeredBy = null
    ): array {
        // Check if we're doing initial registration or discipline assignment
        $isDisciplineAssignment = ! empty($disciplineId) && $disciplineId !== '0';

        // If this is a discipline assignment check, just verify this specific discipline isn't already assigned
        if ($isDisciplineAssignment) {
            return $this->checkDisciplineAssignment($event, $individual, $disciplineId, $registeredBy);
        }

        // Otherwise, this is an initial registration check - get all enrollments
        $existingEnrollments = AthleteEnrollment::query()
            ->where('event_id', $event->id)
            ->where('individual_id', $individual->id)
            ->with(['federation', 'entity', 'enrollment'])
            ->get();

        // No enrollments found - individual can register
        if ($existingEnrollments->isEmpty()) {
            return ['can_register' => true];
        }

        // Check based on who is registering the individual
        if ($registeredBy === null) {
            return $this->checkIndividualRegistration($existingEnrollments);
        } elseif ($registeredBy instanceof Federation) {
            return $this->checkFederationRegistration($existingEnrollments, $registeredBy);
        } elseif ($registeredBy instanceof Entity) {
            return $this->checkEntityRegistration($existingEnrollments, $registeredBy);
        }

        // Default case: Unknown registering type
        return [
            'can_register' => false,
            'message' => 'An unexpected error occurred while validating registration eligibility.',
        ];
    }

    /**
     * Check if a discipline can be assigned to an athlete
     * This only validates that the specific discipline isn't already assigned
     */
    private function checkDisciplineAssignment(
        Event $event,
        Individual $individual,
        string $disciplineId,
        ?Model $registeredBy
    ): array {
        // Check if this discipline is already assigned to this athlete
        $existingDiscipline = AthleteEnrollment::query()
            ->where('event_id', $event->id)
            ->where('individual_id', $individual->id)
            ->where('discipline_id', $disciplineId)
            ->with(['federation', 'entity'])
            ->first();

        // If discipline isn't assigned yet, allow it
        if (! $existingDiscipline) {
            return ['can_register' => true];
        }

        // Discipline is already assigned - build error message
        $organizationType = $existingDiscipline->federation_id
            ? 'federation: ' . ($existingDiscipline->federation?->name ?? 'Unknown Federation')
            : 'club: ' . ($existingDiscipline->entity?->name ?? 'Unknown Club');

        return [
            'can_register' => false,
            'message' => 'This athlete is already assigned to this discipline by another ' . $organizationType,
        ];
    }

    /**
     * Check if an individual can register themselves
     */
    private function checkIndividualRegistration(Collection $existingEnrollments): array
    {
        // Check if individual is already registered by a federation or entity
        $federationRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->federation_id !== null);
        if ($federationRegistration) {
            $statusMessage = $federationRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                ? ' (payment pending)'
                : '';

            return [
                'can_register' => false,
                'message' => 'You are already registered for this event by a National Federation: ' .
                    ($federationRegistration->federation?->name ?? 'Unknown Federation') . $statusMessage,
            ];
        }

        $entityRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->entity_id !== null);
        if ($entityRegistration) {
            $statusMessage = $entityRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                ? ' (payment pending)'
                : '';

            return [
                'can_register' => false,
                'message' => 'You are already registered for this event by a Club: ' .
                    ($entityRegistration->entity?->name ?? 'Unknown Club') . $statusMessage,
            ];
        }

        // Check if individual is already self-registered
        $selfRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->federation_id === null && $enrollment->entity_id === null);
        if ($selfRegistration) {
            // Show different message for pending payment
            if ($selfRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value) {
                return [
                    'can_register' => false,
                    'message' => 'You have already registered for this event. Payment is pending.',
                ];
            }

            return [
                'can_register' => false,
                'message' => 'You have already registered for this event.',
            ];
        }

        return ['can_register' => true];
    }

    /**
     * Check if a federation can register an individual
     */
    private function checkFederationRegistration(
        Collection $existingEnrollments,
        Federation $federation
    ): array {
        // Check if individual is already self-registered
        $selfRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->federation_id === null && $enrollment->entity_id === null);
        if ($selfRegistration) {
            // Different message for pending payment
            if ($selfRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value) {
                return [
                    'can_register' => false,
                    'message' => 'This athlete has already self-registered for this event with payment pending. ' .
                        'Please check with the athlete before proceeding.',
                ];
            }

            return [
                'can_register' => false,
                'message' => 'This athlete has already self-registered for this event.',
            ];
        }

        // Check if individual is already registered by another federation
        $otherFederationRegistration = $existingEnrollments->first(function ($enrollment) use ($federation) {
            return $enrollment->federation_id !== null && $enrollment->federation_id !== $federation->id;
        });
        if ($otherFederationRegistration) {
            $statusMessage = $otherFederationRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                ? ' (payment pending)'
                : '';

            return [
                'can_register' => false,
                'message' => 'This athlete is already registered for this event by another federation: ' .
                    ($otherFederationRegistration->federation?->name ?? 'Unknown Federation') . $statusMessage,
            ];
        }

        // Check if individual is already registered by this federation
        $thisFederationRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->federation_id === $federation->id);
        if ($thisFederationRegistration) {
            // Different message for pending payment
            if ($thisFederationRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value) {
                return [
                    'can_register' => false,
                    'message' => 'You have already registered this athlete for this event. Payment is pending.',
                ];
            }

            return [
                'can_register' => false,
                'message' => 'You have already registered this athlete for this event.',
            ];
        }

        // Check if individual is already registered by an entity
        $entityRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->entity_id !== null);
        if ($entityRegistration) {
            $statusMessage = $entityRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                ? ' (payment pending)'
                : '';

            return [
                'can_register' => false,
                'message' => 'This athlete is already registered for this event by a club: ' .
                    ($entityRegistration->entity?->name ?? 'Unknown Club') . $statusMessage,
            ];
        }

        return ['can_register' => true];
    }

    /**
     * Check if an entity can register an individual
     */
    private function checkEntityRegistration(
        Collection $existingEnrollments,
        Entity $entity
    ): array {
        // Check if individual is already self-registered
        $selfRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->federation_id === null && $enrollment->entity_id === null);
        if ($selfRegistration) {
            // Different message for pending payment
            if ($selfRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value) {
                return [
                    'can_register' => false,
                    'message' => 'This athlete has already self-registered for this event with payment pending. ' .
                        'Please check with the athlete before proceeding.',
                ];
            }

            return [
                'can_register' => false,
                'message' => 'This athlete has already self-registered for this event.',
            ];
        }

        // Check if individual is already registered by another entity
        $otherEntityRegistration = $existingEnrollments->first(function ($enrollment) use ($entity) {
            return $enrollment->entity_id !== null && $enrollment->entity_id !== $entity->id;
        });
        if ($otherEntityRegistration) {
            $statusMessage = $otherEntityRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value
                ? ' (payment pending)'
                : '';

            return [
                'can_register' => false,
                'message' => 'This athlete is already registered for this event by another club: ' .
                    ($otherEntityRegistration->entity?->name ?? 'Unknown Club') . $statusMessage,
            ];
        }

        // Check if individual is already registered by this entity
        $thisEntityRegistration = $existingEnrollments->first(fn ($enrollment) => $enrollment->entity_id === $entity->id);
        if ($thisEntityRegistration) {
            // Different message for pending payment
            if ($thisEntityRegistration->status_class === EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value) {
                return [
                    'can_register' => false,
                    'message' => 'You have already registered this athlete for this event. Payment is pending.',
                ];
            }

            return [
                'can_register' => false,
                'message' => 'You have already registered this athlete for this event.',
            ];
        }

        // Allow entity to register athletes that are already registered by federations
        // This enables the multi-organization registration scenario
        return ['can_register' => true];
    }
}
