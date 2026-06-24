<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseAttributedFactory extends Factory
{
    protected $model = LicenseAttributed::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        DocumentTypeFactory::new()->create(['code' => DocumentData::DEFAULT_TYPE_CODE]);

        return [
            'status_class' => PendingLicenseAttributedState::class,
            'license_id' => License::factory()->create(),
            'federation_id' => Federation::factory(),
            'model_type' => $this->faker->randomElement(['individual', 'entity']),
            'model_id' => function (array $attributes) {
                if ($attributes['model_type'] === 'individual') {
                    return Individual::factory();
                }

                return Entity::factory();
            },
            'license_name' => $this->faker->word,
            'holder_name' => $this->faker->name,
            'federation_name' => $this->faker->company,
            'national_license_code' => $this->faker->randomNumber(8),
            'license_number' => $this->faker->randomNumber(8),
            'total_value' => $this->faker->randomFloat(2, 10, 1000),
            'current_term_starts_at' => $today = $this->faker->date(),
            'current_term_ends_at' => Carbon::parse($today)->addYear(),
            'owner_member_code' => $this->faker->randomNumber(8),
            'notes' => $this->faker->paragraph,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
