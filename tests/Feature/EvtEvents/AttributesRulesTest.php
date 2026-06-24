<?php

use App\Enums\EvtAttributeRuleOperatorsEnum;
use Domain\EvtEvents\Actions\CreateAttributesForDisciplineAction;
use Domain\EvtEvents\Actions\ValidateAttributeRulesAction;
use Domain\EvtEvents\Models\AttributeRules;
use Domain\EvtEvents\Models\Discipline;
use Illuminate\Validation\ValidationException;

it('can create an attribute', function () {
    $createAttributeAction = app(CreateAttributesForDisciplineAction::class);

    $attribute_values = \Domain\EvtEvents\Models\Attribute::factory()->make();

    $discipline = Discipline::factory()->create();
    $attributes = $createAttributeAction->execute($discipline, [$attribute_values->toArray()]);

    $this->assertDatabaseHas('evt_attributes', $attribute_values->toArray());
    $this->assertDatabaseHas('evt_discipline_attribute_association', [
        'discipline_id' => $discipline->id,
    ]);
});

it('can create an attribute rule', function () {
    $rule = AttributeRules::factory()->create();

    $this->assertDatabaseHas($rule->getTable(), $rule->toArray());
});

it('can validate attribute rule', function () {
    $attribute = \Domain\EvtEvents\Models\Attribute::factory()->create();

    $rule = AttributeRules::factory()->create([
        'operator' => EvtAttributeRuleOperatorsEnum::EQUAL->value,
        'attribute_id' => $attribute->id,
        'comparison_value' => 'test_value',
    ]);

    $this->assertDatabaseHas($rule->getTable(), $rule->toArray());

    $validateAction = app(ValidateAttributeRulesAction::class);

    expect(fn () => $validateAction->execute(
        [$attribute->id => 'wrong_value'],
        [$rule]
    ))->toThrow(ValidationException::class);

    $validation = $validateAction->execute(
        [$attribute->id => 'test_value'],
        [$rule]
    );

    expect($validation)->toBe(true);
});
