<?php

use App\Models\Sport;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Diagnostics\Actions\GenerateIndividualProfileDiagnosticAction;
use Domain\Diagnostics\Data\IndividualProfileDiagnostic;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->individual = Individual::factory()->create();
    $this->federation = Federation::factory()->create(['is_local' => false, 'is_default_federation' => true]);
    $this->localFederation = Federation::factory()->create(['is_local' => true, 'is_default_federation' => false]);
    $this->entity = Entity::factory()->create();
    $this->sport = Sport::factory()->create();

    $this->action = app(GenerateIndividualProfileDiagnosticAction::class);
});

test('returns IndividualProfileDiagnostic instance', function () {
    $result = $this->action->execute($this->individual);

    expect($result)->toBeInstanceOf(IndividualProfileDiagnostic::class);
});

test('includes individual data in result', function () {
    $result = $this->action->execute($this->individual);

    expect($result->individual->id)->toBe($this->individual->id);
});

test('detects active federation membership', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->federationMemberships)->toHaveCount(1);
    expect($result->federationMemberships[0]['is_active'])->toBeTrue();
    expect($result->federationMemberships[0]['name'])->toBe($this->federation->name);
});

test('detects pending federation membership', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => PendingIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->federationMemberships)->toHaveCount(1);
    expect($result->federationMemberships[0]['is_active'])->toBeFalse();
});

test('detects local vs main federation type', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);
    $this->individual->individualFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual);

    $mainMembership = collect($result->federationMemberships)->firstWhere('is_default', true);
    $localMembership = collect($result->federationMemberships)->firstWhere('is_local', true);

    expect($mainMembership)->not->toBeNull();
    expect($localMembership)->not->toBeNull();
});

test('detects active entity membership', function () {
    $this->individual->individualEntities()->create([
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->entityMemberships)->toHaveCount(1);
    expect($result->entityMemberships[0]['is_active'])->toBeTrue();
});

test('detects athlete registration with sport', function () {
    $this->individual->individualEntities()->create([
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    EntityAthlete::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport->id,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->entityMemberships[0]['sports'])->toContain($this->sport->name);
});

test('detects professional roles', function () {
    $role = ProfessionalRole::factory()->create(['role' => 'COACH', 'name' => 'Coach']);
    $this->individual->professionalRoles()->attach($role->id);

    $result = $this->action->execute($this->individual);

    expect($result->professionalRoles)->toHaveCount(1);
    expect($result->professionalRoles[0]['role'])->toBe('COACH');
    expect($result->professionalRoles[0]['display_name'])->toBe('Coach');
});

test('detects active licenses', function () {
    $license = License::factory()->create();

    // Link license to federation via pivot
    $this->federation->licenses()->attach($license->id);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual', // Use morph map alias
        'model_id' => $this->individual->id,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->activeLicenses)->toHaveCount(1);
    expect($result->activeLicenses[0]['status'])->toBe('Active');
});

test('detects active certifications', function () {
    $refereeRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $certification = Certification::factory()->create(['professional_role_id' => $refereeRole->id]);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->certifications)->toHaveCount(1);
    expect($result->certifications[0]['is_active'])->toBeTrue();
    expect($result->certifications[0]['grants_role'])->toBe('TECHNICAL_OFFICIAL');
});

test('flags pending certifications needing activation', function () {
    $refereeRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $certification = Certification::factory()->create(['professional_role_id' => $refereeRole->id]);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $certification->id,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->certifications[0]['action_needed'])->not->toBeNull();
});

test('quick status shows athlete eligible when registered', function () {
    // Setup: Active federation, active entity, registered as athlete
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);
    $this->individual->individualEntities()->create([
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    EntityAthlete::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport->id,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->quickStatus['athlete']['eligible'])->toBeTrue();
    expect($result->canBeAthlete())->toBeTrue();
});

test('quick status shows athlete not eligible without entity membership', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->quickStatus['athlete']['eligible'])->toBeFalse();
    expect($result->canBeAthlete())->toBeFalse();
});

test('quick status shows referee eligible with active certification and role', function () {
    $refereeRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $certification = Certification::factory()->create(['professional_role_id' => $refereeRole->id]);

    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $this->individual->professionalRoles()->attach($refereeRole->id);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $certification->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->quickStatus['referee']['eligible'])->toBeTrue();
    expect($result->canBeReferee())->toBeTrue();
});

test('quick status shows referee not eligible with pending certification', function () {
    $refereeRole = ProfessionalRole::factory()->create(['role' => 'TECHNICAL_OFFICIAL']);
    $certification = Certification::factory()->create(['professional_role_id' => $refereeRole->id]);

    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    CertificationAttributed::factory()->create([
        'individual_id' => $this->individual->id,
        'certification_id' => $certification->id,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->quickStatus['referee']['eligible'])->toBeFalse();
    expect($result->quickStatus['referee']['reason'])->not->toBeNull();
});

test('quick status shows official eligible with any active membership', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual);

    expect($result->quickStatus['official']['eligible'])->toBeTrue();
    expect($result->canBeOfficial())->toBeTrue();
});

test('toArray returns all diagnostic data', function () {
    $this->individual->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $result = $this->action->execute($this->individual);
    $array = $result->toArray();

    expect($array)->toHaveKeys([
        'individual',
        'federationMemberships',
        'entityMemberships',
        'professionalRoles',
        'activeLicenses',
        'certifications',
        'quickStatus',
    ]);
    expect($array['individual'])->toHaveKeys(['id', 'name', 'member_code', 'email']);
});
