<?php

namespace Database\Factories\Domain\Diving\Models;

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivingProfessionalCertificationFactory extends Factory
{
    protected $model = DivingProfessionalCertification::class;

    public function definition(): array
    {
        $systems = ['SSI', 'PADI', 'SDI_TDI', 'DDI', 'GUE', 'CMAS'];
        $levels = ['Open Water Instructor', 'Advanced Instructor', 'Divemaster', 'Course Director', 'Instructor Trainer'];
        $certificationNames = [
            'Open Water Instructor',
            'Advanced Open Water Instructor',
            'Rescue Diver Instructor',
            'Divemaster',
            'Course Director',
            'Technical Instructor',
            'Nitrox Instructor',
        ];

        $system = $this->faker->randomElement($systems);
        $issueDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $hasExpiration = $this->faker->boolean(70); // 70% chance of having expiration

        return [
            'individual_id' => Individual::factory(),
            'certification_name' => $this->faker->randomElement($certificationNames),
            'certification_system' => $system,
            'certification_level' => $this->faker->randomElement($levels),
            'certification_number' => $system . '-' . $this->faker->unique()->numerify('######'),
            'national_equivalency' => $this->faker->optional(0.6)->randomElement(['N1', 'N2', 'N3', 'N4']),
            'issue_date' => $issueDate,
            'expiration_date' => $hasExpiration ? $this->faker->dateTimeBetween($issueDate, '+3 years') : null,
            'status_class' => PendingValidationDivingCertificationState::class,
            'validation_notes' => null,
            'validated_by' => null,
            'validated_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\ActiveDivingCertificationState::class,
            'validated_by' => \App\Models\User::factory(),
            'validated_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\ExpiredDivingCertificationState::class,
            'expiration_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\RevokedDivingCertificationState::class,
            'validation_notes' => 'Certification revoked due to violation.',
        ]);
    }

    public function forSystem(string $system): static
    {
        return $this->state(fn (array $attributes) => [
            'certification_system' => $system,
            'certification_number' => $system . '-' . $this->faker->unique()->numerify('######'),
        ]);
    }

    public function withoutExpiration(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => null,
        ]);
    }

    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'certification_level' => 'Instructor',
            'certification_name' => $this->faker->randomElement([
                'Open Water Instructor',
                'Advanced Open Water Instructor',
                'Rescue Diver Instructor',
            ]),
        ]);
    }

    public function divemaster(): static
    {
        return $this->state(fn (array $attributes) => [
            'certification_level' => 'Divemaster',
            'certification_name' => 'Divemaster',
        ]);
    }
}
