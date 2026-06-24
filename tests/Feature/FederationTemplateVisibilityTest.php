<?php

use App\Enums\EventApplicationTypeEnum;
use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
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

    $this->mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    $this->mainFedUser = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->mainFedUser->federations()->attach($this->mainFederation->id);

    $this->territorialFederation = Federation::factory()->create([
        'is_default_federation' => false,
        'is_local' => true,
    ]);

    $this->territorialFedUser = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->territorialFedUser->federations()->attach($this->territorialFederation->id);

    $this->entityTemplate = ApplicationTemplate::factory()->openForSubmissions()->forEntities()->create();
    $this->federationTemplate = ApplicationTemplate::factory()->openForSubmissions()->forFederations()->create();
    $this->bothTemplate = ApplicationTemplate::factory()->openForSubmissions()->forBoth()->create();
});

it('territorial federation sees all templates including entity-only', function () {
    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertSee($this->entityTemplate->name)
        ->assertSee($this->federationTemplate->name)
        ->assertSee($this->bothTemplate->name);
});

it('main federation only sees federation and both templates', function () {
    actingAs($this->mainFedUser)
        ->get(route('federation.event-applications.available-templates'))
        ->assertSuccessful()
        ->assertDontSee($this->entityTemplate->name)
        ->assertSee($this->federationTemplate->name)
        ->assertSee($this->bothTemplate->name);
});

it('territorial federation sees template detail page for entity-only template', function () {
    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.create-from-template', $this->entityTemplate))
        ->assertSuccessful()
        ->assertSee($this->entityTemplate->name);
});

it('territorial federation store redirects to edit (wizard)', function () {
    $response = actingAs($this->territorialFedUser)
        ->post(route('federation.event-applications.store'), [
            'template_id' => $this->entityTemplate->id,
            'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
            'event_name' => $this->entityTemplate->name,
            'event_type' => $this->entityTemplate->event_type,
            'sport_id' => $this->entityTemplate->sport_id,
            'event_category' => $this->entityTemplate->event_category,
            'start_date' => $this->entityTemplate->event_start_date?->format('Y-m-d'),
            'end_date' => $this->entityTemplate->event_end_date?->format('Y-m-d'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $application = EventApplication::where('entity_id', $this->territorialFederation->id)->first();
    expect($application)->not->toBeNull();

    $response->assertRedirect(route('federation.event-applications.edit', $application));
});

it('territorial federation redirect to existing application on duplicate', function () {
    $existingApplication = EventApplication::factory()->create([
        'entity_id' => $this->territorialFederation->id,
        'entity_type' => 'federation',
        'template_id' => $this->entityTemplate->id,
    ]);

    actingAs($this->territorialFedUser)
        ->get(route('federation.event-applications.create-from-template', $this->entityTemplate))
        ->assertRedirect(route('federation.event-applications.show', $existingApplication))
        ->assertSessionHas('info');
});
