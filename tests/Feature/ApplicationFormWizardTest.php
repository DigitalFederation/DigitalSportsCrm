<?php

use App\Enums\UserGroupEnum;
use App\Livewire\EventApplications\Entity\ApplicationFormWizard;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $this->entityUser = User::factory()->create([
        'group_id' => UserGroupEnum::ENTITY->value,
    ]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);

    $this->sport = Sport::factory()->create();
    $this->district = District::factory()->create();

    $this->actingAs($this->entityUser);
});

it('renders the wizard component', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->assertSuccessful()
        ->assertSet('currentStep', 1)
        ->assertSet('totalSteps', 10);
});

it('can navigate to next step after valid step 1', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->set('event_name', 'Test Event')
        ->set('event_type', 'competition')
        ->set('start_date', now()->addWeek()->format('Y-m-d'))
        ->set('end_date', now()->addWeeks(2)->format('Y-m-d'))
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->assertHasNoErrors();
});

it('validates required fields on step 1', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->set('event_name', '')
        ->set('start_date', '')
        ->set('end_date', '')
        ->call('nextStep')
        ->assertSet('currentStep', 1)
        ->assertHasErrors(['event_name', 'start_date', 'end_date']);
});

it('validates end date must be after start date', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->set('event_name', 'Test Event')
        ->set('event_type', 'competition')
        ->set('start_date', '2026-06-15')
        ->set('end_date', '2026-06-10')
        ->call('nextStep')
        ->assertHasErrors(['end_date']);
});

it('can navigate back without validation', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->set('event_name', 'Test Event')
        ->set('event_type', 'competition')
        ->set('start_date', now()->addWeek()->format('Y-m-d'))
        ->set('end_date', now()->addWeeks(2)->format('Y-m-d'))
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

it('cannot go below step 1', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

it('can add and remove repeater rows', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->call('addRepeaterRow', 'previous_editions')
        ->assertCount('formData.previous_editions', 1)
        ->call('addRepeaterRow', 'previous_editions')
        ->assertCount('formData.previous_editions', 2)
        ->call('removeRepeaterRow', 'previous_editions', 0)
        ->assertCount('formData.previous_editions', 1);
});

it('can add revenue partner repeater rows', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->call('addRepeaterRow', 'revenue_partners')
        ->assertCount('formData.revenue.partners', 1)
        ->call('removeRepeaterRow', 'revenue_partners', 0)
        ->assertCount('formData.revenue.partners', 0);
});

it('auto-fills entity data on mount', function () {
    $component = Livewire::test(ApplicationFormWizard::class);

    expect($component->get('formData.entity_name'))->toBe($this->entity->name);
});

it('saves draft successfully', function () {
    Livewire::test(ApplicationFormWizard::class)
        ->set('event_name', 'Draft Event')
        ->set('event_type', 'competition')
        ->set('start_date', now()->addWeek()->format('Y-m-d'))
        ->set('end_date', now()->addWeeks(2)->format('Y-m-d'))
        ->set('formData.forecast_total_participants', 50)
        ->call('saveDraft');

    $application = EventApplication::where('event_name', 'Draft Event')->first();

    expect($application)->not->toBeNull()
        ->and($application->status_class)->toBe(DraftApplicationState::class)
        ->and($application->form_data)->toBeArray()
        ->and($application->form_data['forecast_total_participants'])->toBe(50);
});

it('loads existing application data in edit mode', function () {
    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $this->entity->id,
        'entity_type' => $this->entity->getMorphClass(),
        'event_name' => 'Existing Event',
        'form_data' => [
            'forecast_total_participants' => 100,
            'entity_name' => $this->entity->name,
            'national_federation_number' => '',
        ],
    ]);

    $component = Livewire::test(ApplicationFormWizard::class, [
        'application' => $application,
        'mode' => 'edit',
    ]);

    expect($component->get('event_name'))->toBe('Existing Event')
        ->and($component->get('formData.forecast_total_participants'))->toBe(100);
});

it('submits application and changes state', function () {
    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $this->entity->id,
        'entity_type' => $this->entity->getMorphClass(),
        'event_name' => 'Submit Me',
        'event_type' => 'competition',
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeeks(2),
    ]);

    Livewire::test(ApplicationFormWizard::class, [
        'application' => $application,
        'mode' => 'edit',
    ])
        ->call('submitApplication');

    $application->refresh();

    expect($application->status_class)->toBe(\Domain\EventApplications\States\SubmittedApplicationState::class)
        ->and($application->submitted_at)->not->toBeNull();
});

it('persists form_data across save and reload', function () {
    $formData = [
        'entity_name' => $this->entity->name,
        'national_federation_number' => '',
        'responsible_email' => '',
        'event_director_name' => 'John Director',
        'event_director_phone' => '912345678',
        'event_director_email' => 'director@example.test',
        'previous_editions' => [
            ['year' => '2025', 'location' => 'Lisbon', 'name' => 'Event 2025', 'athletes' => 30, 'clubs' => 5],
        ],
    ];

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $this->entity->id,
        'entity_type' => $this->entity->getMorphClass(),
        'event_name' => 'Persist Test',
        'event_type' => 'competition',
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeeks(2),
        'form_data' => $formData,
    ]);

    $component = Livewire::test(ApplicationFormWizard::class, [
        'application' => $application,
        'mode' => 'edit',
    ]);

    expect($component->get('formData.event_director_name'))->toBe('John Director')
        ->and($component->get('formData.previous_editions'))->toHaveCount(1)
        ->and($component->get('formData.previous_editions.0.location'))->toBe('Lisbon');
});

it('pre-fills data from template', function () {
    $template = ApplicationTemplate::factory()->open()->forEntities()->create([
        'name' => 'Template Event',
        'event_type' => 'organization',
        'sport_id' => $this->sport->id,
    ]);

    $component = Livewire::test(ApplicationFormWizard::class, [
        'template' => $template,
    ]);

    expect($component->get('event_name'))->toBe('Template Event')
        ->and($component->get('event_type'))->toBe('competition')
        ->and($component->get('sport_id'))->toBe($this->sport->id);
});

it('returns correct step titles', function () {
    $component = Livewire::test(ApplicationFormWizard::class);

    expect($component->instance()->getStepTitle())->toBe(__('event_applications.wizard.steps.event'));

    $component->set('event_name', 'Test')
        ->set('event_type', 'competition')
        ->set('start_date', now()->addWeek()->format('Y-m-d'))
        ->set('end_date', now()->addWeeks(2)->format('Y-m-d'))
        ->call('nextStep');

    expect($component->instance()->getStepTitle())->toBe(__('event_applications.wizard.steps.entity'));
});

it('returns 10 steps from getSteps', function () {
    $component = Livewire::test(ApplicationFormWizard::class);
    $steps = $component->instance()->getSteps();

    expect($steps)->toHaveCount(10);
});

it('returns correct title for step 10 summary', function () {
    $component = Livewire::test(ApplicationFormWizard::class);
    $component->set('currentStep', 10);

    expect($component->instance()->getStepTitle())->toBe(__('event_applications.wizard.steps.summary'));
});
