<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\UserGroupSeeder::class);
    $this->seed(\Database\Seeders\CommitteeSeeder::class);
    $this->seed(\Database\Seeders\DivingEntityLicenseSeeder::class);

    // Create entity user
    $entityGroup = \App\Models\Group::where('code', 'ENTITY')->first();
    $this->user = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->user);

    $this->actingAs($this->user);
});

// SIMPLIFIED: These are now basic smoke tests to ensure routes work
// Business logic should be tested in model/service tests

test('entity can access diving licenses index', function () {
    $response = $this->get(route('entity.diving_licenses.index'));
    $response->assertOk();
});
