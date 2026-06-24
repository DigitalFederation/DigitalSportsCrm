<?php

use App\Livewire\Entity\EditMemberPhoto;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('secure-media');

    Role::findOrCreate('entity-admin', 'web');
    Role::findOrCreate('entity-diving-services', 'web');

    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $this->individualGroup = Group::firstOrCreate(
        ['code' => 'INDIVIDUAL'],
        ['name' => 'Individual']
    );

    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $this->entityUser->assignRole('entity-admin');

    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);

    $this->individual = Individual::factory()->create();
    $this->individual->individualEntities()->create([
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
});

// ============================================================================
// Authorization Tests
// ============================================================================

test('entity admin can mount the component for their member', function () {
    actingAs($this->entityUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->assertSuccessful()
        ->assertSet('individual.id', $this->individual->id)
        ->assertSet('showEditor', false);
});

test('entity diving services user can mount the component', function () {
    $divingUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $divingUser->assignRole('entity-diving-services');
    $this->entity->users()->attach($divingUser);

    actingAs($divingUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->assertSuccessful();
});

test('user without entity role cannot mount the component', function () {
    $regularUser = User::factory()->create(['group_id' => $this->individualGroup->id]);

    actingAs($regularUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->assertForbidden();
});

test('entity admin cannot edit member from another entity', function () {
    $otherEntity = Entity::factory()->create();
    $otherUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $otherUser->assignRole('entity-admin');
    $otherEntity->users()->attach($otherUser);

    actingAs($otherUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->assertForbidden();
});

// ============================================================================
// Toggle Editor Tests
// ============================================================================

test('can toggle editor visibility', function () {
    actingAs($this->entityUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->assertSet('showEditor', false)
        ->call('toggleEditor')
        ->assertSet('showEditor', true)
        ->call('toggleEditor')
        ->assertSet('showEditor', false);
});

test('toggling editor resets photo and validation', function () {
    actingAs($this->entityUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->assertSet('showEditor', true)
        ->assertSet('photo', null);
});

// ============================================================================
// Upload Tests
// ============================================================================

test('can upload a profile photo', function () {
    actingAs($this->entityUser);

    $photo = UploadedFile::fake()->image('profile.jpg', 300, 300);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->set('photo', $photo)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showEditor', false)
        ->assertSet('photo', null)
        ->assertDispatched('profile-photo-updated');

    expect($this->individual->fresh()->getFirstMediaUrl('profile'))->not->toBeEmpty();
});

test('uploading replaces existing photo', function () {
    actingAs($this->entityUser);

    $firstPhoto = UploadedFile::fake()->image('first.jpg', 300, 300);
    $secondPhoto = UploadedFile::fake()->image('second.jpg', 300, 300);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->set('photo', $firstPhoto)
        ->call('save');

    expect($this->individual->fresh()->getMedia('profile'))->toHaveCount(1);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->set('photo', $secondPhoto)
        ->call('save');

    expect($this->individual->fresh()->getMedia('profile'))->toHaveCount(1);
});

// ============================================================================
// Validation Tests
// ============================================================================

test('validates photo is required on save', function () {
    actingAs($this->entityUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->call('save')
        ->assertHasErrors(['photo' => 'required']);
});

test('validates photo mime type on save', function () {
    actingAs($this->entityUser);

    $file = UploadedFile::fake()->image('photo.gif');

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->set('photo', $file)
        ->call('save')
        ->assertHasErrors(['photo']);
});

test('validates photo max size', function () {
    actingAs($this->entityUser);

    $photo = UploadedFile::fake()->image('large.jpg')->size(3000);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->set('photo', $photo)
        ->assertHasErrors(['photo']);
});

// ============================================================================
// Remove Photo Tests
// ============================================================================

test('can remove a profile photo', function () {
    actingAs($this->entityUser);

    $photo = UploadedFile::fake()->image('profile.jpg', 300, 300);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('toggleEditor')
        ->set('photo', $photo)
        ->call('save');

    expect($this->individual->fresh()->getFirstMediaUrl('profile'))->not->toBeEmpty();

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->call('removePhoto')
        ->assertSet('showEditor', false)
        ->assertDispatched('profile-photo-updated');

    expect($this->individual->fresh()->getMedia('profile'))->toHaveCount(0);
});

// ============================================================================
// Event Listener Tests
// ============================================================================

test('responds to toggle-member-photo-editor event', function () {
    actingAs($this->entityUser);

    Livewire::test(EditMemberPhoto::class, ['individual' => $this->individual])
        ->assertSet('showEditor', false)
        ->dispatch('toggle-member-photo-editor')
        ->assertSet('showEditor', true);
});
