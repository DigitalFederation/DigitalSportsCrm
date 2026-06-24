<?php

namespace Database\Factories;

use Domain\Payments\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'handler' => $this->faker->name(),  // Add the handler column
            'instructions' => $this->faker->text(),
        ];
    }
}
