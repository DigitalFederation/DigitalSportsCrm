<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'name' => $this->faker->name(),
            // Use a globally-unique address rather than faker's safeEmail(): faker's
            // unique() only dedupes within a single test (the app/faker is rebuilt per
            // test), and safeEmail() draws from a small pool, so across a full suite run
            // two factory users can collide on the users_email_unique constraint.
            'email' => Str::uuid().'@example.test',
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'email_verified_at' => Carbon::now(),
            'group_id' => Group::factory(),
            'active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     *
     * @return $this
     */
    public function withPersonalTeam()
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(function (array $attributes, User $user) {
                    return ['name' => $user->name.'\'s Team', 'user_id' => $user->id, 'personal_team' => true];
                }),
            'ownedTeams'
        );
    }

    public function forGroup(string $groupCode)
    {
        return $this->state(function (array $attributes) use ($groupCode) {
            $group = Group::where('code', $groupCode)->first();

            return [
                'group_id' => $group->id,
            ];
        });
    }
}
