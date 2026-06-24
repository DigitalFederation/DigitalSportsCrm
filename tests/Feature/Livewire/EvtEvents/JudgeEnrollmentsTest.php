<?php

use App\Livewire\EvtEvents\JudgeEnrollments;
use App\Models\Sport;
use App\Models\User;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\Organizer;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\EvtEvents\Models\Sport as EvtSport;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $individualGroup = \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);
    $this->user = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->evtSport = EvtSport::factory()->create();
    $this->event = Event::factory()->create();
    $this->competition = Competition::factory()->create(['event_id' => $this->event->id]);
    Organizer::create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->federation->id,
        'organizable_type' => Federation::class,
    ]);

    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);
});

it('renders for chief judge', function () {
    $this->actingAs($this->user)
        ->get(route('individual.technical-delegate.referees', $this->event))
        ->assertSuccessful()
        ->assertSeeLivewire(JudgeEnrollments::class);
});

it('shows referee enrollments in the table', function () {
    $refereeIndividual = Individual::factory()->create([
        'name' => 'JudgeTestReferee',
        'surname' => 'Tester',
    ]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->assertSee('JudgeTestReferee');
});

it('can assign functions from event attributes to a referee', function () {
    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal', 'Juiz Cronometrista'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal', 'Juiz Cronometrista'],
            'notes' => null,
        ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Principal',
    ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Cronometrista',
    ]);
});

it('can assign multiple functions with notes', function () {
    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal', 'Juiz de Filmagem'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal', 'Juiz de Filmagem'],
            'notes' => 'Some notes',
        ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Principal',
        'notes' => 'Some notes',
    ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz de Filmagem',
        'notes' => 'Some notes',
    ]);
});

it('syncs functions replacing removed and adding new ones', function () {
    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal', 'Juiz Cronometrista', 'Juiz de Filmagem'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    // Pre-create existing assignments
    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => true,
        'function_text' => 'Juiz Principal',
        'assigned_by' => $this->individual->id,
    ]);
    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => true,
        'function_text' => 'Juiz Cronometrista',
        'assigned_by' => $this->individual->id,
    ]);

    // Sync: remove Cronometrista, keep Principal, add Filmagem
    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal', 'Juiz de Filmagem'],
            'notes' => null,
        ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Principal',
    ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz de Filmagem',
    ]);

    $this->assertDatabaseMissing('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Cronometrista',
    ]);
});

it('can save evaluation via manageEvaluation modal', function () {
    $refereeIndividual = Individual::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageEvaluation', $refereeEnrollment, data: [
            'evaluation' => 4,
            'evaluation_notes' => 'Good performance overall',
        ]);

    $this->assertDatabaseHas('evt_referees_enrollment', [
        'id' => $refereeEnrollment->id,
        'evaluation' => 4,
        'evaluation_notes' => 'Good performance overall',
    ]);
});

it('manageFunctions does not affect evaluation', function () {
    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'evaluation' => 5,
        'evaluation_notes' => 'Existing notes',
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal'],
            'notes' => 'Function notes',
        ]);

    $this->assertDatabaseHas('evt_referees_enrollment', [
        'id' => $refereeEnrollment->id,
        'evaluation' => 5,
        'evaluation_notes' => 'Existing notes',
    ]);
});

it('can toggle referee presence', function () {
    $refereeIndividual = Individual::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => true,
        'function_text' => 'Judge',
        'assigned_by' => $this->individual->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('togglePresence', $refereeEnrollment);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => false,
    ]);
});

it('activates pending enrollment when evaluation is saved', function () {
    $refereeIndividual = Individual::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => PendingRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageEvaluation', $refereeEnrollment, data: [
            'evaluation' => 3,
            'evaluation_notes' => 'Solid performance',
        ]);

    $this->assertDatabaseHas('evt_referees_enrollment', [
        'id' => $refereeEnrollment->id,
        'evaluation' => 3,
        'evaluation_notes' => 'Solid performance',
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);
});

it('does not change state when evaluating an already active enrollment', function () {
    $refereeIndividual = Individual::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageEvaluation', $refereeEnrollment, data: [
            'evaluation' => 5,
            'evaluation_notes' => 'Outstanding',
        ]);

    $this->assertDatabaseHas('evt_referees_enrollment', [
        'id' => $refereeEnrollment->id,
        'evaluation' => 5,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);
});

