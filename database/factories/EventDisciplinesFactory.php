<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventDisciplines;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventDisciplinesFactory extends Factory
{
    protected $model = EventDisciplines::class;

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'discipline_id' => Discipline::factory(),
        ];
    }
}
