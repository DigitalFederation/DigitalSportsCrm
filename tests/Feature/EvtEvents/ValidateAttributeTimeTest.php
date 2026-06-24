<?php

use App\Enums\EvtAttributeRuleOperatorsEnum;
use Domain\EvtEvents\Actions\ValidateAttributeRulesAction;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->attribute = Attribute::factory()->create([
        'name' => 'Time Individual',
        'attribute_type' => 'TIME',
        'default_value' => '99:99:99,00',
        'fillable_type' => 'MANUAL',
        'fillable_global' => false,
        'required' => true,
        'attribute_data' => [
            'type' => 'TIME',
            'required' => true,
            'read_only' => false,
        ],
    ]);

    $this->rule = AttributeRules::factory()->create([
        'attribute_id' => $this->attribute->id,
        'operator' => EvtAttributeRuleOperatorsEnum::REGEX_MATCH->value,
        'default_value' => '',
        'name' => 'Time Format Validation',
        'is_validation' => true,
        'comparison_value' => '/^([0-5]?\\d):([0-5]?\\d)(\\.\\d{1,2})?$/',
    ]);

    $this->validateAttributeRulesAction = new ValidateAttributeRulesAction;
});

it('validates correct time format', function () {
    $value = [$this->attribute->id => '12:34.78'];
    $validation = $this->validateAttributeRulesAction->execute($value, [$this->rule]);

    expect($validation)->toBe(true);
});

it('rejects time format without centiseconds', function () {
    $value = [$this->attribute->id => '12:34'];

    expect(fn () => $this->validateAttributeRulesAction->execute($value, [$this->rule]))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});
