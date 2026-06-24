<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;
use Support\UtilityMethods;

/**
 * @extends Factory<Individual>
 */
class IndividualFactory extends Factory
{
    protected $model = Individual::class;

    public function definition()
    {
        return [
            'country_id' => Country::factory(),
            'user_id' => function () {
                // Find the group or create if not exists
                $group = Group::firstOrCreate(
                    ['code' => 'INDIVIDUAL'],
                    ['name' => 'Individual']
                );

                // Find a user with the 'INDIVIDUAL' group or create a new one
                return User::factory()->create(['group_id' => $group->id])->id;
            },
            'name' => $this->faker->firstName,
            'surname' => $this->faker->lastName,
            'birthdate' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'email' => $this->faker->unique()->safeEmail,
            'doc_ref_type' => $this->faker->randomElement(['passport', 'cc']),
            'doc_ref' => $this->faker->unique()->numerify('########'),
            'created_by' => function (array $attributes) {
                return $attributes['user_id'];
            },
            'updated_by' => function (array $attributes) {
                return $attributes['user_id'];
            },
            'member_code' => UtilityMethods::generateUniqueIndividualCode(),
        ];
    }
}
