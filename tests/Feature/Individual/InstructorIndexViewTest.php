<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function createPendingInvitation(Entity $entity, User $user, string $committeeCode): EntityProfessionalRoleInvitation
{
    return EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $entity->id,
        'invited_user_id' => $user->id,
        'committee_code' => $committeeCode,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);
}

beforeEach(function () {
    $this->individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    $this->committeeDiving = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving']);
    $this->committeeScientific = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Scientific']);

    $this->roleDivingInstructor = ProfessionalRole::factory()
        ->for($this->committeeDiving)
        ->create(['role' => 'INSTRUCTOR', 'code' => 'DIVING_INSTRUCTOR', 'name' => 'Diving Instructor']);
    $this->roleScientificInstructor = ProfessionalRole::factory()
        ->for($this->committeeScientific)
        ->create(['role' => 'INSTRUCTOR', 'code' => 'SCIENTIFIC_INSTRUCTOR', 'name' => 'Scientific Instructor']);

    $this->user = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();
    $this->individual = $this->user->individual;
    $this->individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');

    $this->entity1 = Entity::factory()->create(['name' => 'Test Entity 1']);
    $this->entity2 = Entity::factory()->create(['name' => 'Test Entity 2']);

    actingAs($this->user);
});

// Diving Instructor Index Tests

test('individual can view diving instructor index page', function () {
    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewIs('web.individual.instructor.index')
        ->assertViewHas('committee', 'diving')
        ->assertViewHas('invites')
        ->assertViewHas('pendingGenericInvites');
});

test('individual diving index shows pending generic invites', function () {
    createPendingInvitation($this->entity1, $this->user, 'DIVING');

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 1)
        ->assertSee($this->entity1->name);
});

test('individual diving index shows accept and reject buttons for pending invites', function () {
    createPendingInvitation($this->entity1, $this->user, 'DIVING');

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertSee(__('Accept'))
        ->assertSee(__('Reject'));
});

test('individual diving index shows active associations', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->roleDivingInstructor->name,
    ]);

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewHas('invites', fn ($invites) => $invites->count() === 1)
        ->assertSee($this->entity1->name);
});

test('individual diving index shows pending associations with accept button', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingInstructor->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->roleDivingInstructor->name,
    ]);

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertSee(__('Accept'));
});

test('individual diving index does not show scientific invites', function () {
    createPendingInvitation($this->entity1, $this->user, 'SCIENTIFIC');

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 0);
});

// Scientific Instructor Index Tests

test('individual can view scientific instructor index page', function () {
    get(route('individual.instructor.index', ['committee' => 'scientific']))
        ->assertOk()
        ->assertViewIs('web.individual.instructor.index')
        ->assertViewHas('committee', 'scientific');
});

test('individual scientific index shows pending generic invites', function () {
    createPendingInvitation($this->entity1, $this->user, 'SCIENTIFIC');

    get(route('individual.instructor.index', ['committee' => 'scientific']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 1);
});

test('individual scientific index shows active associations', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->roleScientificInstructor->name,
    ]);

    get(route('individual.instructor.index', ['committee' => 'scientific']))
        ->assertOk()
        ->assertViewHas('invites', fn ($invites) => $invites->count() === 1);
});

test('individual scientific index does not show diving invites', function () {
    createPendingInvitation($this->entity1, $this->user, 'DIVING');

    get(route('individual.instructor.index', ['committee' => 'scientific']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 0);
});

// Multiple Invites Tests

test('individual index shows multiple pending invites from different entities', function () {
    createPendingInvitation($this->entity1, $this->user, 'DIVING');
    createPendingInvitation($this->entity2, $this->user, 'DIVING');

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 2)
        ->assertSee($this->entity1->name)
        ->assertSee($this->entity2->name);
});

test('individual index does not show already accepted invites', function () {
    EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity1->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => 'DIVING',
        'status' => 'accepted',
        'expires_at' => now()->addDays(7),
    ]);

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 0);
});

test('individual index does not show rejected invites', function () {
    EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity1->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => 'DIVING',
        'status' => 'rejected',
        'expires_at' => now()->addDays(7),
    ]);

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertOk()
        ->assertViewHas('pendingGenericInvites', fn ($invites) => $invites->count() === 0);
});

// Invalid Committee Tests

test('individual cannot view invalid committee instructor page', function () {
    get(route('individual.instructor.index', ['committee' => 'invalid']))
        ->assertNotFound();
});

// User Without Individual Tests

test('user without individual profile cannot access page', function () {
    $userWithoutIndividual = User::factory()
        ->for($this->individualGroup, 'group')
        ->create();
    actingAs($userWithoutIndividual);

    get(route('individual.instructor.index', ['committee' => 'diving']))
        ->assertForbidden();
});

// Code Filter Tests

test('individual can filter by professional code', function () {
    get(route('individual.instructor-code.index', ['committee' => 'diving', 'code' => 'DIVING_INSTRUCTOR']))
        ->assertOk()
        ->assertViewHas('code', 'DIVING_INSTRUCTOR')
        ->assertViewHas('professionalName');
});
