<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use Domain\EvtEvents\Models\Event;

class CheckEventRegisteredAthletesAction
{
    public function execute(Event $event, string $federationId): bool
    {
        return $event->athleteEnrollments()
            ->whereHas('enrollment', function ($q) {
                $q->where('payment_status', EvtEnrollmentStatusEnum::ACTIVE);
            })
            ->where('federation_id', $federationId)
            ->where('status_class', EvtAthleteEnrollmentStatusEnum::PAID)
            ->exists();
    }
}
