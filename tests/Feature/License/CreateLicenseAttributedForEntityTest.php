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
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=DocumentTypeSeeder');
});

it('does not create a document for a zero value license attributed to an entity', function () {

    $group = Group::factory()->create(['code' => 'FEDERATION', 'id' => 3]);

    $user = \App\Models\User::factory()->create([
        'group_id' => $group->id,
    ]);

    $federation = Federation::factory()->create(['is_local' => false]);

    // Create active membership for federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);

    $entity = Entity::factory()->create();
    $federation->entities()->attach($entity);

    // Create a license with unit_value as 0
    $license = License::factory()->create([
        'unit_value' => 0,
        'tax_value' => 23,
        'tax_percentage' => 23,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'entity',
        'entity_id' => $entity->id,
        'notes' => 'Test license with zero unit value',
    ];

    $this->actingAs($user)
        ->post(route('federation.license-attributed.store'), $data);

    // Assert no document is created
    expect(Document::count())->toBe(0);
});

it('does create a document for a license attributed to an entity', function () {

    $group = Group::factory()->create(['code' => 'FEDERATION', 'id' => 3]);

    $user = User::factory()->create([
        'group_id' => $group->id,
    ]);
    $federation = Federation::factory()->create(['is_local' => false]);

    // Create active membership for federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);

    $entity = Entity::factory()->create();

    // Ensure an active relationship between federation and entity
    $federation->entities()->attach($entity, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => true,
    ]);

    // Create a license with unit_value as 100
    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_entity' => 50,
        'tax_value' => 11.5,
        'tax_percentage' => 23,
        'requester_model' => Federation::class,
    ]);

    $data = [
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'entity',
        'entity_id' => $entity->id,
        'requester_model_type' => Federation::class,
        'notes' => 'Test license with price',
    ];

    $response = $this->actingAs($user)->post(route('federation.license-attributed.store'), $data);
    $response->assertRedirect();

    // Assert document is created
    $expectedTotalPrice = $license->unit_value + $license->tax_value;
    // Get the last license attributed
    $licenseAttributed = LicenseAttributed::latest()->first();
    $createdDocument = Document::with('details')->latest()->first();

    // Add more detailed assertions
    expect($licenseAttributed)->not->toBeNull();
    expect($createdDocument)->not->toBeNull();
    expect($createdDocument->total_value)->toEqual($expectedTotalPrice);
});

it('does create a document for a license attributed to an individual', function () {

    $group = Group::factory()->create([
        'code' => 'FEDERATION',
        'id' => 3,
    ]);
    $committee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Technical Committee',
    ]);

    $user = \App\Models\User::factory()->create([
        'group_id' => $group->id,
    ]);
    $federation_is_default = Federation::factory()->create(['is_default_federation' => true]);
    $federation = Federation::factory()->create(['is_local' => false]);

    // Create active memberships for both federations
    Membership::factory()->create([
        'federation_id' => $federation_is_default->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);

    $membership = Membership::factory()->create(['federation_id' => $federation->id]);
    $membershipPlan = MembershipPlan::factory()->create(['committee_id' => $committee->id]);
    $membership->plans()->attach($membershipPlan->id);
    $user->assignRole('federation-admin');
    $user->assignRole('association-sport-admin');
    $user->assignRole('association-admin');

    $individual = Individual::factory()->create();
    // Ensure an active relationship between federation and individual
    $federation->individuals()->attach($individual, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Create a license with unit_value as 100
    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 0,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => 'All',
    ]);

    $data = [
        'committee' => 'diving',
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'license_type_name' => 'individual',
        'notes' => 'Test license with price',
        'requester_model_type' => Federation::class,
        'individual' => [$individual->id],
    ];

    $response = $this->actingAs($user)->post(route('federation.license-attributed.store'), $data);
    $response->assertRedirect();

    // Assert document is created
    $expectedTotalPrice = $license->unit_value + $license->tax_value;

    // Get the last license attributed
    $licenseAttributed = LicenseAttributed::latest()->first();
    $createdDocument = Document::with('details')->latest()->first();

    expect($createdDocument)->not->toBeNull();
    expect($createdDocument->total_value)->toEqual($expectedTotalPrice);
});
