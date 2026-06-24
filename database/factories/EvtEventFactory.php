<?php

namespace Database\Factories;

use App\Enums\EvtEventEnrollmentTypeEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<EvtEventFactory>
 */
class EvtEventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start_date = $this->faker->dateTimeBetween('now', '+1 year');
        $end_date = $this->faker->dateTimeBetween('+1 year', '+2 years');

        return [
            'name' => $this->faker->word(),
            'event_type' => $this->faker->word(),
            'event_category' => 'organization',
            'status_class' => ActiveEventState::class,
            'enrollment_type' => 'all',
            'location' => $this->faker->address(),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'start_registration' => $start_date,
            'end_registration' => $end_date,
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
            'is_visible' => $this->faker->boolean(),
        ];
    }

    /**
     * Indicate that the event is active.
     */
    public function active(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => ActiveEventState::class,
        ]);
    }

    public function canceled(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => CanceledEventState::class,
        ]);
    }

    /**
     * Indicate that the event is only for federations.
     */
    public function forFederations(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_type' => EvtEventEnrollmentTypeEnum::only_federations->value,
        ]);
    }
}
