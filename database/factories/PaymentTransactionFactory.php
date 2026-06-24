<?php

namespace Database\Factories;

use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => \Domain\Documents\Models\Document::factory(), // Assuming you have a Document factory
            'amount' => $this->faker->randomFloat(2, 10, 500), // Example range: $10 - $500
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'payment_data' => null,
            'comment' => $this->faker->sentence, // Random comment
        ];
    }
}
