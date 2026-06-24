<?php

namespace Database\Factories;

use Domain\Certifications\Models\CertificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationType>
 */
class CertificationTypeFactory extends Factory
{
    protected $model = CertificationType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->realText(45),
        ];
    }
}
