<?php

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Sport;
use Domain\EvtEvents\Actions\ApplyAthleteEligibilityFiltersAction;
use Domain\EvtEvents\Actions\CheckIndividualCompetitionEligibilityAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sport = Sport::factory()->create();
    $this->individual = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
});

/*
|--------------------------------------------------------------------------
| CheckIndividualCompetitionEligibilityAction - document expiry vs event end_date
|--------------------------------------------------------------------------
*/

it('marks athlete ineligible when document expires before event end_date', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    // Document expires 1 day before event ends
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addDays(14),
    ]);

    $action = new CheckIndividualCompetitionEligibilityAction;
    $reasons = $action->execute($event, $this->individual);

    expect($reasons)->toHaveCount(1);
});

it('marks athlete eligible when document expires on event end_date', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    // Document expires exactly on event end_date
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addDays(15),
    ]);

    $action = new CheckIndividualCompetitionEligibilityAction;
    $reasons = $action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

it('marks athlete eligible when document expires after event end_date', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    // Document expires well after event ends
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    $action = new CheckIndividualCompetitionEligibilityAction;
    $reasons = $action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

it('marks athlete eligible when document has null expiry_date', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    // Document with no expiry date (never expires)
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => null,
    ]);

    $action = new CheckIndividualCompetitionEligibilityAction;
    $reasons = $action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| ApplyAthleteEligibilityFiltersAction - document expiry vs event end_date
|--------------------------------------------------------------------------
*/

it('filters out athlete whose document expires before event end_date using query filter', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    // Document expires before event end_date
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addDays(14),
    ]);

    $action = new ApplyAthleteEligibilityFiltersAction;
    $query = Individual::query();
    $result = $action->execute($query, $event, null);

    expect($result->pluck('id'))->not->toContain($this->individual->id);
});

it('includes athlete whose document expires on event end_date using query filter', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    // Document expires exactly on event end_date
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addDays(15),
    ]);

    $action = new ApplyAthleteEligibilityFiltersAction;
    $query = Individual::query();
    $result = $action->execute($query, $event, null);

    expect($result->pluck('id'))->toContain($this->individual->id);
});

it('includes athlete whose document has null expiry using query filter', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => null,
    ]);

    $action = new ApplyAthleteEligibilityFiltersAction;
    $query = Individual::query();
    $result = $action->execute($query, $event, null);

    expect($result->pluck('id'))->toContain($this->individual->id);
});
