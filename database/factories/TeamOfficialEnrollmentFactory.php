<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamOfficialEnrollmentFactory extends Factory
{
    protected $model = TeamOfficialEnrollment::class;

    public function definition()
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'event_id' => Event::factory(),
            'federation_id' => Federation::factory(),
            'individual_id' => Individual::factory(),
            'status_class' => RegisteredTeamOfficialEnrollmentState::class,
        ];
    }

    public function forEvent(Event $event)
    {
        return $this->state(function (array $attributes) use ($event) {
            return [
                'event_id' => $event->id,
            ];
        });
    }

    public function forFederation(Federation $federation)
    {
        return $this->state(function (array $attributes) use ($federation) {
            return [
                'federation_id' => $federation->id,
            ];
        });
    }

    public function forIndividual(Individual $individual)
    {
        return $this->state(function (array $attributes) use ($individual) {
            return [
                'individual_id' => $individual->id,
            ];
        });
    }

    public function withStatus(string $status)
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status_class' => $status,
            ];
        });
    }
}
