<?php

use Database\Factories\AttributeFactory;
use Database\Factories\DisciplineFactory;
use Domain\EvtEvents\Actions\CreateAttributesForDisciplineAction;

it('creates multiple attributes for a discipline', function () {

    $discipline = DisciplineFactory::new()->create();
    $attributesData = AttributeFactory::new()->count(3)->raw(['discipline_id' => $discipline->id]);
    $action = new CreateAttributesForDisciplineAction;

    $attributes = $action->execute($discipline, $attributesData);

    foreach ($attributes as $index => $attribute) {
        expect($attribute->name)->toBe($attributesData[$index]['name'])
            ->and($attribute->disciplines()->first()->id)->toBe($discipline->id)
            ->and($attribute)->toBeInstanceOf(\Domain\EvtEvents\Models\Attribute::class);
    }
});
