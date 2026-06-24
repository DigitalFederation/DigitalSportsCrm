<?php

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\EvtEvents\Actions\ApplyAthleteEligibilityFiltersAction;
use Domain\EvtEvents\Actions\GetEligibleAthletesAction;
use Domain\EvtEvents\Actions\GetEligibleEntityAthletesAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\SportAgeGroup;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $this->sport = Sport::factory()->create();
    $this->license = License::factory()->create(['sport_id' => $this->sport->id]);

    $this->sportAgeGroup = SportAgeGroup::factory()->create([
        'sport_id' => $this->sport->id,
        'birthday_start' => Carbon::now()->subYears(30)->startOfDay(),
        'birthday_end' => Carbon::now()->subYears(20)->endOfDay(),
    ]);

    $this->discipline = Discipline::factory()->create([
        'sport_id' => $this->sport->id,
        'gender' => 'male',
    ]);

    $this->discipline->sportAgeGroups()->attach($this->sportAgeGroup->id);

    $this->event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Event for All',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    $this->individualInAgeGroup = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $this->individualOutOfAgeGroup = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(35)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $this->individualInAgeGroup->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);
    $this->individualOutOfAgeGroup->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $this->license->id,
        'model_id' => $this->individualInAgeGroup->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
        'current_term_starts_at' => now(),
        'current_term_ends_at' => now()->addYear(),
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $this->license->id,
        'model_id' => $this->individualOutOfAgeGroup->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
        'current_term_starts_at' => now(),
        'current_term_ends_at' => now()->addYear(),
    ]);

    $this->applyFiltersAction = new ApplyAthleteEligibilityFiltersAction;
});

it('filters eligible athletes based on age group', function () {
    $action = new GetEligibleAthletesAction($this->applyFiltersAction);

    $eligibleAthletes = $action->execute($this->event->id, $this->federation->id, $this->discipline->id)->get();

    expect($eligibleAthletes)
        ->toHaveCount(1)
        ->first()->id->toBe($this->individualInAgeGroup->id);
});

it('filters athletes by local federation affiliation when required', function () {
    $localFederation = Federation::factory()->create(['is_local' => true]);
    $otherLocalFederation = Federation::factory()->create(['is_local' => true]);

    $entity = Entity::factory()->create();
    $entity->entityFederations()->create([
        'federation_id' => $localFederation->id,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $athleteInLocalFed = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athleteNotInLocalFed = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athleteInLocalFed->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athleteNotInLocalFed->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $athleteInLocalFed->individualFederations()->create([
        'federation_id' => $localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);
    $athleteNotInLocalFed->individualFederations()->create([
        'federation_id' => $otherLocalFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $eventWithRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithRequirement->id,
        'sport_id' => $this->sport->id,
        'requires_local_federation_affiliation' => true,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $eventWithRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)
        ->toHaveCount(1)
        ->first()->id->toBe($athleteInLocalFed->id);
});

it('filters athletes by entity sport registration when required', function () {
    $entity = Entity::factory()->create();

    $athleteRegistered = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athleteNotRegistered = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athleteRegistered->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athleteNotRegistered->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $athleteRegistered->entityAthletes()->create([
        'entity_id' => $entity->id,
        'sport_id' => $this->sport->id,
        'status_class' => \Domain\Entities\States\ActiveEntityProfessionalRoleState::class,
    ]);

    $competition = Competition::factory()->create([
        'sport_id' => $this->sport->id,
        'requires_athlete_entity_sport_registration' => true,
    ]);

    $eventWithRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);
    $competition->update(['event_id' => $eventWithRequirement->id]);
    $eventWithRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)
        ->toHaveCount(1)
        ->first()->id->toBe($athleteRegistered->id);
});

it('does not filter by entity sport registration when requirement is disabled', function () {
    $entity = Entity::factory()->create();

    $athlete1 = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athlete2 = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athlete1->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athlete2->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $athlete1->entityAthletes()->create([
        'entity_id' => $entity->id,
        'sport_id' => $this->sport->id,
        'status_class' => \Domain\Entities\States\ActiveEntityProfessionalRoleState::class,
    ]);

    $competition = Competition::factory()->create([
        'sport_id' => $this->sport->id,
        'requires_athlete_entity_sport_registration' => false,
    ]);

    $eventWithoutRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);
    $competition->update(['event_id' => $eventWithoutRequirement->id]);
    $eventWithoutRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithoutRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)->toHaveCount(2);
    expect($eligibleAthletes->pluck('id')->toArray())
        ->toContain($athlete1->id)
        ->toContain($athlete2->id);
});

