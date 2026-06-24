<?php

namespace Database\Factories;

use App\Enums\EvtAttributeRuleOperatorsEnum;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeRules>
 */
class EvtAttributeRuleFactory extends Factory
{
    protected $model = AttributeRules::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory()->create(),
            'operator' => $this->faker->randomElement(EvtAttributeRuleOperatorsEnum::cases()),
            'default_value' => $this->faker->word,
            'name' => $this->faker->word,
        ];
    }
}
