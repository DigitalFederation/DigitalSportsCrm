<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly pick one of the predefined groups
        $group = $this->faker->randomElement([
            ['name' => 'Individual', 'code' => 'INDIVIDUAL'],
            ['name' => 'Entity', 'code' => 'ENTITY'],
            ['name' => 'Federation', 'code' => 'FEDERATION'],
            ['name' => 'Admin', 'code' => 'ADMIN'],
        ]);

        return [
            'name' => $group['name'],
            'code' => $group['code'],
        ];
    }
}
