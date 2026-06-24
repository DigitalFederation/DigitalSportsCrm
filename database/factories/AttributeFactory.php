<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'attribute_type' => $this->faker->randomElement(['country', 'text']),
            'default_value' => $this->faker->word,
            'validation_rules' => 'required|string',
            'custom_class' => null,
            'fillable_type' => $this->faker->randomElement(['auto', 'manual']),
            'fillable_global' => $this->faker->boolean,
            'discipline_id' => DisciplineFactory::new()->create(),
        ];
    }
}
