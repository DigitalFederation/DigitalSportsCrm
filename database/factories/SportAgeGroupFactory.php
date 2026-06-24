<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\Models\SportAgeGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SportAgeGroupFactory extends Factory
{
    protected $model = SportAgeGroup::class;

    public function definition(): array
    {
        return [
            'sport_id' => Sport::factory(),
            'title' => $this->faker->word,
            'birthday_start' => Carbon::now()->subYears(30)->format('Y-m-d'),
            'birthday_end' => Carbon::now()->subYears(20)->format('Y-m-d'),
        ];
    }
}