it('does not filter by local federation when requirement is disabled', function () {
    $localFederation = Federation::factory()->create(['is_local' => true]);

    $entity = Entity::factory()->create();
    $entity->entityFederations()->create([
        'federation_id' => $localFederation->id,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $athlete1 = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athlete2 = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athlete1->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athlete2->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $athlete1->individualFederations()->create([
        'federation_id' => $localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $eventWithoutRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithoutRequirement->id,
        'sport_id' => $this->sport->id,
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $eventWithoutRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithoutRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)->toHaveCount(2);
    expect($eligibleAthletes->pluck('id')->toArray())
        ->toContain($athlete1->id)
        ->toContain($athlete2->id);
});

it('filters athletes by required medical exam document', function () {
    $entity = Entity::factory()->create();

    $athleteWithMedical = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athleteWithoutMedical = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athleteWithMedical->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athleteWithoutMedical->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithMedical->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    $eventWithDocRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithDocRequirement->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $eventWithDocRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithDocRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)
        ->toHaveCount(1)
        ->first()->id->toBe($athleteWithMedical->id);
});

it('excludes athletes whose medical exam has expired', function () {
    $entity = Entity::factory()->create();

    $athleteWithValidMedical = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athleteWithExpiredMedical = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athleteWithValidMedical->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athleteWithExpiredMedical->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithValidMedical->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithExpiredMedical->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->subDay(),
    ]);

    $eventWithDocRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithDocRequirement->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $eventWithDocRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithDocRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)
        ->toHaveCount(1)
        ->first()->id->toBe($athleteWithValidMedical->id);
});

it('does not filter by documents when no documents are required', function () {
    $entity = Entity::factory()->create();

    $athlete1 = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athlete2 = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athlete1->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athlete2->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $eventWithoutDocRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithoutDocRequirement->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => null,
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $eventWithoutDocRequirement->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithoutDocRequirement->id, $entity->id)->get();

    expect($eligibleAthletes)->toHaveCount(2);
    expect($eligibleAthletes->pluck('id')->toArray())
        ->toContain($athlete1->id)
        ->toContain($athlete2->id);
});

it('requires all documents when multiple are required', function () {
    $entity = Entity::factory()->create();

    $athleteWithBothDocs = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);
    $athleteWithOnlyMedical = Individual::factory()->create([
        'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d'),
        'gender' => 'male',
    ]);

    $athleteWithBothDocs->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athleteWithOnlyMedical->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithBothDocs->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);
    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithBothDocs->id,
        'type' => OfficialDocumentTypeEnum::InsuranceAthlete,
        'expiry_date' => now()->addYear(),
    ]);

    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithOnlyMedical->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    $eventWithMultipleDocs = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithMultipleDocs->id,
        'sport_id' => $this->sport->id,
        'required_athlete_documents' => [
            OfficialDocumentTypeEnum::MedicalStatement->value,
            OfficialDocumentTypeEnum::InsuranceAthlete->value,
        ],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);
    $eventWithMultipleDocs->refresh();

    $action = new GetEligibleEntityAthletesAction(new ApplyAthleteEligibilityFiltersAction);
    $eligibleAthletes = $action->execute($eventWithMultipleDocs->id, $entity->id)->get();

    expect($eligibleAthletes)
        ->toHaveCount(1)
        ->first()->id->toBe($athleteWithBothDocs->id);
});
