<?php

use App\Livewire\DivingLicenseRequestWizard;
use App\Models\Committee;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('wizard loads without errors', function () {
    // Create user with entity group
    $entityGroup = \App\Models\Group::create(['code' => 'ENTITY', 'name' => 'Entity']);
    $entity = Entity::factory()->create();
    $user = User::factory()->create(['group_id' => $entityGroup->id]);
    $user->entities()->attach($entity->id);

    // Create basic diving license - wizard uses DIVINGSERVICES (non-international)
    $divingCommittee = Committee::firstOrCreate(
        ['code' => 'DIVINGSERVICES'],
        ['name' => 'Diving Services Committee', 'is_international' => false]
    );
    $entityLicenseType = LicenseType::firstOrCreate(['name' => 'entity']);

    $license = License::create([
        'name' => 'Test License',
        'committee_id' => $divingCommittee->id,
        'type_id' => $entityLicenseType->id,
        'unit_value' => 100,
        'unit_value_entity' => 100,
        'active' => true,
        'interval' => 1,
        'interval_unit' => 'years',
    ]);

    Livewire::actingAs($user)
        ->test(DivingLicenseRequestWizard::class)
        ->assertSet('currentStep', 1)
        ->assertSee('Test License');
});
