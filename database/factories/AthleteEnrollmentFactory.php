<?php

namespace Database\Factories;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class AthleteEnrollmentFactory extends Factory
{
    protected $model = AthleteEnrollment::class;

    public function definition()
    {
        return [
            'discipline_id' => Discipline::factory(),
            'individual_id' => Individual::factory(),
            'federation_id' => Federation::factory(),
            'entity_id' => null,
            'enrollment_id' => Enrollment::factory(),
            'event_id' => Event::factory(),
            'per_person_pricing_id' => null,
            'per_person_price' => 0,
            'discipline_pricing_id' => null,
            'discipline_price' => 0,
            'event_fee_pricing_id' => null,
            'event_fee' => 0,
            'total_price' => 0,
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED,
        ];
    }

    public function forFederation(Federation $federation)
    {
        return $this->state([
            'federation_id' => $federation->id,
            'entity_id' => null,
        ]);
    }

    public function forEntity(Entity $entity)
    {
        return $this->state([
            'federation_id' => null,
            'entity_id' => $entity->id,
        ]);
    }

    public function forEnrollment(Enrollment $enrollment)
    {
        return $this->state([
            'enrollment_id' => $enrollment->id,
            'event_id' => $enrollment->event_id,
        ]);
    }

    public function withPricing(?Pricing $perPersonPricing = null, ?Pricing $disciplinePricing = null, ?Pricing $eventFeePricing = null)
    {
        return $this->state(function (array $attributes) use ($perPersonPricing, $disciplinePricing, $eventFeePricing) {
            $perPersonPrice = $perPersonPricing ? $perPersonPricing->price : 0;
            $disciplinePrice = $disciplinePricing ? $disciplinePricing->price : 0;
            $eventFee = $eventFeePricing ? $eventFeePricing->price : 0;

            return [
                'per_person_pricing_id' => $perPersonPricing ? $perPersonPricing->id : null,
                'per_person_price' => $perPersonPrice,
                'discipline_pricing_id' => $disciplinePricing ? $disciplinePricing->id : null,
                'discipline_price' => $disciplinePrice,
                'event_fee_pricing_id' => $eventFeePricing ? $eventFeePricing->id : null,
                'event_fee' => $eventFee,
                'total_price' => $perPersonPrice + $disciplinePrice + $eventFee,
            ];
        });
    }
}
