<?php

namespace Database\Factories;

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentsFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'enrollable_id' => $this->faker->randomElement([
                Federation::factory(),
                Entity::factory(),
                Individual::factory(),
            ]),
            'enrollable_type' => $this->faker->randomElement([
                'App\\Src\\Domain\\EvtEvents\\Models\\Federation',
                'App\\Src\\Domain\\EvtEvents\\Models\\Entity',
                'App\\Src\\Domain\\EvtEvents\\Models\\Individual',
            ]),
        ];
    }
}
