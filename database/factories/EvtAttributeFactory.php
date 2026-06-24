<?php

namespace Database\Factories;

use App\Enums\EvtAttributeFillableTypeEnum;
use Domain\EvtEvents\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvtAttributeFactory>
 */
class EvtAttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fillable_type = $this->faker->randomElement(EvtAttributeFillableTypeEnum::cases());

        return [
            'name' => $this->faker->name(),
            'attribute_type' => $this->faker->word(),
            'default_value' => $this->faker->word(),
            'validation_rules' => $this->faker->word(),
            'custom_class' => '',
            'fillable_type' => $fillable_type->name,
            'fillable_global' => $this->faker->boolean,
        ];
    }
}
