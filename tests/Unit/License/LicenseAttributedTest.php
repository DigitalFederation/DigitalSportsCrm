<?php

use App\Models\Group;
use Carbon\Carbon;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\DataTransferObject\LicenseAttributedData;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\PendingLicenseAttributedState;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=CommitteeSeeder');
});

it('store method creates a license attributed for an individual', function () {
    $federation = Federation::factory()->create();
    $individual = Individual::factory()->create();
    $license = License::factory()->create();

    $licenseAttributedData = new LicenseAttributedData(
        id: '',
        license_id: $license->id,
        federation_id: $federation->id,
        model_type: 'individual',
        model_id: $individual->id,
        license_name: $license->name,
        holder_name: $individual->name.' '.$individual->surname,
        federation_name: $federation->name,
        current_term_starts_at: Carbon::today(),
        current_term_ends_at: Carbon::now()->lastOfMonth()->endOfDay(),
        notes: 'Test notes',
        status_class: PendingLicenseAttributedState::class
    );

    $createLicenseAttributedAction = new CreateLicenseAttributedAction;
    $licenseAttributed = $createLicenseAttributedAction($licenseAttributedData);

    $this->assertDatabaseHas('license_attributed', [
        'id' => $licenseAttributed->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
    ]);
});

it('store method creates a license attributed for an entity', function () {
    $federation = Federation::factory()->create();
    $user = \App\Models\User::factory()->create(['group_id' => Group::select('id')->where('code', 'FEDERATION')->first()->id]);
    $user->federations()->attach($federation);

    $this->actingAs($user);

    $entity = Entity::factory()->create();
    $license = License::factory()->create();

    $licenseAttributedData = new LicenseAttributedData(
        id: '',
        license_id: $license->id,
        federation_id: $federation->id,
        model_type: 'entity',
        model_id: $entity->id,
        license_name: $license->name,
        holder_name: $entity->name,
        federation_name: $federation->name,
        current_term_starts_at: Carbon::today(),
        current_term_ends_at: Carbon::now()->lastOfMonth()->endOfDay(),
        notes: 'Test notes',
        status_class: PendingLicenseAttributedState::class,
    );

    $createLicenseAttributedAction = new CreateLicenseAttributedAction;
    $licenseAttributed = $createLicenseAttributedAction($licenseAttributedData);

    $this->assertDatabaseHas('license_attributed', [
        'id' => $licenseAttributed->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
    ]);
});
