<?php

namespace Database\Factories;

use App\Enums\EvtIndividualEnrollmentStatusEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class IndividualEnrollmentFactory extends Factory
{
    protected $model = IndividualEnrollment::class;

    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'event_id' => Event::factory(),
            'federation_id' => Federation::factory(),
            'entity_id' => null,
            'individual_id' => Individual::factory(),
            'status_class' => EvtIndividualEnrollmentStatusEnum::REGISTERED->value,
            'price_type' => null,
            'price' => 0,
            'pricing_id' => null,
        ];
    }

    public function forEnrollment(Enrollment $enrollment): static
    {
        return $this->state([
            'enrollment_id' => $enrollment->id,
            'event_id' => $enrollment->event_id,
        ]);
    }

    public function forFederation(Federation $federation): static
    {
        return $this->state([
            'federation_id' => $federation->id,
            'entity_id' => null,
        ]);
    }

    public function forEntity(Entity $entity): static
    {
        return $this->state([
            'federation_id' => null,
            'entity_id' => $entity->id,
        ]);
    }
}
