<?php

use App\Models\Group;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
});

it('check if federation can activate a suspendend license', function () {
    $group = Group::factory()->create(['code' => 'FEDERATION']);

    $user = \App\Models\User::factory()->create([
        'group_id' => $group->id,
    ]);
    $federation = Federation::factory()->create();

    // Create active membership for federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);
    $individual = Individual::factory()->create();
    $federation->individuals()->attach($individual);

    $professionalRole = ProfessionalRole::factory()->create(['role' => 'DIVER']);
    $license = License::factory()->create([
        'professional_role_id' => $professionalRole->id,
    ]);
    $licenseAttributed = LicenseAttributed::factory()->create([
        'federation_id' => $federation->id,
        'license_id' => $license->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => \Domain\Licenses\States\SuspendedLicenseAttributedState::class,
        'total_value' => 0,
    ]);
    $this->actingAs($user);

    $response = $this->put(route('federation.license-attributed.activate', $licenseAttributed->id));
    $response->assertStatus(302);
    $response->assertSessionHas('success', __('License activated with success.'));
});

it('check if federation can suspend a license', function () {
    $group = Group::factory()->create(['code' => 'FEDERATION']);

    $user = \App\Models\User::factory()->create([
        'group_id' => $group->id,
    ]);
    $federation = Federation::factory()->create();

    // Create active membership for federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);
    $individual = Individual::factory()->create();
    $federation->individuals()->attach($individual);

    $professionalRole = ProfessionalRole::factory()->create(['role' => 'DIVER']);
    $license = License::factory()->create([
        'professional_role_id' => $professionalRole->id,
    ]);
    $licenseAttributed = LicenseAttributed::factory()->create([
        'federation_id' => $federation->id,
        'license_id' => $license->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
    $this->actingAs($user);

    $response = $this->post(route('federation.license-suspend.store', ['license_id' => $licenseAttributed->id]));
    $response->assertStatus(302);
    $response->assertSessionHas('success', 'License suspended with success.');
});

it('check if federation can change a license to provisional state', function () {
    $group = Group::factory()->create(['code' => 'FEDERATION']);

    $user = \App\Models\User::factory()->create([
        'group_id' => $group->id,
    ]);
    $federation = Federation::factory()->create();

    // Create active membership for federation
    Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);

    $federation->users()->attach($user);
    $individual = Individual::factory()->create();
    $federation->individuals()->attach($individual);

    $professionalRole = ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $license = License::factory()->create([
        'professional_role_id' => $professionalRole->id,
    ]);
    $licenseAttributed = LicenseAttributed::factory()->create([
        'federation_id' => $federation->id,
        'license_id' => $license->id,
        'model_id' => $individual->id,
        'model_type' => 'individual',
        'status_class' => PendingLicenseAttributedState::class,
    ]);
    $this->actingAs($user);

    $response = $this->put(route('federation.license-attributed.provisional', ['license_id' => $licenseAttributed->id]));

    $response->assertStatus(302);
    $response->assertSessionHas('success', 'License request approved with success');
});
