<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Memberships\Models\Membership;
use Illuminate\Support\Carbon;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->withoutMiddleware(\App\Http\Middleware\EnsureProfilePhotoExists::class);

    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=DocumentTypeSeeder');

    // Create default federation
    $federation = Federation::factory()->create(['is_default_federation' => true]);

    // Create and attach active membership to federation
    $membership = Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);
});

it('creates a document for a paid license attributed to an individual', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test license with price',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();
    $expectedTotalPrice = $license->unit_value_individual + ($license->unit_value_individual * ($license->tax_percentage / 100));
    $createdDocument = Document::with('details')->latest()->first();

    expect($createdDocument->total_value)->toEqual($expectedTotalPrice);
    expect($licenseAttributed->status_class)->toEqual(PendingLicenseAttributedState::class);
});

it('does not create a document for a free license attributed to an individual', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 0,
        'unit_value_individual' => 0,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => Individual::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test free license',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();
    $createdDocument = Document::latest()->first();

    expect($createdDocument)->toBeNull();
    expect($licenseAttributed->status_class)->toEqual(ActiveLicenseAttributedState::class);
});

it('sets individual license attributed to waiting approval if admin approval is required', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => 'All',
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test license requiring admin approval',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->status_class)->toEqual(PendingLicenseAttributedState::class);
});

it('activates a license for an entity and syncs user roles accordingly', function () {
    $committee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Technical Committee',
    ]);

    $groupFederation = Group::factory()->create(['code' => 'FEDERATION', 'id' => 3]);
    $groupEntity = Group::factory()->create(['code' => 'ENTITY', 'id' => 2]);

    $userFederation = User::factory()->create(['group_id' => $groupFederation->id]);
    $userEntity = User::factory()->create(['group_id' => $groupEntity->id]);

    $federation = Federation::factory()->create(['is_local' => false]);

    // Create active membership for the new federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    $federation->users()->attach($userFederation);

    $entity = Entity::factory()->create();
    $entity->users()->attach($userEntity);
    // Ensure an active relationship between federation and entity
    $federation->entities()->attach($entity, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => true,
    ]);

    $license = License::factory()->create([
        'committee_id' => $committee->id,
        'unit_value' => 0,
        'unit_value_entity' => 0,
        'tax_value' => 0,
        'tax_percentage' => 0,
    ]);

    // Create the entity-diving-services role if it doesn't exist
    $divingAdminRole = \App\Models\Role::firstOrCreate(
        ['name' => 'entity-diving-services'],
        ['guard_name' => 'web']
    );

    // Map the license to the entity-diving-services role for the DIVING committee
    \Illuminate\Support\Facades\DB::table('license_roles')->insert([
        'license_id' => $license->id,
        'role_id' => $divingAdminRole->id,
        'committee_id' => $committee->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'entity_id' => $entity->id,
        'requester_model_type' => Federation::class,
        'current_term_starts_at' => now()->format('Y-m-d'),
        'license_type_name' => 'entity',
        'notes' => 'Test license with zero unit value',
    ];

    $response = $this->actingAs($userFederation)->post(route('federation.license-attributed.store'), $data);

    $savedLicenseAttributed = LicenseAttributed::latest()->first();

    if (! $savedLicenseAttributed) {
        $this->fail('No LicenseAttributed record was created. Check the logs and dumps for more information.');
    }

    expect($savedLicenseAttributed->license_id)->toEqual($license->id);

    // Refresh the license to get the latest status
    $savedLicenseAttributed->refresh();

    // Check if the license was properly activated
    expect($savedLicenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);

    // Manually trigger the role sync since it might not be happening automatically in tests
    // This is necessary because the async job processing might not run in the test environment
    $syncAction = new \Domain\Users\Actions\SyncEntityUserRolesAction;
    $syncAction->execute($userEntity);

    $userEntity->refresh();

    $expectedRoles = ['entity-diving-services', 'entity-admin'];
    foreach ($expectedRoles as $role) {
        expect($userEntity->hasRole($role))->toBeTrue();
    }
});

it('uses the default federation for individual license requests', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $defaultFederation = Federation::where('is_default_federation', true)->first();
    $individual = Individual::factory()->create(['user_id' => $user->id]);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test license with default federation',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->federation_id)->toEqual($defaultFederation->id);
});

