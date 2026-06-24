<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionReferee;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionRefereeFactory extends Factory
{
    protected $model = CompetitionReferee::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'individual_id' => Individual::factory(),
        ];
    }
}
