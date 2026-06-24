<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Illuminate\Validation\ValidationException;

class ValidateEnrollmentPricingAction
{
    public function execute(Event $event, string $enrollmentType): bool
    {
        $role = match ($enrollmentType) {
            'athlete' => EvtEventEnrollmentRoleEnum::ATHLETE,
            'coach' => EvtEventEnrollmentRoleEnum::COACH,
            'referee' => EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL,
            'official' => EvtEventEnrollmentRoleEnum::OFFICIAL,
            default => throw ValidationException::withMessages([
                'role' => __('Invalid enrollment role'),
            ])
        };

        if (! Pricing::query()
            ->where('event_id', $event->id)
            ->where('enrollment_role', $role)
            ->where('is_active', true)
            ->exists()) {

            throw ValidationException::withMessages([
                'pricing' => __('Enrollment registration is not available for :role role', ['role' => $enrollmentType]),
            ]);
        }

        return true;
    }
}
