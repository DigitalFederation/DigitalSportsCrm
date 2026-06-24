<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Sport;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Competition>
 */
class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create Sport first to ensure it exists
        $sport = Sport::factory()->create();

        return [
            'sport_id' => $sport->id, // Use the created sport's ID
            'event_id' => Event::factory(),
            'year' => $this->faker->year,
            'month' => $this->faker->monthName,
            'number' => $this->faker->numerify('Event ###'),
            'rounds_total' => $this->faker->numberBetween(1, 5),
            'cat_age' => $this->faker->word,
            'cat_competition' => $this->faker->word,
            'environment' => $this->faker->word,
            'full_name' => $this->faker->sentence,
            'status_class' => $this->faker->word,
            'venue' => $this->faker->sentence,
            'venue_address' => $this->faker->address,
            'venue_country_id' => Country::factory(),
            'venue_city' => $this->faker->city,
            'start_date' => $this->faker->date,
            'end_date' => $this->faker->date,
            'medals_gold' => 0,
            'medals_silver' => 0,
            'medals_bronze' => 0,
        ];
    }

    /**
     * Configure the factory to use a specific sport.
     */
    public function forSport(Sport $sport): self
    {
        return $this->state(function (array $attributes) use ($sport) {
            return [
                'sport_id' => $sport->id,
            ];
        });
    }
}
