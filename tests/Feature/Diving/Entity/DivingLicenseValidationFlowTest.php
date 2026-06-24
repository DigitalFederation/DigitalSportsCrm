<?php

use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin group first
    $adminGroup = \App\Models\Group::firstOrCreate([
        'id' => \App\Enums\UserGroupEnum::ADMIN->value,
        'code' => 'ADMIN',
        'name' => 'Admin',
    ]);

    // Create admin user (needed for CreatedUpdatedBy trait)
    $this->admin = User::factory()->create([
        'group_id' => $adminGroup->id,
    ]);

    // Create or get admin role and permission
    $adminRole = \App\Models\Role::firstOrCreate(
        ['name' => 'admin'],
        ['guard_name' => 'web']
    );

    // Create permission for diving certifications
    $permission = \App\Models\Permission::firstOrCreate([
        'name' => 'access diving certifications attributed',
        'guard_name' => 'web',
    ]);

    // Give the role the permission
    $adminRole->givePermissionTo($permission);

    $this->admin->assignRole($adminRole);

    // Create or get ENTITY group
    $entityGroup = \App\Models\Group::firstOrCreate([
        'code' => 'ENTITY',
    ], [
        'name' => 'Entity',
    ]);

    // Create entity with user (must have ENTITY group for middleware)
    $this->entityUser = User::factory()->create([
        'group_id' => $entityGroup->id,
    ]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);

    // Store entity ID for debugging
    $this->entityId = $this->entity->id;

    // Create diving license that requires admin validation
    // Use DIVINGSERVICES (non-international) which requires TD approval for entities
    // Note: DIVING committee is international and skips TD approval
    $committee = \App\Models\Committee::where('code', 'DIVINGSERVICES')->first();
    if (! $committee) {
        $committee = \App\Models\Committee::factory()->create([
            'code' => 'DIVINGSERVICES',
            'is_international' => false, // DIVINGSERVICES is non-international
        ]);
    }

    $this->license = License::factory()->create([
        'committee_id' => $committee->id,
        'name' => 'Test Diving Services License',
        'requires_admin_validation' => true,
        'unit_value_entity' => 100.00,
        'requester_model' => Entity::class, // Ensure it's for entities
    ]);

    // Ensure entity has active affiliation
    $federation = \Domain\Federations\Models\Federation::factory()->create();
    $this->entity->federations()->attach($federation, [
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
    ]);

    // Create an active affiliation for the entity - ensure ID is passed correctly
    $affiliation = \Domain\Memberships\Models\Affiliation::factory()->create([
        'member_type' => Entity::class,
        'member_id' => $this->entityId,
        'status_class' => \Domain\Memberships\States\ActiveAffiliationState::class,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
    ]);

    // Verify creation - note member_id is stored as string in polymorphic relationships
    $created = \Domain\Memberships\Models\Affiliation::find($affiliation->id);
    expect($created)->not->toBeNull();
    expect($created->member_id)->toBe((string) $this->entityId);
});

test('entity requests diving license which goes to pending technical director approval state', function () {
    // Act as entity user
    $this->actingAs($this->entityUser);

    // Mock the entity to bypass affiliation check
    $entityMock = \Mockery::mock($this->entity)->makePartial();
    $entityMock->shouldReceive('hasActiveAffiliation')->andReturn(true);

    // Mock validation plan privilege service to allow license request
    $validationService = \Mockery::mock(\Domain\Memberships\Services\ValidationPlanPrivilegeService::class);
    $validationService->shouldReceive('canRequestLicense')->andReturn(true);
    $validationService->shouldReceive('getValidationPlanReason')->andReturn('');
    app()->instance(\Domain\Memberships\Services\ValidationPlanPrivilegeService::class, $validationService);

    // Create license request using the action
    $purchaseAction = app(\Domain\Licenses\Actions\PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($this->license, $entityMock);

    // Assert license is in pending technical director approval state (NEW WORKFLOW)
    expect($licenseAttributed->status_class)->toBe(PendingTechnicalDirectorApprovalLicenseAttributedState::class);
    expect($licenseAttributed->total_value)->toBeGreaterThan(0);

    // Assert no document was created yet (documents are created after all approvals)
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $this->entity->id)
        ->first();
    expect($document)->toBeNull();
});

