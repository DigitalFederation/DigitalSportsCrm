<?php

namespace Database\Factories;

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Country;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\ExpiredOfficialDocumentState;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficialDocument>
 */
class OfficialDocumentFactory extends Factory
{
    protected $model = OfficialDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $country = Country::factory()->create();

        return [
            'name' => $this->faker->word,
            'individual_id' => $this->faker->uuid,
            'country_id' => $country->id,
            'type' => $this->faker->randomElement(OfficialDocumentTypeEnum::cases()),
            'federation_id' => $this->faker->optional()->numberBetween(1, 10),
            'status_class' => PendingOfficialDocumentState::class,
            'expiry_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'role' => $this->faker->optional()->word,
            'created_by' => $this->faker->uuid,
            'updated_by' => $this->faker->uuid,
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the document is active.
     *
     * @return Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_class' => ActiveOfficialDocumentState::class,
                'activated_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            ];
        });
    }

    /**
     * Indicate that the document is expired.
     *
     * @return Factory
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'status_class' => ExpiredOfficialDocumentState::class,
                'expiry_date' => $this->faker->dateTimeBetween('-1 year', 'yesterday')->format('Y-m-d'),
            ];
        });
    }
}
