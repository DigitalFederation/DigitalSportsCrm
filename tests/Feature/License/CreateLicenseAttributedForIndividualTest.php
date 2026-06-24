<?php

use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->withoutMiddleware(\App\Http\Middleware\EnsureProfilePhotoExists::class);

    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=DocumentTypeSeeder');

    Federation::factory()->create(['is_default_federation' => true]);

    $this->group = Group::factory()->create(['code' => 'INDIVIDUAL', 'id' => 3]);
    $this->user = User::factory()->create(['group_id' => $this->group->id]);
    $this->federation = Federation::factory()->create(['is_local' => false]);
    $this->individual = Individual::factory()->create(['user_id' => $this->user->id]);
    $this->federation->individuals()->attach($this->individual);
});

it('creates a document for a license attributed to an individual', function () {
    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => null,
        'tax_percentage' => 23,
        'requester_model' => Individual::class,
    ]);

    $this->actingAs($this->user)->post(route('individual.license-attributed.store'), [
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'license_type_name' => 'individual',
        'individual' => [$this->individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test license with price',
    ]);

    $expectedTotal = $license->unit_value_individual * (1 + $license->tax_percentage / 100);
    $document = Document::latest()->first();

    expect($document->total_value)->toEqual($expectedTotal);
});

it('sets license to pending state for paid license', function () {
    $license = License::factory()->create([
        'unit_value' => 50,
        'unit_value_individual' => 100,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'requester_model' => 'All',
    ]);

    $this->actingAs($this->user)->post(route('individual.license-attributed.store'), [
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'license_type_name' => 'individual',
        'individual' => [$this->individual->id],
        'requester_model_type' => 'individual',
        'notes' => 'Test license with price',
    ]);

    $licenseAttributed = LicenseAttributed::latest()->first();

    expect($licenseAttributed->status_class)->toEqual(PendingLicenseAttributedState::class);
});
