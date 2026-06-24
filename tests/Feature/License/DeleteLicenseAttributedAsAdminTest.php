<?php

use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\delete;

beforeEach(function () {
    // Seed necessary data
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=CommitteeSeeder');
});

it('can delete a license attributed as an admin user', function () {
    // Create an admin user
    $adminUser = User::factory()->create([
        'group_id' => Group::where('code', 'ADMIN')->first()->id,
    ]);
    $adminUser->assignRole('admin');

    // Create necessary related data
    $individual = Individual::factory()->create();
    $federation = Federation::factory()->create();
    $license = License::factory()->create();

    // Create a LicenseAttributed
    $licenseAttributed = LicenseAttributed::create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_name' => $license->name,
        'holder_name' => $individual->name . ' ' . $individual->surname,
        'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
    ]);

    // Attempt to delete the LicenseAttributed as admin user
    actingAs($adminUser);
    $response = delete(route('admin.license-attributed.delete', $licenseAttributed->id));

    // Assert the response and database state
    $response->assertStatus(302); // Redirect after successful deletion
    $response->assertSessionHas('success');

    // Check if the record is soft deleted
    $deletedRecord = LicenseAttributed::withTrashed()->find($licenseAttributed->id);
    expect($deletedRecord)->not->toBeNull();
    expect($deletedRecord->trashed())->toBeTrue();

    // Check if activity was logged
    expect(DB::table('activity_log')->where('subject_id', $licenseAttributed->id)->exists())->toBeTrue();
});

it('cannot delete a license attributed as a non-admin user', function () {
    // Create a non-admin user (e.g., INDIVIDUAL)
    $nonAdminUser = User::factory()->create([
        'group_id' => Group::where('code', 'INDIVIDUAL')->first()->id,
    ]);

    // Create necessary related data
    $individual = Individual::factory()->create();
    $federation = Federation::factory()->create();
    $license = License::factory()->create();

    // Create a LicenseAttributed
    $licenseAttributed = LicenseAttributed::create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_name' => $license->name,
        'holder_name' => $individual->name . ' ' . $individual->surname,
        'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
    ]);

    // Attempt to delete the LicenseAttributed as non-admin user
    actingAs($nonAdminUser);
    $response = delete(route('admin.license-attributed.delete', $licenseAttributed->id));

    // Assert the response and database state
    $response->assertStatus(403); // Forbidden

    // Check if the record still exists
    expect(LicenseAttributed::find($licenseAttributed->id))->not->toBeNull();
});
