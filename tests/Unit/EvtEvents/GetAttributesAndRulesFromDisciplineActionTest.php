<?php

use Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Domain\EvtEvents\Models\Discipline;

it('gets attributes and rules for a discipline', function () {

    $discipline = Discipline::factory()->create();
    $attributes = Attribute::factory()->count(3)->create([
        'fillable_global' => false,
    ]);

    $attributes->each(function ($attribute) {
        $rules = AttributeRules::factory()->count(3)->create(['attribute_id' => $attribute->id]);
        $attribute->rules()->saveMany($rules);
    });

    $discipline->attributes()->attach($attributes);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    expect($result)->toHaveKeys(['attributes', 'global_attributes']);
    expect($result['attributes'])->toHaveCount(3);

    // Loop the final array structure, not the Eloquent models
    foreach ($result['attributes'] as $index => $attributeArray) {
        // Compare to the Eloquent attribute
        $rawModel = $attributes[$index];

        expect($attributeArray['attribute_data']['id'])
            ->toBe($rawModel->id);

        expect($attributeArray['rules'])
            ->toHaveCount(3);
    }
});

it('returns empty arrays when discipline id is empty', function () {
    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute(null);

    expect($result)->toHaveKeys(['attributes', 'global_attributes']);
    expect($result['attributes'])->toBeEmpty();
    expect($result['global_attributes'])->toBeEmpty();

    $result = $action->execute('');

    expect($result)->toHaveKeys(['attributes', 'global_attributes']);
    expect($result['attributes'])->toBeEmpty();
    expect($result['global_attributes'])->toBeEmpty();
});

it('handles attributes with options in attribute_data correctly', function () {
    $discipline = Discipline::factory()->create();

    // Create attribute with options inside attribute_data['options']
    $attribute = Attribute::factory()->create([
        'name' => 'Test Attribute',
        'attribute_type' => 'SELECT',
        'fillable_global' => false,
        'attribute_data' => [
            'options' => [
                'option1' => 'Option 1',
                'option2' => 'Option 2',
                'option3' => 'Option 3',
            ],
            'required' => true,
        ],
    ]);

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    expect($result['attributes'][0]['attribute_data']['options'])
        ->toBe([
            'option1' => 'Option 1',
            'option2' => 'Option 2',
            'option3' => 'Option 3',
        ]);
});

it('handles attributes with numeric keys at the top level correctly', function () {
    $discipline = Discipline::factory()->create();

    // Create attribute with numeric keys at the top level
    $attributeData = [
        0 => '1',
        1 => '2',
        2 => '3',
    ];

    $attribute = Attribute::factory()->create([
        'name' => 'Relay Order',
        'attribute_type' => 'SELECT',
        'fillable_global' => false,
        'attribute_data' => $attributeData,
    ]);

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    // It should convert numerical array to associative for dropdown
    expect($result['attributes'][0]['attribute_data']['options'])
        ->toBe([
            '1' => '1',
            '2' => '2',
            '3' => '3',
        ]);
});

it('handles attributes with both options and numeric keys correctly', function () {
    $discipline = Discipline::factory()->create();

    // Create attribute with both options and numeric keys
    $attributeData = [
        'options' => ['a' => 'Option A', 'b' => 'Option B'],
        0 => '1',
        1 => '2',
        2 => '3',
    ];

    $attribute = Attribute::factory()->create([
        'name' => 'Mixed Attribute',
        'attribute_type' => 'SELECT',
        'fillable_global' => false,
        'attribute_data' => $attributeData,
    ]);

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    // Explicit options should take precedence over numeric keys
    // When both are present, numeric keys should be ignored
    expect($result['attributes'][0]['attribute_data']['options'])
        ->toBe(['a' => 'Option A', 'b' => 'Option B']);
});

it('handles attributes with empty options correctly', function () {
    $discipline = Discipline::factory()->create();

    // Create attribute with empty options
    $attribute = Attribute::factory()->create([
        'name' => 'Empty Options',
        'attribute_type' => 'SELECT',
        'fillable_global' => false,
        'attribute_data' => [
            'options' => [],
        ],
    ]);

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    // It should preserve the empty options array
    expect($result['attributes'][0]['attribute_data']['options'])->toBeArray();
    expect($result['attributes'][0]['attribute_data']['options'])->toBeEmpty();
});

it('handles sequential numeric arrays correctly', function () {
    $discipline = Discipline::factory()->create();

    // Create attribute with sequential numeric array
    $attributeData = [
        'options' => ['1', '2', '3'],
    ];

    $attribute = Attribute::factory()->create([
        'name' => 'Sequential Array',
        'attribute_type' => 'SELECT',
        'fillable_global' => false,
        'attribute_data' => $attributeData,
    ]);

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    // It should convert sequential array to associative
    expect($result['attributes'][0]['attribute_data']['options'])
        ->toBe([
            '1' => '1',
            '2' => '2',
            '3' => '3',
        ]);
});

it('preserves required flag in attribute data', function () {
    $discipline = Discipline::factory()->create();

    // Create attribute with required flag
    $attribute = Attribute::factory()->create([
        'name' => 'Required Attribute',
        'attribute_type' => 'TEXT',
        'fillable_global' => false,
        'attribute_data' => [
            'required' => true,
        ],
    ]);

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    expect($result['attributes'][0]['attribute_data']['required'])->toBeTrue();
});

it('handles non-array attribute_data gracefully', function () {
    $discipline = Discipline::factory()->create();

    // Create an attribute with invalid attribute_data (non-array)
    $attribute = Attribute::make([
        'name' => 'Invalid Attribute',
        'attribute_type' => 'TEXT',
        'fillable_global' => false,
    ]);

    // Manually set attribute_data to a non-array value to simulate corrupted data
    $attribute->attribute_data = 'not an array';
    $attribute->save();

    $discipline->attributes()->attach($attribute);

    $action = new GetAttributesAndRulesFromDisciplineAction;
    $result = $action->execute($discipline->id);

    // It should handle this gracefully by converting to an empty array
    expect($result['attributes'][0]['attribute_data'])->not->toBe('not an array');
    expect($result['attributes'][0]['attribute_data'])->toBeArray();
});
