<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    artisan('db:seed --class=RoleAndPermissionSeeder');
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create user and individual
    $this->individualUser = User::factory()->create(['group_id' => $group->id]);
    $this->individual = Individual::factory()->create(['user_id' => $this->individualUser->id]);

    // Create federation and active relationship
    $this->federation = Federation::factory()->create();
    $this->individualFederation = IndividualFederation::create([
        'individual_id' => $this->individual->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create entity associated with the federation
    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->federation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $this->individualUser->assignRole('individual-approved');
    $this->actingAs($this->individualUser);
});

test('individual can view and manage pending entity invitations', function () {
    // Create a pending invitation from entity to individual
    $pendingInvitation = IndividualEntity::create([
        'individual_id' => $this->individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);

    // Access the individual's entity index page
    $response = $this->get(route('individual.entity.index'));

    // Check the response status
    $response->assertStatus(200);

    // Check if entity name is visible
    $response->assertSee($this->entity->name);

    // Check if status is displayed (view translates status via match/translation)
    $response->assertSee(__('entities.status_pending'));

    // Check if Accept form/button is present
    $response->assertSee(route('individual.entity.approve'));
    $response->assertSee(__('entities.accept'));

    // Check if Decline form/button is present
    $response->assertSee(route('individual.entity.delete', $this->entity));
    $response->assertSee(__('entities.cancel'));
});
