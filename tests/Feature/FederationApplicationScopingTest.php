<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    // Main federation sees everything.
    $this->mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    $this->mainFedUser = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->mainFedUser->federations()->attach($this->mainFederation->id);

    // Territorial federation (e.g. ANM) - sees only its member entities
    $this->territorialFederation = Federation::factory()->create([
        'is_default_federation' => false,
        'is_local' => true,
    ]);

    $this->territorialFedUser = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->territorialFedUser->federations()->attach($this->territorialFederation->id);

    // Entity that is an active member of the territorial federation
    $this->memberEntity = Entity::factory()->create();
    $this->territorialFederation->entities()->attach($this->memberEntity->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Entity that is NOT a member of the territorial federation
    $this->nonMemberEntity = Entity::factory()->create();

    // Application from the member entity
    $this->memberApplication = EventApplication::factory()->submitted()->create([
        'entity_id' => $this->memberEntity->id,
        'entity_type' => 'entity',
    ]);

    // Application from the non-member entity
    $this->nonMemberApplication = EventApplication::factory()->submitted()->create([
        'entity_id' => $this->nonMemberEntity->id,
        'entity_type' => 'entity',
    ]);

    // Application from the territorial federation itself
    $this->ownFederationApplication = EventApplication::factory()->submitted()->create([
        'entity_id' => $this->territorialFederation->id,
        'entity_type' => 'federation',
    ]);
});

it('main federation sees all applications on index', function () {
    actingAs($this->mainFedUser)
        ->get(route('federation.event-applications.index'))
        ->assertSuccessful()
        ->assertSee($this->memberApplication->event_name)
        ->assertSee($this->nonMemberApplication->event_name)
        ->assertSee($this->ownFederationApplication->event_name);
});

it('territorial federation only sees member entity applications on index', function () {
    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.index'))
        ->assertSuccessful()
        ->assertSee($this->memberApplication->event_name)
        ->assertDontSee($this->nonMemberApplication->event_name)
        ->assertSee($this->ownFederationApplication->event_name);
});

it('main federation can view any application', function () {
    actingAs($this->mainFedUser)
        ->get(route('federation.event-applications.show', $this->nonMemberApplication))
        ->assertSuccessful();
});

it('territorial federation can view member entity application', function () {
    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.show', $this->memberApplication))
        ->assertSuccessful();
});

it('territorial federation can view its own application', function () {
    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.show', $this->ownFederationApplication))
        ->assertSuccessful();
});

it('territorial federation cannot view non-member entity application', function () {
    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.show', $this->nonMemberApplication))
        ->assertForbidden();
});
