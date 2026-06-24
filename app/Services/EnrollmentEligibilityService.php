<?php

namespace App\Services;

use Domain\EvtEvents\Models\Event;

class EnrollmentEligibilityService
{
    public function canEnrollInEvent(Event $event, string $enrollmentType): bool
    {
        if (! $this->isEventOpen($event)) {
            return false;
        }

        return match ($enrollmentType) {
            'individual' => $this->canEnrollIndividual($event),
            'athlete' => $this->canEnrollAthlete($event),
            'coach' => $this->canEnrollCoach($event),
            'staff' => $this->canEnrollStaff($event),
            default => false,
        };
    }

    private function isEventOpen(Event $event): bool
    {
        if ($event->event_category === 'organization') {
            return $event->state->allowsEnrollments();
        }

        if (! $event->start_registration || ! $event->end_registration) {
            return false;
        }

        return $event->isRegistrationOpen();
    }

    private function canEnrollIndividual(Event $event): bool
    {
        // Organization events should allow individual enrollments by default
        if ($event->isOrganizationEvent()) {
            return true;
        }

        return $event->allow_individual_enrollment;
    }

    private function canEnrollAthlete(Event $event): bool
    {
        return $event->isSportEvent();
    }

    private function canEnrollCoach(Event $event): bool
    {
        return $event->allow_coach_enrollment;
    }

    private function canEnrollStaff(Event $event): bool
    {
        return $event->isOrganizationEvent();
    }
}
