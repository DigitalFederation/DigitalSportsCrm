<?php

namespace Database\Factories;

use App\Models\Country;
use Domain\Entities\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Support\UtilityMethods;

/**
 * @extends Factory<Entity>
 */
class EntityFactory extends Factory
{
    protected $model = Entity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'country_id' => Country::factory(),
            'vat_number' => $this->faker->numerify('###########'),
            'name' => $this->faker->company,
            'legal_name' => $this->faker->company,
            'legal_responsible_person' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'address' => $this->faker->streetAddress,
            'location' => $this->faker->city,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'email' => $this->faker->companyEmail,
            'member_code' => UtilityMethods::generateUniqueIndividualCode(),
        ];
    }
}
