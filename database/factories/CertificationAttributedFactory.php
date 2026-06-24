<?php

namespace Database\Factories;

use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\CanceledCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificationAttributed>
 */
class CertificationAttributedFactory extends Factory
{
    protected $model = CertificationAttributed::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'certification_id' => Certification::factory(),
            'federation_id' => Federation::factory(),
            'entity_id' => Entity::factory(),
            'status_class' => $this->faker->randomElement([
                ActiveCertificationAttributedState::class,
                PendingCertificationAttributedState::class,
                CanceledCertificationAttributedState::class,
            ]),
            'individual_id' => Individual::factory(),
            'national_code' => $this->faker->unique()->numerify('#####'),
            'international_code' => $this->faker->unique()->numerify('#####'),
            'certification_name' => $this->faker->text(45),
            'holder_name' => $this->faker->name,
            'federation_name' => $this->faker->company,
            'entity_name' => $this->faker->company,
            'instructor_id' => Individual::factory(),
            'code' => $this->faker->unique()->bothify('???-###'),
            'number' => $this->faker->unique()->numerify('#####'),
            'activator_id' => $this->faker->randomElement([
                Federation::factory(),
                Entity::factory(),
            ]),
            'activator_type' => $this->faker->randomElement([
                Federation::class,
                Entity::class,
            ]),
            'activated_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'current_term_starts_at' => $this->faker->date(),
            'current_term_ends_at' => $this->faker->date(),
            'notes' => $this->faker->paragraph,
            'batch_id' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
