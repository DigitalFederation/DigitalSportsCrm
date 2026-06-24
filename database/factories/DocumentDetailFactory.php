<?php

namespace Database\Factories;

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentDetailFactory extends Factory
{
    protected $model = DocumentDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'description' => $this->faker->sentence,
            'owner_id' => LicenseAttributed::factory(),
            'owner_type' => LicenseAttributed::class,
            'reference' => $this->faker->text(50),
            'quantity' => $this->faker->numberBetween(1, 10),
            'net_value' => $this->faker->randomFloat(2, 0, 999999.99),
            'tax_value' => $this->faker->randomFloat(2, 0, 999999.99),
            'tax_percentage' => $this->faker->randomFloat(2, 0, 100),
            'total_value' => $this->faker->randomFloat(2, 0, 999999.99),
            'is_debit' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
