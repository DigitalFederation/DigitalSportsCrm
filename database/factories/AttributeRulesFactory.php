<?php

namespace Database\Factories;

use App\Enums\EvtAttributeRuleOperatorsEnum;
use Domain\EvtEvents\Models\AttributeRules;
use Domain\EvtEvents\Models\EventAttributes;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeRulesFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = AttributeRules::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'attribute_id' => EventAttributes::factory(),
            'operator' => $this->faker->randomElement(EvtAttributeRuleOperatorsEnum::cases()),
            'default_value' => $this->faker->word,
            'comparison_field' => $this->faker->word,
        ];
    }
}