it('shows both active and pending referees', function () {
    $activeIndividual = Individual::factory()->create([
        'name' => 'ActiveRef',
        'surname' => 'One',
    ]);

    $pendingIndividual = Individual::factory()->create([
        'name' => 'PendingRef',
        'surname' => 'Two',
    ]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $activeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $pendingIndividual->id,
        'status_class' => PendingRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->assertSee('ActiveRef')
        ->assertSee('PendingRef');
});

it('shows empty state when no referees enrolled', function () {
    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->assertSee(__('events.no_technical_officials'));
});

it('computes referees count correctly', function () {
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    RefereeEnrollment::factory()->count(3)->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event]);

    expect($component->get('refereesCount'))->toBe(3);
});

it('shows competition_days field for individual sport events', function () {
    $appSport = Sport::factory()->create(['sport_type' => 'individual']);
    EvtSport::factory()->create(['id' => $appSport->id, 'sport_type' => 'individual']);
    $this->competition->update(['sport_id' => $appSport->id]);

    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal'],
            'notes' => null,
            'competition_days' => 3,
        ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Principal',
        'competition_days' => 3,
        'number_of_games' => null,
    ]);
});

it('shows both competition_days and number_of_games for team sport events', function () {
    $appSport = Sport::factory()->create(['sport_type' => 'team']);
    EvtSport::factory()->create(['id' => $appSport->id, 'sport_type' => 'team']);
    $this->competition->update(['sport_id' => $appSport->id]);

    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal'],
            'notes' => 'Team event notes',
            'competition_days' => 5,
            'number_of_games' => 12,
        ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Principal',
        'competition_days' => 5,
        'number_of_games' => 12,
        'notes' => 'Team event notes',
    ]);
});

it('persists competition_days and number_of_games when updating existing assignments', function () {
    $appSport = Sport::factory()->create(['sport_type' => 'team']);
    EvtSport::factory()->create(['id' => $appSport->id, 'sport_type' => 'team']);
    $this->competition->update(['sport_id' => $appSport->id]);

    $refereeIndividual = Individual::factory()->create();

    $attribute = Attribute::factory()->create([
        'name' => 'Funcao Oficial Tecnico',
        'attribute_type' => 'SELECT',
        'attribute_data' => ['Juiz Principal', 'Juiz Cronometrista'],
        'fillable_global' => false,
    ]);
    $this->event->refereeAttributes()->attach($attribute->id);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => true,
        'function_text' => 'Juiz Principal',
        'assigned_by' => $this->individual->id,
        'competition_days' => 1,
        'number_of_games' => 2,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->callTableAction('manageFunctions', $refereeEnrollment, data: [
            'functions' => ['Juiz Principal'],
            'notes' => null,
            'competition_days' => 7,
            'number_of_games' => 15,
        ]);

    $this->assertDatabaseHas('evt_referee_function_assignments', [
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'function_text' => 'Juiz Principal',
        'competition_days' => 7,
        'number_of_games' => 15,
    ]);
});

it('displays competition_days column value in the table', function () {
    $refereeIndividual = Individual::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $refereeIndividual->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
    ]);

    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'is_present' => true,
        'function_text' => 'Juiz Principal',
        'assigned_by' => $this->individual->id,
        'competition_days' => 4,
    ]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->assertCanSeeTableRecords([$refereeEnrollment])
        ->assertTableColumnStateSet('competition_days_display', 4, $refereeEnrollment);
});

it('hides number_of_games column for individual sport events', function () {
    $appSport = Sport::factory()->create(['sport_type' => 'individual']);
    EvtSport::factory()->create(['id' => $appSport->id, 'sport_type' => 'individual']);
    $this->competition->update(['sport_id' => $appSport->id]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->assertTableColumnVisible('competition_days_display')
        ->assertTableColumnHidden('number_of_games_display');
});

it('shows number_of_games column for team sport events', function () {
    $appSport = Sport::factory()->create(['sport_type' => 'team']);
    EvtSport::factory()->create(['id' => $appSport->id, 'sport_type' => 'team']);
    $this->competition->update(['sport_id' => $appSport->id]);

    Livewire::actingAs($this->user)
        ->test(JudgeEnrollments::class, ['event' => $this->event])
        ->assertTableColumnVisible('competition_days_display')
        ->assertTableColumnVisible('number_of_games_display');
});
