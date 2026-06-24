<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create groups
    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);

    // Create committees
    $this->committeeDiving = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);
    $this->committeeScientific = Committee::factory()->create([
        'code' => 'SCIENTIFIC',
        'name' => 'Scientific',
        'is_international' => true,
    ]);

    // Create professional roles
    $this->divingInstructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'DIVING_INSTRUCTOR',
        'name' => 'Diving Instructor',
        'committee_id' => $this->committeeDiving->id,
    ]);
    $this->scientificInstructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'SCIENTIFIC_INSTRUCTOR',
        'name' => 'Scientific Instructor',
        'committee_id' => $this->committeeScientific->id,
    ]);

    // Create licenses
    $this->divingLicense = License::factory()->create([
        'professional_role_id' => $this->divingInstructorRole->id,
        'committee_id' => $this->committeeDiving->id,
    ]);
    $this->scientificLicense = License::factory()->create([
        'professional_role_id' => $this->scientificInstructorRole->id,
        'committee_id' => $this->committeeScientific->id,
    ]);

    // Create federation
    $this->federation = Federation::factory()->create();

    // Create entity user
    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);

    // Create entity
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
    $this->entity->federations()->attach($this->federation->id);
});

// ============================================================================
// DIVING Committee Middleware Tests
// ============================================================================

test('entity with active diving license can access diving instructor page', function () {
    // Create active diving license for entity
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertStatus(200);
});

test('entity without diving license cannot access diving instructor page', function () {
    // No diving license for entity
    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('entity with expired diving license cannot access diving instructor page', function () {
    // Create expired diving license for entity
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ExpiredLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('entity with pending diving license cannot access diving instructor page', function () {
    // Create pending diving license for entity
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => PendingLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

// ============================================================================
// SCIENTIFIC Committee Middleware Tests
// ============================================================================

test('entity with active scientific license can access scientific instructor page', function () {
    // Create active scientific license for entity
    LicenseAttributed::factory()->create([
        'license_id' => $this->scientificLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.scientific-instructor.index'));

    $response->assertStatus(200);
});

test('entity without scientific license cannot access scientific instructor page', function () {
    // No scientific license for entity
    actingAs($this->entityUser);

    $response = get(route('entity.scientific-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('entity with expired scientific license cannot access scientific instructor page', function () {
    // Create expired scientific license for entity
    LicenseAttributed::factory()->create([
        'license_id' => $this->scientificLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ExpiredLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.scientific-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

// ============================================================================
// Cross-Committee Tests
// ============================================================================

test('entity with diving license but not scientific cannot access scientific page', function () {
    // Create active diving license for entity (NOT scientific)
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.scientific-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('entity with scientific license but not diving cannot access diving page', function () {
    // Create active scientific license for entity (NOT diving)
    LicenseAttributed::factory()->create([
        'license_id' => $this->scientificLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('entity with both diving and scientific licenses can access both pages', function () {
    // Create active diving license
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create active scientific license
    LicenseAttributed::factory()->create([
        'license_id' => $this->scientificLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $divingResponse = get(route('entity.international-diving-instructor.index'));
    $divingResponse->assertStatus(200);

    $scientificResponse = get(route('entity.scientific-instructor.index'));
    $scientificResponse->assertStatus(200);
});

// ============================================================================
// User Without Entity Tests
// ============================================================================

test('user without entity cannot access instructor pages via middleware', function () {
    $userWithoutEntity = User::factory()->create(['group_id' => $this->entityGroup->id]);
    actingAs($userWithoutEntity);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertStatus(403);
});

// ============================================================================
// Error Message Tests
// ============================================================================

test('middleware shows correct error message for inactive license', function () {
    // Create inactive (expired) diving license
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ExpiredLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertRedirect();
    // The error message should indicate they have an inactive license
    $response->assertSessionHas('error');
});

test('middleware shows correct error message for no license at all', function () {
    // No license for entity at all
    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertRedirect();
    // The error message should indicate they need a license
    $response->assertSessionHas('error');
});
