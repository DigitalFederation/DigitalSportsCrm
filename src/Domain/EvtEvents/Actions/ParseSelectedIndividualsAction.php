<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Illuminate\Database\Eloquent\Collection;

class ParseSelectedIndividualsAction
{
    /**
     * @param  Collection<int, \Domain\EvtEvents\Models\Enrollment>  $pendingEnrollments
     */
    public function execute(Collection $pendingEnrollments): array
    {
        $selectedIndividuals = [];

        foreach ($pendingEnrollments as $enrollment) {
            foreach ($enrollment->individualEnrollments as $individualEnrollment) {
                $selectedIndividuals[] = [
                    'id' => $individualEnrollment->id,
                    'name' => $individualEnrollment->individual->name,
                    'surname' => $individualEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::INDIVIDUAL->name,
                ];
            }
            foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {

                $selectedIndividuals[] = [
                    'id' => $athleteEnrollment->id,
                    'name' => $athleteEnrollment->individual->name ?? '',
                    'surname' => $athleteEnrollment->individual->surname ?? '',
                    'role' => EvtEventEnrollmentRoleEnum::ATHLETE->name,
                ];

            }
            foreach ($enrollment->coachEnrollments as $coachEnrollment) {
                $selectedIndividuals[] = [
                    'id' => $coachEnrollment->id,
                    'name' => $coachEnrollment->individual->name,
                    'surname' => $coachEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::COACH->name,
                ];
            }
        }

        return $selectedIndividuals;

    }
}
