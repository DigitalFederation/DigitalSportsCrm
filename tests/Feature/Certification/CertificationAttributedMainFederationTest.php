<?php

use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
        'name' => 'Main Federation',
        'legal_name' => 'Main Federation Legal Name',
    ]);

    $this->localFederation = Federation::factory()->create([
        'is_default_federation' => false,
        'is_local' => true,
        'parent_id' => $this->mainFederation->id,
        'name' => 'Local Federation',
    ]);

    $this->certification = Certification::factory()->create();
    $this->individual = Individual::factory()->create();
});

test('certification attributed creation auto-corrects local federation to main federation', function () {
    $certificationAttributed = CertificationAttributed::create([
        'certification_id' => $this->certification->id,
        'federation_id' => $this->localFederation->id, // Attempt to use local
        'individual_id' => $this->individual->id,
        'holder_name' => 'Test Holder',
        'certification_name' => $this->certification->name,
        'federation_name' => 'Local Federation',
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    expect($certificationAttributed->federation_id)->toBe($this->mainFederation->id)
        ->and($certificationAttributed->federation_name)->toBe($this->mainFederation->legal_name);
});

test('certification attributed creation uses main federation when none provided', function () {
    $certificationAttributed = CertificationAttributed::create([
        'certification_id' => $this->certification->id,
        'individual_id' => $this->individual->id,
        'holder_name' => 'Test Holder',
        'certification_name' => $this->certification->name,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    expect($certificationAttributed->federation_id)->toBe($this->mainFederation->id);
});

test('certification attributed creation keeps main federation when explicitly provided', function () {
    $certificationAttributed = CertificationAttributed::create([
        'certification_id' => $this->certification->id,
        'federation_id' => $this->mainFederation->id,
        'individual_id' => $this->individual->id,
        'holder_name' => 'Test Holder',
        'certification_name' => $this->certification->name,
        'federation_name' => 'Main Federation',
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    expect($certificationAttributed->federation_id)->toBe($this->mainFederation->id);
});

test('certification attributed rejects child modalidade federation', function () {
    $modalidadeFederation = Federation::factory()->create([
        'is_default_federation' => false,
        'is_local' => false, // Not local = modalidade
        'parent_id' => $this->mainFederation->id,
        'name' => 'International Diving Federation',
    ]);

    $certificationAttributed = CertificationAttributed::create([
        'certification_id' => $this->certification->id,
        'federation_id' => $modalidadeFederation->id, // Attempt to use modalidade
        'individual_id' => $this->individual->id,
        'holder_name' => 'Test Holder',
        'certification_name' => $this->certification->name,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    // Should be corrected to main federation
    expect($certificationAttributed->federation_id)->toBe($this->mainFederation->id);
});
