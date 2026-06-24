<?php

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Sport;
use Domain\EvtEvents\Actions\CheckIndividualCompetitionEligibilityAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
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
    $this->action = new CheckIndividualCompetitionEligibilityAction;
});

it('returns empty reasons when event has no competition', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'event_category' => 'organizational',
    ]);

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

it('returns empty reasons when competition has no requirements', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [],
        'required_athlete_licenses' => [],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

it('returns reason when individual is missing a required document', function () {
    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toHaveCount(1)
        ->and($reasons[0])->toContain(__('official_documents.types.MedicalStatement'));
});

it('returns empty reasons when individual has the required document', function () {
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

it('returns reason when required document is expired', function () {
    OfficialDocument::factory()->active()->create([
        'individual_id' => $this->individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->subDay(),
    ]);

    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toHaveCount(1);
});

it('returns reason when individual is missing a required license', function () {
    $license = License::factory()->create(['sport_id' => $this->sport->id]);

    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_licenses' => [$license->id],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toHaveCount(1)
        ->and($reasons[0])->toBe(__('events.competition_missing_required_license'));
});

it('returns empty reasons when individual holds required license', function () {
    $license = License::factory()->create(['sport_id' => $this->sport->id]);

    LicenseAttributed::factory()->create([
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
        'current_term_ends_at' => now()->addYear(),
    ]);

    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_licenses' => [$license->id],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toBeEmpty();
});

it('returns multiple reasons when multiple requirements are unmet', function () {
    $license = License::factory()->create(['sport_id' => $this->sport->id]);

    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $event->id,
        'sport_id' => $this->sport->id,
        'required_athlete_licenses' => [$license->id],
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $event->refresh();

    $reasons = $this->action->execute($event, $this->individual);

    expect($reasons)->toHaveCount(2);
});
