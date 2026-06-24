<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;

class ActivateEnrollmentsAction
{
    public function execute(int $enrollmentId): void
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        // Activate individual enrollments
        $enrollment->individualEnrollments()->update(['status_class' => EvtEnrollmentStatusEnum::PAID->value]);

        // Activate athlete enrollments
        $enrollment->athleteEnrollments()->update(['status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value]);

        // Activate coach enrollments
        $enrollment->coachEnrollments()->update(['status_class' => RegisteredCoachEnrollmentState::class]);

        // Activate referee enrollments
        $enrollment->refereeEnrollments()->update(['status_class' => ActiveRefereeEnrollmentState::class]);

        // Activate team official enrollments
        $enrollment->teamOfficialEnrollments()->update(['status_class' => RegisteredTeamOfficialEnrollmentState::class]);
    }
}
