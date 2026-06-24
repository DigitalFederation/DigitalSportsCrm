<?php

use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('can store a federation and create associated user', function () {
    // Set up
    $FedGroup = Group::factory()->create(['code' => 'FEDERATION']); // Necessary for the controller create user action
    $group = Group::factory()->create(['code' => 'ADMIN']);
    $admin = User::factory()->for($group, 'group')->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $country = Country::factory()->create();  // Assuming you have a CountryFactory

    // Test data
    $federationData = [
        'country_id' => $country->id, // Assuming $country is an instance of the Country model
        'parent_id' => null, // or some integer if applicable
        'name' => 'Test Federation',
        'is_local' => false,
        'legal_name' => 'Test Legal Federation',
        'address' => '123 Federation Street',
        'location' => 'Test City',
        'latitude' => 12.123456, // lat for DTO
        'longitude' => 65.654321, // lng for DTO
        'website' => 'https://federation.example.test',
        'email' => 'federation@example.test',
        'user_email' => 'federation-user@example.test',
        'confirm_user_email' => 'federation-user@example.test',
        'phone' => '+1234567890',
        'zip_code' => '12345',
        'vat_number' => 'VAT12345',
        'board_members' => [],
        'member_code' => 'CODE123',
        'logo' => null, // Assuming no file for the logo
        'attachments' => null, // Assuming no files for attachments
        'is_default_federation' => 0,
    ];

    // Act
    $response = $this->post(route('admin.federation.store'), $federationData);

    // Assert
    $response->assertRedirect(route('admin.federation.index'));
    $response->assertSessionHas('success', 'Federation created with success.');

    $federation = Federation::where('email', 'federation@example.test')->first();
    $this->assertNotNull($federation);

    // Check additional fields
    $this->assertEquals('Test Federation', $federation->name);
    $this->assertNotNull($federation->member_code);

    $associatedUsers = $federation->users;
    $associatedUser = $associatedUsers->first();

    $this->assertNotNull($associatedUser);  // Ensure there is an associated user
    $this->assertEquals('FEDERATION', $associatedUser->group->code);
});
