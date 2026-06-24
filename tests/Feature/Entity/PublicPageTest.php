<?php

use App\Livewire\Entity\PublicPage\ManagePublicPage;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Actions\UpdateEntityPublicSettingsAction;
use Domain\Entities\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('entity-admin', 'web');

    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);

    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $this->entityUser->assignRole('entity-admin');

    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
});

// ============================================================================
// Route Access Tests
// ============================================================================

test('entity admin can access public page management', function () {
    actingAs($this->entityUser);

    $response = get(route('entity.public-page.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.entity.public_page.index');
});

test('user without entity cannot access public page management', function () {
    $userWithoutEntity = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $userWithoutEntity->assignRole('entity-admin');

    actingAs($userWithoutEntity);

    $response = get(route('entity.public-page.index'));

    $response->assertForbidden();
});

test('unauthenticated user cannot access public page management', function () {
    $response = get(route('entity.public-page.index'));

    $response->assertRedirect(route('login'));
});

// ============================================================================
// Livewire Component Tests
// ============================================================================

test('livewire component mounts correctly', function () {
    actingAs($this->entityUser);

    Livewire::test(ManagePublicPage::class)
        ->assertSet('entity.id', $this->entity->id)
        ->assertSet('activeTab', 'general');
});

test('can save general settings', function () {
    actingAs($this->entityUser);

    $description = '<p>Test public description</p>';

    Livewire::test(ManagePublicPage::class)
        ->set('publicDescription', $description)
        ->call('saveGeneralSettings')
        ->assertHasNoErrors();

    $this->entity->refresh();
    expect($this->entity->public_description)->toBe($description);
});

// ============================================================================
// Action Tests
// ============================================================================

test('UpdateEntityPublicSettingsAction updates public description', function () {
    $action = new UpdateEntityPublicSettingsAction;

    $description = '<p>New description</p>';
    $action($this->entity, ['public_description' => $description]);

    $this->entity->refresh();
    expect($this->entity->public_description)->toBe($description);
});
