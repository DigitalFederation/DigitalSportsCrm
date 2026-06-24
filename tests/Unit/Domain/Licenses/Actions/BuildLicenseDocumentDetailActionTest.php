<?php

use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\BuildLicenseDocumentDetailAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new BuildLicenseDocumentDetailAction;
    $this->federation = Federation::factory()->create();
    $this->committee = \App\Models\Committee::factory()->create();
});

it('normalizes morph aliases correctly', function ($input, $expected) {
    $reflection = new ReflectionClass($this->action);
    $method = $reflection->getMethod('normalizeRequesterModel');
    $method->setAccessible(true);

    $result = $method->invoke($this->action, $input);

    expect($result)->toBe($expected);
})->with([
    ['entity', Entity::class],
    ['individual', Individual::class],
    [Entity::class, Entity::class],
    [Individual::class, Individual::class],
    ['some_other_value', 'some_other_value'],
]);

it('determines unit value correctly for entity with morph alias', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'unit_value' => 30.00,
        'unit_value_entity' => 50.00,
        'unit_value_individual' => 40.00,
    ]);

    $entity = Entity::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'entity', // Using morph alias
        'requested_by_id' => $entity->id,
        'total_value' => 50.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0])
        ->toBeInstanceOf(DocumentDetailData::class)
        ->and($result[0]->unit_value)
        ->toBe(50.00);
});

it('determines unit value correctly for individual with morph alias', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'unit_value' => 30.00,
        'unit_value_entity' => 50.00,
        'unit_value_individual' => 40.00,
    ]);

    $individual = Individual::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'requester_model_type' => 'individual', // Using morph alias
        'requested_by_id' => null, // Individual doesn't need requested_by_id
        'total_value' => 40.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0])
        ->toBeInstanceOf(DocumentDetailData::class)
        ->and($result[0]->unit_value)
        ->toBe(40.00);
});

it('uses fallback total_value when unit value is null', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'unit_value' => null,
        'unit_value_entity' => null,
        'unit_value_individual' => null,
    ]);

    $entity = Entity::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'total_value' => 75.00, // Fallback value
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0])
        ->toBeInstanceOf(DocumentDetailData::class)
        ->and($result[0]->unit_value)
        ->toBe(75.00); // Should use the fallback
});

it('handles license with only base unit_value correctly', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'unit_value' => 100.00,
        'unit_value_entity' => null,
        'unit_value_individual' => null,
    ]);

    $entity = Entity::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'total_value' => 100.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]->unit_value)
        ->toBe(100.00); // Should use base unit_value
});

it('includes holder name in description when provided', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'Test License',
        'unit_value_entity' => 50.00,
    ]);

    $entity = Entity::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'holder_name' => 'ABC Company',
        'total_value' => 50.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result[0]->description)->toBe('Test License - ABC Company');
});

it('handles multiple license attributed records', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'unit_value_entity' => 75.00,
    ]);

    $entity = Entity::factory()->create();
    $individual1 = Individual::factory()->create();
    $individual2 = Individual::factory()->create();

    $licenseAttributed1 = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual1->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'holder_name' => 'Person One',
        'total_value' => 75.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed2 = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual2->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'holder_name' => 'Person Two',
        'total_value' => 75.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed1->setRelation('license', $license);
    $licenseAttributed2->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed1, $licenseAttributed2]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($result[0]->unit_value)->toBe(75.00)
        ->and($result[1]->unit_value)->toBe(75.00)
        ->and($result[0]->description)->toContain('Person One')
        ->and($result[1]->description)->toContain('Person Two');
});

it('skips records when license is not found', function () {
    // Create a license attributed without setting the license relation
    $licenseAttributed = new LicenseAttributed;
    $licenseAttributed->id = \Illuminate\Support\Str::uuid();
    $licenseAttributed->license_id = 'non-existent';
    $licenseAttributed->federation_id = $this->federation->id;
    $licenseAttributed->model_type = 'entity';
    $licenseAttributed->model_id = 'test-id';
    $licenseAttributed->requester_model_type = 'entity';
    $licenseAttributed->total_value = 50.00;
    $licenseAttributed->status_class = PendingLicenseAttributedState::class;

    // Don't set the license relation, simulating a missing license

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)->toBeArray()->toHaveCount(0);
});

it('handles legacy array format for backward compatibility', function () {
    $licenseArray = [
        'id' => 'license-1',
        'name' => 'Legacy License',
        'unit_value' => 30.00,
        'unit_value_entity' => 50.00,
        'unit_value_individual' => 40.00,
        'tax_value' => 5.00,
        'tax_percentage' => 10,
    ];

    $attributedArray = [
        'id' => 'attributed-1',
        'license' => $licenseArray,
        'requester_model_type' => 'entity',
        'holder_name' => 'Legacy Holder',
    ];

    $result = (new BuildLicenseDocumentDetailAction)([$attributedArray]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0])
        ->toBeInstanceOf(DocumentDetailData::class)
        ->and($result[0]->unit_value)->toBe(50.00)
        ->and($result[0]->tax_value)->toBe(5.00)
        ->and($result[0]->tax_percentage)->toBe(10.0)
        ->and($result[0]->description)->toBe('Legacy License - Legacy Holder');
});

it('includes moloni_reference in document detail when license has one', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'License with Moloni Ref',
        'unit_value' => 100.00,
        'moloni_reference' => 'LIC-001',
    ]);

    $entity = Entity::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'total_value' => 100.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0])
        ->toBeInstanceOf(DocumentDetailData::class)
        ->and($result[0]->reference)->toBe('LIC-001');
});

it('returns null reference when license has no moloni_reference', function () {
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'License without Moloni Ref',
        'unit_value' => 100.00,
        'moloni_reference' => null,
    ]);

    $entity = Entity::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'total_value' => 100.00,
        'status_class' => PendingLicenseAttributedState::class,
    ]);

    $licenseAttributed->setRelation('license', $license);

    $result = (new BuildLicenseDocumentDetailAction)([$licenseAttributed]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]->reference)->toBeNull();
});

it('includes moloni_reference in legacy array format', function () {
    $licenseArray = [
        'id' => 'license-1',
        'name' => 'Legacy License',
        'unit_value' => 50.00,
        'tax_value' => 5.00,
        'tax_percentage' => 10,
        'moloni_reference' => 'LEGACY-REF-001',
    ];

    $attributedArray = [
        'id' => 'attributed-1',
        'license' => $licenseArray,
        'requester_model_type' => 'entity',
    ];

    $result = (new BuildLicenseDocumentDetailAction)([$attributedArray]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]->reference)->toBe('LEGACY-REF-001');
});

it('handles null moloni_reference in legacy array format', function () {
    $licenseArray = [
        'id' => 'license-1',
        'name' => 'Legacy License',
        'unit_value' => 50.00,
        'tax_value' => 5.00,
        'tax_percentage' => 10,
        // No moloni_reference key
    ];

    $attributedArray = [
        'id' => 'attributed-1',
        'license' => $licenseArray,
        'requester_model_type' => 'entity',
    ];

    $result = (new BuildLicenseDocumentDetailAction)([$attributedArray]);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]->reference)->toBeNull();
});