test('admin cannot validate license that is still pending technical director approval', function () {
    // Create license in pending technical director approval state (the new first step)
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'total_value' => 100.00,
    ]);

    // Act as admin
    $this->actingAs($this->admin);

    // Try to approve the license before technical director approval
    $response = $this->post(route('admin.entity_diving_license_validation.approve', $licenseAttributed), [
        'notes' => 'Trying to approve before technical director',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', __('diving.license_not_pending_validation'));

    // Reload license attributed
    $licenseAttributed->refresh();

    // Assert license state didn't change
    expect($licenseAttributed->status_class)->toBe(PendingTechnicalDirectorApprovalLicenseAttributedState::class);
    expect($licenseAttributed->validation_notes)->toBeNull();
    expect($licenseAttributed->validated_by)->toBeNull();
    expect($licenseAttributed->validated_at)->toBeNull();
});

test('admin can approve diving license which generates payment document', function () {
    // Create license in pending validation state (after technical director approval)
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'total_value' => 100.00,
    ]);

    // Act as admin
    $this->actingAs($this->admin);

    // Approve the license
    $response = $this->post(route('admin.entity_diving_license_validation.approve', $licenseAttributed), [
        'notes' => 'Approved for testing',
    ]);

    $response->assertRedirect(route('admin.entity_diving_license_validation.index'));
    $response->assertSessionHas('success');

    // Reload license attributed
    $licenseAttributed->refresh();

    // Assert license is now in pending payment state
    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);
    expect($licenseAttributed->validation_notes)->toBe('Approved for testing');
    expect($licenseAttributed->validated_by)->toBe($this->admin->id);
    expect($licenseAttributed->validated_at)->not->toBeNull();

    // Assert payment document was created
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $this->entity->id)
        ->first();

    expect($document)->not->toBeNull();
    expect($document->status_class)->toBe(PendingDocumentState::class);
    expect($document->total_value)->toBeGreaterThan(0); // Value includes tax
});

test('admin can reject diving license with reason visible to entity', function () {
    // Create license in pending validation state
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'total_value' => 100.00,
    ]);

    // Act as admin
    $this->actingAs($this->admin);

    // Reject the license
    $response = $this->post(route('admin.entity_diving_license_validation.reject', $licenseAttributed), [
        'reason' => 'Missing required documentation',
    ]);

    $response->assertRedirect(route('admin.entity_diving_license_validation.index'));
    $response->assertSessionHas('success');

    // Reload license attributed
    $licenseAttributed->refresh();

    // Assert license is now canceled with rejection reason
    expect($licenseAttributed->status_class)->toBe(CanceledLicenseAttributedState::class);
    expect($licenseAttributed->validation_notes)->toBe('Missing required documentation');
    expect($licenseAttributed->validated_by)->toBe($this->admin->id);
    expect($licenseAttributed->validated_at)->not->toBeNull();
    expect($licenseAttributed->cancelled_at)->not->toBeNull();

    // Assert no payment document was created
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $this->entity->id)
        ->first();
    expect($document)->toBeNull();
});

test('entity can see rejection reason on license detail page', function () {
    // Create rejected license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => CanceledLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'validation_notes' => 'Incomplete technical director information',
        'cancelled_at' => now(),
    ]);

    // Act as entity user
    $this->actingAs($this->entityUser);

    // View license detail page
    $response = $this->get(route('entity.diving_licenses.show', $licenseAttributed));

    $response->assertOk();
    // Use translation keys - works regardless of locale
    $response->assertSee(__('diving.license_validation_rejected'));
    $response->assertSee(__('diving.rejection_reason'));
    $response->assertSee('Incomplete technical director information');
});

test('entity can see pending payment status after approval', function () {
    // Create approved license pending payment
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'total_value' => 100.00,
    ]);

    // Act as entity user
    $this->actingAs($this->entityUser);

    // View license detail page
    $response = $this->get(route('entity.diving_licenses.show', $licenseAttributed));

    $response->assertOk();
    // Use translation key - works regardless of locale
    $response->assertSee(__('diving.license_approved_pending_payment'));
});

test('license becomes active after payment is completed', function () {
    // Create license in pending payment state
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'total_value' => 100.00,
    ]);

    // Create associated payment document
    $document = Document::factory()->create([
        'owner_type' => 'entity',
        'owner_id' => $this->entity->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
    ]);

    // Simulate payment completion (this would normally be done by payment gateway)
    $calculateDatesAction = app(\Domain\Licenses\Actions\CalculateLicenseValidityDatesAction::class);
    $transition = new \Domain\Licenses\States\PendingToActiveTransition($calculateDatesAction);
    $transition($licenseAttributed);

    // Reload license attributed
    $licenseAttributed->refresh();

    // Assert license is now active
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($licenseAttributed->activated_at)->not->toBeNull();
});

