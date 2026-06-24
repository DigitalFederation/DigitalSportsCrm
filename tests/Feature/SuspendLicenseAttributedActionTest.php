<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\DocumentType;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Actions\SuspendLicenseAttributedAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    DocumentType::factory()->create(['code' => 'ORD']);
    $this->committee = Committee::factory()->create();
    $this->professionalRole = ProfessionalRole::factory()->create();
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $user = User::factory()->create(['group_id' => $group->id]);
    $this->federation = Federation::factory()->create();
    $user->federations()->attach($this->federation->id);
    $this->individual = Individual::factory()->create();
    $this->federation->individuals()->attach($this->individual->id);
    $this->actingAs($user);
    $this->license = License::factory()->create([
        'professional_role_id' => $this->professionalRole->id,
    ]);
    // Set up initial state with factories for Entity and Individual
    Entity::factory()->create();
});

it('suspends a license correctly', function () {
    $license = LicenseAttributed::factory()->create(
        [
            'status_class' => ActiveLicenseAttributedState::class,
            'model_type' => 'individual',
            'model_id' => $this->individual->id,
            'license_id' => $this->license->id,
            'federation_id' => $this->federation->id,
        ]
    );
    $action = new SuspendLicenseAttributedAction;
    $action($license);

    $updatedLicense = LicenseAttributed::find($license->id);
    expect($updatedLicense->status_class)->toBe(SuspendedLicenseAttributedState::class);
});
