<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\ExpireLicenseAttributedAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create(['code' => 'FEDERATION']);
    $this->user = User::factory()->create(['group_id' => $this->group->id]);
    $this->federation = Federation::factory()->create();
    $this->federation->users()->attach($this->user);
});

it('expires an individual license correctly', function () {
    $individual = Individual::factory()->create();
    $this->federation->individuals()->attach($individual);

    $license = License::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $action = new ExpireLicenseAttributedAction;
    $action($licenseAttributed);

    $licenseAttributed->refresh();

    expect($licenseAttributed->status_class)->toBe(ExpiredLicenseAttributedState::class);
});

it('expires an entity license correctly', function () {
    // Create a user for the entity
    $user = User::factory()->create();

    // Create entity and attach user
    $entity = Entity::factory()->create();
    $entity->users()->attach($user);

    // Attach entity to federation
    $this->federation->entities()->attach($entity);

    // Create license and license attributed
    $license = License::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $action = new ExpireLicenseAttributedAction;
    $action($licenseAttributed);

    $licenseAttributed->refresh();

    expect($licenseAttributed->status_class)->toBe(ExpiredLicenseAttributedState::class);
});

it('logs activity when license is expired', function () {
    $individual = Individual::factory()->create();
    $this->federation->individuals()->attach($individual);

    $license = License::factory()->create();
    $licenseAttributed = LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $action = new ExpireLicenseAttributedAction;
    $action($licenseAttributed);

    $activity = Activity::where('subject_id', $licenseAttributed->id)->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('License expired.')
        ->and($activity->event)->toBe('expired')
        ->and($activity->subject_type)->toBe(LicenseAttributed::class);
});
