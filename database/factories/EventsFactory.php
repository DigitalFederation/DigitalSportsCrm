<?php

namespace Database\Factories;

use Carbon\Carbon;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventsFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $startRegistration = $this->faker->dateTimeBetween('now', '+6 months');
        $endRegistration = $this->faker->dateTimeBetween($startRegistration, '+8 months');
        $startDate = $this->faker->dateTimeBetween($endRegistration, '+9 months');
        $endDate = $this->faker->dateTimeBetween($startDate, '+10 months');

        return [
            'name' => $this->faker->word(),
            'event_type' => $this->faker->word(),
            'event_category' => 'competition',
            'status_class' => ActiveEventState::class,
            'enrollment_type' => 'all',
            'location' => $this->faker->address(),
            'start_date' => $startDate,
            'start_registration' => $startRegistration,
            'end_date' => $endDate,
            'end_registration' => $endRegistration,
            'geo_zone_id' => null,
            'description' => $this->faker->paragraph(),
            'notes' => $this->faker->paragraph(),
            'featured_image' => 'default.png',
            'event_fee' => $this->faker->randomFloat(2, 50, 200),
            'external_url' => $this->faker->url(),
            'venue' => $this->faker->sentence(),
            'venue_address' => $this->faker->address(),
            'venue_city' => $this->faker->city(),
            'venue_country_id' => null,
            'is_visible' => true,
            'allow_individual_enrollment' => true,
            'allow_coach_enrollment' => true,
            'allow_referee_enrollment' => true,
        ];
    }

    public function active(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => ActiveEventState::class,
        ]);
    }

    public function withCustomDates(?Carbon $startRegistration = null): Factory
    {
        return $this->state(function (array $attributes) use ($startRegistration) {
            $startRegistration = $startRegistration ?? now();

            return [
                'start_registration' => $startRegistration,
                'end_registration' => $startRegistration->copy()->addDays(25),
                'start_date' => $startRegistration->copy()->addDays(30),
                'end_date' => $startRegistration->copy()->addDays(35),
            ];
        });
    }
}
