<?php

use Database\Factories\DisciplineFactory;
use Domain\EvtEvents\Actions\CreateDisciplineAction;
use Domain\EvtEvents\Models\Discipline;

it('creates a discipline', function () {

    $sport = \Database\Factories\SportFactory::new()->create();
    $data = DisciplineFactory::new()->create(['sport_id' => $sport->id])->toArray();

    $action = new CreateDisciplineAction;

    $discipline = $action->execute($data);

    expect($discipline->name)->toBe($data['name']);
    expect($discipline)->toBeInstanceOf(Discipline::class);
});
