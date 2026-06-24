<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\DisciplineTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplineTemplateFactory extends Factory
{
    protected $model = DisciplineTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ];
    }
}
