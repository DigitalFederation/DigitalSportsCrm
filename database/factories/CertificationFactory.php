<?php

namespace Database\Factories;

use App\Models\Committee;
use Domain\Certifications\Models\Certification;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'committee_id' => Committee::factory(),
            'name' => $this->faker->unique()->sentence(2),
            'license_id' => License::factory(),
        ];
    }
}
