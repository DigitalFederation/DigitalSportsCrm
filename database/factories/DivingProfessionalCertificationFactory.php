<?php

namespace Database\Factories;

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Diving\Models\DivingProfessionalCertification>
 */
class DivingProfessionalCertificationFactory extends Factory
{
    protected $model = DivingProfessionalCertification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
        $expiryDate = Carbon::instance($issueDate)->addYears($this->faker->numberBetween(1, 5));

        return [
            'individual_id' => Individual::factory(),
            'certification_system' => $this->faker->randomElement(['CMAS', 'SSI', 'PADI', 'SDI_TDI', 'DDI', 'GUE']),
            'certification_level' => $this->faker->randomElement(['Open Water', 'Advanced', 'Rescue', 'Master']),
            'certification_number' => strtoupper($this->faker->bothify('??#####')),
            'issue_date' => $issueDate,
            'expiration_date' => $expiryDate,
            'issuing_organization' => $this->faker->company(),
            'issuing_location' => $this->faker->city() . ', ' . $this->faker->country(),
            'status_class' => ActiveDivingCertificationState::class,
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the certification is pending validation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\PendingValidationDivingCertificationState::class,
        ]);
    }

    /**
     * Indicate that the certification is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\ExpiredDivingCertificationState::class,
            'expiry_date' => Carbon::now()->subDay(),
        ]);
    }

    /**
     * Indicate that the certification is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\RevokedDivingCertificationState::class,
        ]);
    }
}
