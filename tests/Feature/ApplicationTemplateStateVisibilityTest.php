<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Models\ApplicationTemplate;
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

    // Entity setup
    $this->entityUser = User::factory()->create([
        'group_id' => UserGroupEnum::ENTITY->value,
    ]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);

    // Federation setup
    $this->federationUser = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->federation = Federation::factory()->create();
    $this->federation->users()->attach($this->federationUser);
});

// --- Entity: Available Templates Visibility ---

it('shows open templates to entities in available-templates', function () {
    $template = ApplicationTemplate::factory()->open()->forEntities()->create([
        'name' => 'Open Entity Template',
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertSee('Open Entity Template');
});

it('shows closed templates to entities in available-templates', function () {
    $template = ApplicationTemplate::factory()->closed()->forEntities()->create([
        'name' => 'Closed Entity Template',
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertSee('Closed Entity Template');
});

it('does not show draft templates to entities in available-templates', function () {
    $template = ApplicationTemplate::factory()->forEntities()->create([
        'name' => 'Draft Entity Template',
        'state' => 'draft',
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertDontSee('Draft Entity Template');
});

it('does not show archived templates to entities in available-templates', function () {
    $template = ApplicationTemplate::factory()->archived()->forEntities()->create([
        'name' => 'Archived Entity Template',
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertDontSee('Archived Entity Template');
});

// --- Federation: Available Templates Visibility ---

it('shows open templates to federations in available-templates', function () {
    $template = ApplicationTemplate::factory()->open()->forFederations()->create([
        'name' => 'Open Federation Template',
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertSee('Open Federation Template');
});

it('shows closed templates to federations in available-templates', function () {
    $template = ApplicationTemplate::factory()->closed()->forFederations()->create([
        'name' => 'Closed Federation Template',
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertSee('Closed Federation Template');
});

it('does not show draft templates to federations in available-templates', function () {
    $template = ApplicationTemplate::factory()->forFederations()->create([
        'name' => 'Draft Federation Template',
        'state' => 'draft',
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertDontSee('Draft Federation Template');
});

it('does not show archived templates to federations in available-templates', function () {
    $template = ApplicationTemplate::factory()->archived()->forFederations()->create([
        'name' => 'Archived Federation Template',
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertDontSee('Archived Federation Template');
});

// --- Entity: createFromTemplate blocks non-open templates ---

it('blocks entity from creating application when template is closed', function () {
    $template = ApplicationTemplate::factory()->closed()->forEntities()->create();

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.create-from-template', $template))
        ->assertRedirect(route('entity.event-applications.available-templates'))
        ->assertSessionHas('error', __('event_applications.template_not_open'));
});

it('allows entity to create application when template is open', function () {
    $template = ApplicationTemplate::factory()->open()->forEntities()->create();

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.create-from-template', $template))
        ->assertSuccessful();
});

// --- Federation: createFromTemplate blocks non-open templates ---

it('blocks federation from creating application when template is closed', function () {
    $template = ApplicationTemplate::factory()->closed()->forFederations()->create();

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.create-from-template', $template))
        ->assertRedirect(route('federation.event-applications.available-templates'))
        ->assertSessionHas('error', __('event_applications.template_not_open'));
});

it('blocks federation from creating application when template is draft', function () {
    $template = ApplicationTemplate::factory()->forFederations()->create([
        'state' => 'draft',
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.create-from-template', $template))
        ->assertRedirect(route('federation.event-applications.available-templates'))
        ->assertSessionHas('error', __('event_applications.template_not_open'));
});

it('blocks federation from creating application when template is archived', function () {
    $template = ApplicationTemplate::factory()->archived()->forFederations()->create();

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.create-from-template', $template))
        ->assertRedirect(route('federation.event-applications.available-templates'))
        ->assertSessionHas('error', __('event_applications.template_not_open'));
});

// --- Entity: Edit blocked when template is archived ---

it('blocks entity from editing application when template is archived', function () {
    $template = ApplicationTemplate::factory()->archived()->forEntities()->create();

    $application = EventApplication::factory()->draft()->create([
        'template_id' => $template->id,
        'entity_id' => $this->entity->id,
        'entity_type' => $this->entity->getMorphClass(),
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.edit', $application))
        ->assertRedirect()
        ->assertSessionHas('error', __('event_applications.template_archived_cannot_edit'));
});

// --- Federation: Edit blocked when template is archived ---

it('blocks federation from editing application when template is archived', function () {
    $template = ApplicationTemplate::factory()->archived()->forFederations()->create();

    $application = EventApplication::factory()->draft()->create([
        'template_id' => $template->id,
        'entity_id' => $this->federation->id,
        'entity_type' => $this->federation->getMorphClass(),
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.edit', $application))
        ->assertRedirect()
        ->assertSessionHas('error', __('event_applications.template_archived_cannot_edit'));
});

it('allows entity to edit application when template is open', function () {
    $template = ApplicationTemplate::factory()->open()->forEntities()->create();

    $application = EventApplication::factory()->draft()->create([
        'template_id' => $template->id,
        'entity_id' => $this->entity->id,
        'entity_type' => $this->entity->getMorphClass(),
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.edit', $application))
        ->assertSuccessful();
});

it('allows federation to edit application when template is open', function () {
    $template = ApplicationTemplate::factory()->open()->forFederations()->create();

    $application = EventApplication::factory()->draft()->create([
        'template_id' => $template->id,
        'entity_id' => $this->federation->id,
        'entity_type' => $this->federation->getMorphClass(),
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.edit', $application))
        ->assertSuccessful();
});

// --- ReturnedForCorrection: Entity and Federation can edit ---

it('allows entity to edit returned-for-correction application', function () {
    $template = ApplicationTemplate::factory()->open()->forEntities()->create();

    $application = EventApplication::factory()->returnedForCorrection()->create([
        'template_id' => $template->id,
        'entity_id' => $this->entity->id,
        'entity_type' => $this->entity->getMorphClass(),
    ]);

    actingAs($this->entityUser)
        ->get(route('entity.event-applications.edit', $application))
        ->assertSuccessful();
});

it('allows federation to edit returned-for-correction application', function () {
    $template = ApplicationTemplate::factory()->open()->forFederations()->create();

    $application = EventApplication::factory()->returnedForCorrection()->create([
        'template_id' => $template->id,
        'entity_id' => $this->federation->id,
        'entity_type' => $this->federation->getMorphClass(),
    ]);

    actingAs($this->federationUser)
        ->get(route('federation.event-applications.edit', $application))
        ->assertSuccessful();
});