test('non-admin users cannot access validation pages', function () {
    // Create regular entity user
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
    ]);

    // Act as entity user (not admin)
    $this->actingAs($this->entityUser);

    // Try to access admin validation pages
    $response = $this->get(route('admin.entity_diving_license_validation.index'));
    $response->assertForbidden();

    $response = $this->get(route('admin.entity_diving_license_validation.show', $licenseAttributed));
    $response->assertForbidden();

    $response = $this->post(route('admin.entity_diving_license_validation.approve', $licenseAttributed));
    $response->assertForbidden();

    $response = $this->post(route('admin.entity_diving_license_validation.reject', $licenseAttributed));
    $response->assertForbidden();
});

test('rejection requires a reason', function () {
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
    ]);

    $this->actingAs($this->admin);

    // Try to reject without reason
    $response = $this->post(route('admin.entity_diving_license_validation.reject', $licenseAttributed), [
        'reason' => '',
    ]);

    $response->assertSessionHasErrors(['reason']);

    // Verify license state didn't change
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(PendingValidationLicenseAttributedState::class);
});

test('free licenses are automatically activated upon approval', function () {
    // Create free license
    $freeLicense = License::factory()->create([
        'committee_id' => $this->license->committee_id,
        'name' => 'Free Test License',
        'requires_admin_validation' => true,
        'unit_value_entity' => 0,
    ]);

    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $freeLicense->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'total_value' => 0,
    ]);

    $this->actingAs($this->admin);

    // Approve the free license
    $response = $this->post(route('admin.entity_diving_license_validation.approve', $licenseAttributed), [
        'notes' => 'Free license approved',
    ]);

    $response->assertRedirect();

    // Reload and verify it went directly to active
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($licenseAttributed->activated_at)->not->toBeNull();

    // Verify no payment document was created
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $this->entity->id)
        ->first();
    expect($document)->toBeNull();
});

test('cannot approve license not in pending validation state', function () {
    // Create already active license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => ActiveLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
    ]);

    $this->actingAs($this->admin);

    // Try to approve already active license
    $response = $this->post(route('admin.entity_diving_license_validation.approve', $licenseAttributed), [
        'notes' => 'Trying to approve',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', __('diving.license_not_pending_validation'));
});

test('cannot reject license not in pending validation state', function () {
    // Create already canceled license
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => CanceledLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
    ]);

    $this->actingAs($this->admin);

    // Try to reject already canceled license
    $response = $this->post(route('admin.entity_diving_license_validation.reject', $licenseAttributed), [
        'reason' => 'Trying to reject',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', __('diving.license_not_pending_validation'));
});

test('admin can search licenses in validation index', function () {
    // Create multiple licenses - must be entity type to show in entity validation page
    $license1 = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'holder_name' => 'Test Entity One',
        'license_name' => 'Diving School License',
    ]);

    $license2 = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'holder_name' => 'Another Entity',
        'license_name' => 'Diving Center License',
    ]);

    $this->actingAs($this->admin);

    // Search by entity name
    $response = $this->get(route('admin.entity_diving_license_validation.index', ['search' => 'Test Entity']));
    $response->assertOk();
    $response->assertSee('Test Entity One');
    $response->assertDontSee('Another Entity');

    // Search by license name
    $response = $this->get(route('admin.entity_diving_license_validation.index', ['search' => 'Diving Center']));
    $response->assertOk();
    $response->assertSee('Another Entity');
    $response->assertDontSee('Test Entity One');
});

test('validation tracks user and timestamp information', function () {
    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
    ]);

    $this->actingAs($this->admin);

    // Approve and check tracking
    $this->post(route('admin.entity_diving_license_validation.approve', $licenseAttributed), [
        'notes' => 'Approved with tracking',
    ]);

    $licenseAttributed->refresh();
    expect($licenseAttributed->validated_by)->toBe($this->admin->id);
    expect($licenseAttributed->validated_at)->not->toBeNull();
    expect($licenseAttributed->validated_at->diffInSeconds(now()))->toBeLessThan(5);
});
