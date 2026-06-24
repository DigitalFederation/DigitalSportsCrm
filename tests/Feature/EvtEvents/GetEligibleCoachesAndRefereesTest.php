<?php

use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\EvtEvents\Actions\GetEligibleCoachesAction;
use Domain\EvtEvents\Actions\ValidateCoachEnrollmentCertificationsAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\Traits\FiltersLocalFederationAffiliation;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->localFederation = Federation::factory()->create([
        'name' => 'Territorial Association',
        'is_local' => true,
    ]);

    $this->otherLocalFederation = Federation::factory()->create([
        'name' => 'Other Territorial Association',
        'is_local' => true,
    ]);

    $this->sport = Sport::factory()->create();

    $this->entity = Entity::factory()->create();
    $this->entity->entityFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Create competition with local federation requirement
    $this->competition = Competition::factory()->create([
        'sport_id' => $this->sport->id,
        'requires_local_federation_affiliation' => true,
    ]);

    $this->event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Event with Local Fed Requirement',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);
    $this->competition->update(['event_id' => $this->event->id]);
    $this->event->refresh();
});

// Test the trait directly which is used by both coaches and referees actions
it('filters individuals by local federation affiliation using trait', function () {
    // Create a test class that uses the trait
    $filterClass = new class
    {
        use FiltersLocalFederationAffiliation;

        public function filterQuery(Builder $query, Competition $competition, ?Entity $entity): Builder
        {
            return $this->applyLocalFederationFilter($query, $competition, $entity);
        }
    };

    // Create individuals
    $individualInLocalFed = Individual::factory()->create();
    $individualNotInLocalFed = Individual::factory()->create();

    // Only individualInLocalFed is a member of the entity's local federation
    $individualInLocalFed->individualFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // individualNotInLocalFed is a member of a different local federation
    $individualNotInLocalFed->individualFederations()->create([
        'federation_id' => $this->otherLocalFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $query = Individual::query();
    $filteredQuery = $filterClass->filterQuery($query, $this->competition, $this->entity);
    $eligibleIndividuals = $filteredQuery->get();

    // Only individualInLocalFed should be eligible
    expect($eligibleIndividuals)->toHaveCount(1);
    expect($eligibleIndividuals->first()->id)->toBe($individualInLocalFed->id);
    expect($eligibleIndividuals->pluck('id')->toArray())->not->toContain($individualNotInLocalFed->id);
});

it('does not filter individuals when local federation requirement is disabled', function () {
    // Update competition to disable requirement
    $this->competition->update(['requires_local_federation_affiliation' => false]);

    $filterClass = new class
    {
        use FiltersLocalFederationAffiliation;

        public function filterQuery(Builder $query, Competition $competition, ?Entity $entity): Builder
        {
            return $this->applyLocalFederationFilter($query, $competition, $entity);
        }
    };

    // Create individuals
    $individual1 = Individual::factory()->create();
    $individual2 = Individual::factory()->create();

    // individual1 is member of the local federation, individual2 is not
    $individual1->individualFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $query = Individual::query();
    $filteredQuery = $filterClass->filterQuery($query, $this->competition, $this->entity);
    $eligibleIndividuals = $filteredQuery->get();

    // Both individuals should be eligible since requirement is disabled
    expect($eligibleIndividuals)->toHaveCount(2);
    expect($eligibleIndividuals->pluck('id')->toArray())->toContain($individual1->id);
    expect($eligibleIndividuals->pluck('id')->toArray())->toContain($individual2->id);
});

it('does not apply filter when entity is null', function () {
    $filterClass = new class
    {
        use FiltersLocalFederationAffiliation;

        public function filterQuery(Builder $query, Competition $competition, ?Entity $entity): Builder
        {
            return $this->applyLocalFederationFilter($query, $competition, $entity);
        }
    };

    // Create individuals
    Individual::factory()->count(2)->create();

    $query = Individual::query();
    // Pass null for entity - should not filter
    $filteredQuery = $filterClass->filterQuery($query, $this->competition, null);
    $eligibleIndividuals = $filteredQuery->get();

    // Both individuals should be eligible when entity is null
    expect($eligibleIndividuals)->toHaveCount(2);
});

it('filters referees with professional role by local federation affiliation', function () {
    $filterClass = new class
    {
        use FiltersLocalFederationAffiliation;

        public function filterQuery(Builder $query, Competition $competition, ?Entity $entity): Builder
        {
            return $this->applyLocalFederationFilter($query, $competition, $entity);
        }
    };

    // Create or get the referee professional role
    $refereeRole = ProfessionalRole::firstOrCreate(['role' => 'TECHNICAL_OFFICIAL']);

    // Create referees with professional role
    $refereeInLocalFed = Individual::factory()->create();
    $refereeNotInLocalFed = Individual::factory()->create();

    // Attach referee role to individuals
    $refereeInLocalFed->professionalRoles()->attach($refereeRole->id);
    $refereeNotInLocalFed->professionalRoles()->attach($refereeRole->id);

    // Only refereeInLocalFed is a member of the entity's local federation
    $refereeInLocalFed->individualFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // refereeNotInLocalFed is a member of a different local federation
    $refereeNotInLocalFed->individualFederations()->create([
        'federation_id' => $this->otherLocalFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Start with technical officials only
    $query = Individual::query()
        ->whereHas('professionalRoles', function ($q) {
            $q->where('role', 'TECHNICAL_OFFICIAL');
        });

    $filteredQuery = $filterClass->filterQuery($query, $this->competition, $this->entity);
    $eligibleReferees = $filteredQuery->get();

    // Only refereeInLocalFed should be eligible
    expect($eligibleReferees)->toHaveCount(1);
    expect($eligibleReferees->first()->id)->toBe($refereeInLocalFed->id);
});

it('returns no results when entity has no local federations', function () {
    // Create an entity without local federations
    $entityNoLocalFed = Entity::factory()->create();

    $filterClass = new class
    {
        use FiltersLocalFederationAffiliation;

        public function filterQuery(Builder $query, Competition $competition, ?Entity $entity): Builder
        {
            return $this->applyLocalFederationFilter($query, $competition, $entity);
        }
    };

    // Create an individual
    Individual::factory()->create();

    $query = Individual::query();
    $filteredQuery = $filterClass->filterQuery($query, $this->competition, $entityNoLocalFed);
    $eligibleIndividuals = $filteredQuery->get();

    // No individuals should be eligible when entity has no local federations
    expect($eligibleIndividuals)->toHaveCount(0);
});

it('filters coaches by entity sport registration when required', function () {
    // Create an entity
    $entity = Entity::factory()->create();
    $coachRole = ProfessionalRole::factory()->create(['role' => 'COACH']);

    // Create coaches - both are members of the entity
    $coachRegisteredForSport = Individual::factory()->create();
    $coachNotRegisteredForSport = Individual::factory()->create();

    // Both coaches are members of the entity
    $coachRegisteredForSport->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $coachNotRegisteredForSport->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Only coachRegisteredForSport is registered as a coach for this sport in the entity
    $coachRegisteredForSport->professionalRoleEntities()->create([
        'entity_id' => $entity->id,
        'sport_id' => $this->sport->id,
        'professional_role_id' => $coachRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Create competition with entity sport registration requirement
    $competition = Competition::factory()->create([
        'sport_id' => $this->sport->id,
        'requires_coach_entity_sport_registration' => true,
    ]);

    // Create event with the competition - Entity enrollment type
    $eventWithRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Event with Coach Entity Sport Requirement',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);
    $competition->update(['event_id' => $eventWithRequirement->id]);
    $eventWithRequirement->refresh();

    // Test using GetEligibleCoachesAction
    $certificationValidator = app(ValidateCoachEnrollmentCertificationsAction::class);
    $action = new GetEligibleCoachesAction($certificationValidator);
    $query = $action->execute($eventWithRequirement, $entity->id, 'entity');
    $eligibleCoaches = $query->get();

    // Only coachRegisteredForSport should be eligible
    expect($eligibleCoaches)->toHaveCount(1);
    expect($eligibleCoaches->first()->id)->toBe($coachRegisteredForSport->id);
    expect($eligibleCoaches->pluck('id')->toArray())->not->toContain($coachNotRegisteredForSport->id);
});

it('does not filter coaches by entity sport registration when requirement is disabled', function () {
    // Create an entity
    $entity = Entity::factory()->create();
    $coachRole = ProfessionalRole::factory()->create(['role' => 'COACH']);

    // Create coaches - both are members of the entity
    $coach1 = Individual::factory()->create();
    $coach2 = Individual::factory()->create();

    // Both coaches are members of the entity
    $coach1->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $coach2->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Only coach1 is registered for the sport in the entity (coach2 is not)
    $coach1->professionalRoleEntities()->create([
        'entity_id' => $entity->id,
        'sport_id' => $this->sport->id,
        'professional_role_id' => $coachRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Create competition WITHOUT entity sport registration requirement
    $competition = Competition::factory()->create([
        'sport_id' => $this->sport->id,
        'requires_coach_entity_sport_registration' => false,
    ]);

    // Create event with the competition - Entity enrollment type
    $eventWithoutRequirement = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Event without Coach Entity Sport Requirement',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);
    $competition->update(['event_id' => $eventWithoutRequirement->id]);
    $eventWithoutRequirement->refresh();

    // Test using GetEligibleCoachesAction
    $certificationValidator = app(ValidateCoachEnrollmentCertificationsAction::class);
    $action = new GetEligibleCoachesAction($certificationValidator);
    $query = $action->execute($eventWithoutRequirement, $entity->id, 'entity');
    $eligibleCoaches = $query->get();

    // Both coaches should be eligible since requirement is disabled
    expect($eligibleCoaches)->toHaveCount(2);
    expect($eligibleCoaches->pluck('id')->toArray())->toContain($coach1->id);
    expect($eligibleCoaches->pluck('id')->toArray())->toContain($coach2->id);
});
