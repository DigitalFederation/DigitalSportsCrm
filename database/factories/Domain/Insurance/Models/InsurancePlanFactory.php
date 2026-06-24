<?php

namespace Database\Factories\Domain\Insurance\Models;

use App\Enums\InsurancePlansTypeEnum;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Enums\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InsurancePlan>
 */
class InsurancePlanFactory extends Factory
{
    protected $model = InsurancePlan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true) . ' Insurance Plan',
            'description' => $this->faker->paragraph(),
            'insured_activity' => $this->faker->randomElement(['Diving', 'Sports', 'Coaching', 'Instruction']),
            'territorial_scope' => $this->faker->randomElement(['National', 'International', 'European Union']),
            'cmas_license_code' => strtoupper($this->faker->bothify('??###')),
            'target_audience' => $this->faker->randomElement(['individual', 'entity', 'both']),
            'type' => $this->faker->randomElement(InsurancePlansTypeEnum::cases()),
            'individual_fee' => $this->faker->randomFloat(2, 0, 500),
            'entity_fee' => $this->faker->randomFloat(2, 0, 1000),
            'policy_number' => null,
            'policy_number_prefix' => null,
            'policy_number_sequence' => 0,
            'policy_number_format' => '{prefix}-{sequence}',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'period' => 1,
            'period_unit' => 'year',
            'vat_rate' => VatRate::default()->value,
            'requires_official_document' => false,
            'required_document_type' => null,
            'requires_active_affiliation' => false,
        ];
    }

    /**
     * Indicate that the insurance plan requires an official document
     */
    public function requiresDocument(string $documentType): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_official_document' => true,
            'required_document_type' => $documentType,
        ]);
    }

    /**
     * Indicate that the insurance plan is free
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_fee' => 0,
            'entity_fee' => 0,
        ]);
    }

    /**
     * Indicate that the insurance plan requires active affiliation
     */
    public function requiresAffiliation(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_active_affiliation' => true,
        ]);
    }
}