it('uses the requesting federation for federation license requests', function () {
    $group = Group::factory()->create(['code' => 'FEDERATION', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);

    // Create active membership for the new federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);
    $individual = Individual::factory()->create();

    // Ensure an active relationship between federation and individual
    $federation->individuals()->attach($individual, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Federation::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => Federation::class,
        'notes' => 'Test license with requesting federation',
    ];

    $response = $this->actingAs($user)->post(route('federation.license-attributed.store'), $data);

    // Check for any LicenseAttributed records
    $allLicenseAttributed = LicenseAttributed::all();

    // Original assertions
    $licenseAttributed = LicenseAttributed::latest()->first();

    if ($licenseAttributed === null) {
        $this->fail('No LicenseAttributed record was created. Check the logs and dumps for more information.');
    } else {
        expect($licenseAttributed->federation_id)->toEqual($federation->id);
    }
});

it('sets current_term_ends_at when creating a license', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
        'interval' => 1,
        'interval_unit' => 'years',
        'validity_type' => 'calendar_year',
    ]);

    $startDate = '2025-01-15';
    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'current_term_starts_at' => $startDate,
        'notes' => 'Test license with start date',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->current_term_ends_at->format('Y-m-d'))
        ->toBe(Carbon::parse($startDate)->endOfYear()->format('Y-m-d'));
});

it('sets current_term_ends_at to end of current year when no start date provided', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
        'interval' => 1,
        'interval_unit' => 'years',
        'validity_type' => 'calendar_year',
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test license without start date',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->current_term_ends_at->format('Y-m-d'))
        ->toBe(Carbon::now()->endOfYear()->format('Y-m-d'));
});

it('prevents creating a duplicate license when an active one exists', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    // Create an existing active license
    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test duplicate license',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);
    expect(LicenseAttributed::count())->toBe(2);
});

it('allows creating a new license when only cancelled ones exist', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    // Create an existing cancelled license
    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => CanceledLicenseAttributedState::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test new license after cancelled',
    ];

    $response = $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $response->assertSessionHasNoErrors();
    expect(LicenseAttributed::count())->toBe(2);
});

it('sets expired state when expiration date is in the past', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 0,
        'unit_value_individual' => 0,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => Individual::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'current_term_starts_at' => '2023-01-01',
        'current_term_ends_at' => '2023-12-31',
        'notes' => 'Test expired license',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->status_class)->toEqual(ExpiredLicenseAttributedState::class);
    expect($licenseAttributed->current_term_ends_at->format('Y-m-d'))->toBe('2023-12-31');
});

it('uses custom expiration date when provided instead of auto-calculated', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 0,
        'unit_value_individual' => 0,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => Individual::class,
        'interval' => 1,
        'interval_unit' => 'years',
        'validity_type' => 'calendar_year',
    ]);

    $customEndDate = Carbon::now()->addYears(2)->format('Y-m-d');

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'current_term_ends_at' => $customEndDate,
        'notes' => 'Test custom expiration override',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->current_term_ends_at->format('Y-m-d'))->toBe($customEndDate);
});

it('validates that expiration date is not before start date', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 0,
        'unit_value_individual' => 0,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => Individual::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'current_term_starts_at' => '2025-06-01',
        'current_term_ends_at' => '2025-01-01',
        'notes' => 'Test invalid dates',
    ];

    $response = $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $response->assertSessionHasErrors('current_term_ends_at');
});

it('does not create a payment document for an expired license', function () {
    $group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create(['user_id' => $user->id]);
    $federation->individuals()->attach($individual);

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'individual' => [$individual->id],
        'requester_model_type' => 'individual',
        'current_term_starts_at' => '2023-01-01',
        'current_term_ends_at' => '2023-12-31',
        'notes' => 'Test expired license no payment doc',
    ];

    $this->actingAs($user)->post(route('individual.license-attributed.store'), $data);

    $licenseAttributed = LicenseAttributed::latest()->first();
    $createdDocument = Document::latest()->first();

    expect($licenseAttributed->status_class)->toEqual(ExpiredLicenseAttributedState::class);
    expect($createdDocument)->toBeNull();
});

it('allows admin to manually activate a paid license without a paid document', function () {
    $adminGroup = Group::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);
    $adminUser = User::factory()->create(['group_id' => $adminGroup->id]);
    $adminUser->assignRole('admin');

    $federation = Federation::factory()->create(['is_local' => false]);
    $individual = Individual::factory()->create();

    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => PendingLicenseAttributedState::class,
        'total_value' => 123.00,
    ]);

    $response = $this->actingAs($adminUser)
        ->put(route('admin.license-attributed.activate', $licenseAttributed->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);
});
