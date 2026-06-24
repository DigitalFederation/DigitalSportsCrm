<?php

use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed necessary data here
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=UserGroupSeeder');

    $this->country = Country::factory()->create();
});

it('prevents federation-admin group_id change when creating an individual with the same email', function () {
    // Prepare federation and user data
    $federationAdminGroup = Group::where('code', 'FEDERATION')->first();
    $federation = Federation::factory()->create();
    $federationAdminEmail = 'federation-admin@example.com';

    // Create a federation-admin user
    $federationAdmin = User::factory()->create([
        'email' => $federationAdminEmail,
        'group_id' => $federationAdminGroup->id,
    ]);
    $federation->users()->attach($federationAdmin);
    $this->actingAs($federationAdmin);

    // Ensure the federation-admin user is correctly set up
    $federationAdmin->refresh();
    expect($federationAdmin->group->code)->toEqual('FEDERATION');

    // Attempt to create an Individual with the same email
    // You need to adapt this to actually create an individual using your application's logic
    // This might involve calling a specific route or directly using an action class
    $individualData = [
        'name' => 'John Doe',
        'surname' => 'Doe',
        'native_name' => null,
        'country_id' => $this->country->id,
        'birthdate' => '1990-01-01',
        'gender' => 'male',
        'address' => 'Example Street 1',
        'location' => 'Example City',
        'postal_code' => '0000-000',
        'doc_ref_type' => 'ID',
        'doc_ref' => 'EXAMPLE-DOC-001',
        'doc_ref_validation_date' => now()->toDateString(),
        'email' => $federationAdminEmail,
        'federation_id' => [$federation->id],
        'entity_id' => null,
        'logo' => null,
        'professional_role_ids' => null,
        'national_federation_number' => 'NF123456',
    ];

    // Adapt the below line to your application's logic
    $this->post(route('public.individual.store'), $individualData);

    // Assertions
    $federationAdmin->refresh();
    expect($federationAdmin->group->code)->toBe('FEDERATION');

    $response = $this->post(route('federation.individual.store'), $individualData);
    $federationAdmin->refresh();
    expect($federationAdmin->group->code)->toEqual('FEDERATION');

    // If your logic prevents creation of individuals with duplicate emails, assert the individual was not created
    // Alternatively, if your application logic allows for it, adjust this assertion accordingly
    $this->assertDatabaseMissing('individual', [
        'email' => $federationAdminEmail,
    ]);
});
