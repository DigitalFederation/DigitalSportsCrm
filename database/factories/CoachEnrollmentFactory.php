<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoachEnrollmentFactory extends Factory
{
    protected $model = CoachEnrollment::class;

    public function definition()
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'event_id' => Event::factory(),
            'federation_id' => Federation::factory(),
            'individual_id' => Individual::factory(),
            'status_class' => RegisteredCoachEnrollmentState::class,
            'price' => 0,
            'price_type' => 'free',
        ];
    }

    public function forEvent(Event $event): static
    {
        return $this->state(function (array $attributes) use ($event) {
            return [
                'event_id' => $event->id,
            ];
        });
    }

    public function forFederation(Federation $federation): static
    {
        return $this->state(function (array $attributes) use ($federation) {
            return [
                'federation_id' => $federation->id,
            ];
        });
    }

    public function forIndividual(Individual $individual): static
    {
        return $this->state(function (array $attributes) use ($individual) {
            return [
                'individual_id' => $individual->id,
            ];
        });
    }

    public function withStatus(string $status): static
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status_class' => $status,
            ];
        });
    }

    public function canceled(): static
    {
        return $this->withStatus(CanceledCoachEnrollmentState::class);
    }
}
