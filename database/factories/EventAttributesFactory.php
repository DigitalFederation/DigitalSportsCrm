<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventAttributes;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventAttributesFactory extends Factory
{
    protected $model = EventAttributes::class;

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'attribute_template_id' => Attribute::factory(),
            'value' => $this->faker->word,
        ];
    }
}
