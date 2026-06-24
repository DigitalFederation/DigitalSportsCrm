<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GetWaitingListSelectedIndividualsAction
{
    /**
     * @param  EloquentCollection<int, \Domain\EvtEvents\Models\Enrollment>  $pendingEnrollments
     */
    public function execute(EloquentCollection $pendingEnrollments)
    {
        $selectedIndividuals = [];

        foreach ($pendingEnrollments as $enrollment) {
            foreach ($enrollment->individualEnrollments as $individualEnrollment) {

                $selectedIndividuals[] = [
                    'id' => $individualEnrollment->id,
                    'individual_id' => $individualEnrollment->individual_id,
                    'name' => $individualEnrollment->individual->name,
                    'surname' => $individualEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::INDIVIDUAL->name,
                    'pricing_id' => $individualEnrollment->pricing_id ?? null,
                    'price' => $individualEnrollment->price ?? null,
                ];

            }
            foreach ($enrollment->athleteEnrollments as $athleteEnrollment) {
                $selectedIndividuals[] = [
                    'id' => $athleteEnrollment->id,
                    'individual_id' => $athleteEnrollment->individual_id,
                    'name' => $athleteEnrollment->individual->name,
                    'surname' => $athleteEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::ATHLETE->name,
                    'discipline_id' => $athleteEnrollment->discipline_id,
                    'pricing_id' => $athleteEnrollment->per_person_pricing_id ?? $athleteEnrollment->pricing_id,
                    'price' => $athleteEnrollment->per_person_price ?? $athleteEnrollment->price,
                    'discipline_price' => $athleteEnrollment->discipline_price ?? null,
                ];
            }
            foreach ($enrollment->coachEnrollments as $coachEnrollment) {
                $selectedIndividuals[] = [
                    'id' => $coachEnrollment->id,
                    'name' => $coachEnrollment->individual->name,
                    'surname' => $coachEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::COACH->name,
                    'pricing_id' => $coachEnrollment->pricing_id ?? null,
                ];
            }
            foreach ($enrollment->refereeEnrollments as $refereeEnrollment) {
                $selectedIndividuals[] = [
                    'id' => $refereeEnrollment->id,
                    'name' => $refereeEnrollment->individual->name,
                    'surname' => $refereeEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->name,
                    'pricing_id' => $refereeEnrollment->pricing_id ?? null,
                ];
            }
            foreach ($enrollment->teamOfficialEnrollments as $teamOfficialEnrollment) {
                $selectedIndividuals[] = [
                    'id' => $teamOfficialEnrollment->id,
                    'name' => $teamOfficialEnrollment->individual->name,
                    'surname' => $teamOfficialEnrollment->individual->surname,
                    'role' => EvtEventEnrollmentRoleEnum::OFFICIAL->name,
                    'pricing_id' => $teamOfficialEnrollment->pricing_id ?? null,
                ];
            }
        }

        return $selectedIndividuals;
    }
}
