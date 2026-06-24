<?php

namespace Database\Factories;

use App\Models\Country;
use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Federation>
 */
class FederationFactory extends Factory
{
    protected $model = Federation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $board_members = [];
        for ($i = 0; $i <= $this->faker->randomNumber(); $i++) {
            $board_members[] = [
                'name' => $this->faker->name,
                'role' => $this->faker->jobTitle,
            ];
        }

        $federation_names = [
            'NORTHERN UNDERWATER SPORTS FEDERATION',
            'COASTAL DIVING ASSOCIATION',
            'NATIONAL AQUATIC ACTIVITIES FEDERATION',
            'REGIONAL UNDERWATER SPORT FEDERATION',
            'SCIENTIFIC DIVING FEDERATION',
            'COMMUNITY DIVING FEDERATION',
        ];

        $rand_fed = array_rand($federation_names, 1);
        $name = $federation_names[$rand_fed];

        return [
            'country_id' => Country::factory(),
            'parent_id' => null,
            'name' => $name,
            'is_local' => $this->faker->boolean,
            'legal_name' => $name,
            'address' => $this->faker->streetAddress,
            'location' => $this->faker->city,
            'zip_code' => $this->faker->postcode,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'website' => $this->faker->url,
            'email' => $this->faker->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'board_members' => json_encode($board_members),
            'member_code' => $this->faker->numerify('######'),
            'vat_number' => $this->faker->numerify('###########'),
        ];

    }
}
