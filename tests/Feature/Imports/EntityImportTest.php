<?php

use App\Imports\EntityImport;
use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Imports\Actions\BulkInsertEntitiesAction;
use Domain\Imports\Actions\ValidateEntityBulkDataAction;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();

    // Create the ADMIN group if it doesn't exist
    $group = Group::firstOrCreate(
        ['code' => 'ADMIN'],
        ['name' => 'Admin', 'description' => 'Admin Group']
    );

    // Create the permission if it doesn't exist
    Permission::firstOrCreate(['name' => 'access entities']);

    // Create the admin role if it doesn't exist
    $role = Role::firstOrCreate(['name' => 'admin']);
    $role->givePermissionTo('access entities');

    $this->user = User::factory()->create(['group_id' => $group->id]);
    $this->user->assignRole('admin');
    $this->actingAs($this->user);

    // Ensure we have a default federation
    if (! Federation::where('is_default_federation', 1)->exists()) {
        Federation::factory()->create(['is_default_federation' => 1]);
    }

    // Ensure we have a country
    if (! Country::where('name', 'Portugal')->exists()) {
        Country::factory()->create(['name' => 'Portugal']);
    }
});

test('user can access entity import page', function () {
    $response = $this->get(route('admin.entity.import.index'));

    $response->assertStatus(200);
    $response->assertSee(__('import.entity_import_title'));
});

test('user can download entity import template', function () {
    $response = $this->get(route('admin.entity.import.template'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    $response->assertDownload('entity_import_template.csv');
});

test('entity import class returns supported fields', function () {
    $fields = EntityImport::getSupportedFields();

    expect($fields)->toBeArray();
    expect($fields)->toHaveKey('name');
    expect($fields)->toHaveKey('email');
    expect($fields)->toHaveKey('district_id');
    expect($fields['name']['required'])->toBeTrue();
    // country_id is NOT in supported fields - it's auto-set from Main Federation
    expect($fields)->not->toHaveKey('country_id');
});

test('validates required name field', function () {
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => '',
            'country_id' => 1,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['errors'])->not->toBeEmpty();
    expect($results['valid'])->toBeEmpty();
});

test('entity without country_id is valid - country is auto-set from Main Federation', function () {
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            // country_id is NOT provided - will be auto-set from Main Federation
        ],
    ];

    $results = $validateAction->execute($entities);

    // Should be valid since country_id is auto-set
    expect($results['errors'])->toBeEmpty();
    expect($results['valid'])->not->toBeEmpty();
});

test('validates valid entity data', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country_id' => $country->id,
            'email' => 'test@example.com',
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['errors'])->toBeEmpty();
    expect($results['valid'])->not->toBeEmpty();
});

test('resolves country name to id', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country' => $country->name,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['valid'])->not->toBeEmpty();
    $validEntity = array_values($results['valid'])[0];
    expect($validEntity['country_id'])->toBe($country->id);
});

test('detects duplicate member numbers', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);

    // Create existing entity with member number
    Entity::factory()->create([
        'member_number' => 12345,
        'country_id' => $country->id,
    ]);

    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'New Entity',
            'country_id' => $country->id,
            'member_number' => 12345,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['errors'])->not->toBeEmpty();
});

test('validates email format', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country_id' => $country->id,
            'email' => 'invalid-email',
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['errors'])->not->toBeEmpty();
});

test('bulk insert creates entities with federation and user', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $federation = Federation::where('is_default_federation', 1)->first()
        ?? Federation::factory()->create(['is_default_federation' => 1]);

    // Create the ENTITY group for user creation
    $entityGroup = Group::firstOrCreate(
        ['code' => 'ENTITY'],
        ['name' => 'Entity', 'description' => 'Entity Group']
    );

    // Create entity-admin role
    Role::firstOrCreate(['name' => 'entity-admin']);

    $bulkInsertAction = app(BulkInsertEntitiesAction::class);

    $entities = [
        [
            'name' => 'Bulk Insert Test Entity',
            'country_id' => $country->id,
            'email' => 'bulk@example.test',
        ],
    ];

    $results = $bulkInsertAction->execute($entities);

    expect($results['success_count'])->toBe(1);
    expect($results['error_count'])->toBe(0);

    $entity = Entity::where('name', 'Bulk Insert Test Entity')->first();
    expect($entity)->not->toBeNull();

    // Verify specific federation assignment
    expect($entity->federations)->toHaveCount(1);
    expect($entity->federations->first()->id)->toBe($federation->id);

    // Verify state is correct (Active for admin users)
    $pivot = $entity->federations->first()->pivot;
    expect($pivot->status_class)->toBe(ActiveEntityFederationState::class);

    // Verify active flag directly in database (not in withPivot)
    $pivotRecord = \Illuminate\Support\Facades\DB::table('entity_federation')
        ->where('entity_id', $entity->id)
        ->where('federation_id', $federation->id)
        ->first();
    expect($pivotRecord->active)->toBe(1);

    // Verify user was created and associated with entity
    $entityUser = User::where('email', 'bulk@example.test')->first();
    expect($entityUser)->not->toBeNull();
    expect($entityUser->group_id)->toBe($entityGroup->id);
    expect($entityUser->entities()->where('entity.id', $entity->id)->exists())->toBeTrue();
    expect($entityUser->hasRole('entity-admin'))->toBeTrue();
});

test('validates federation id exists', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country_id' => $country->id,
            'federation_id' => 999999,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['errors'])->not->toBeEmpty();
});

test('resolves district name to id', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $district = District::first() ?? District::factory()->create(['name' => 'Lisboa', 'country_id' => $country->id]);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country_id' => $country->id,
            'district' => $district->name,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['valid'])->not->toBeEmpty();
    $validEntity = array_values($results['valid'])[0];
    expect($validEntity['district_id'])->toBe($district->id);
});
