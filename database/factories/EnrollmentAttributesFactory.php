<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\EnrollmentAttributes;
use Domain\EvtEvents\Models\EventAttributes;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentAttributesFactory extends Factory
{
    protected $model = EnrollmentAttributes::class;

    public function definition()
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'attribute_id' => EventAttributes::factory(),
            'value' => $this->faker->word,
        ];
    }
}
