<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtAttributeTypesEnum;
use App\Enums\OfficialDocumentTypeEnum;
use App\Livewire\EvtEvents\ManageEnrollment;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create base test data
    $this->sport = Sport::factory()->create();

    $this->event = Event::factory()->create([
        'event_category' => 'competition',
        'allow_individual_enrollment' => true,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addDay(),
    ]);
    $this->pricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'per_person',
        'price' => 0,
        'is_active' => true,
    ]);
    $this->competition = Competition::factory()->create([
        'event_id' => $this->event->id,
        'max_disciplines_per_athlete' => 1,
    ]);

    $this->federation = Federation::factory()->create();
    $template = DisciplineTemplate::factory()->create();
    // Create a discipline with a limit of 4 athletes
    $this->discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'athlete_limit' => 1,
        'sport_id' => $this->sport->id,
        'gender' => 'male',
    ]);
    // Attach the discipline to the discipline template pivot
    $template->disciplines()->attach($this->discipline->id);
    // Associate discipline with competition
    $this->competition->update(['discipline_template_id' => $template->id]);

    // Create the out-of-race attribute
    $this->outOfRaceAttribute = Attribute::factory()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE->value,
        'name' => 'Out of Race',
        'attribute_data' => [
            'options' => ['yes' => 'Yes', 'no' => 'No'],
            'default_value' => 'no',
        ],
        'fillable_type' => 'MANUAL',
        'fillable_global' => 1,
    ]);

    // Attach attribute to discipline
    // $this->discipline->attributes()->attach($this->outOfRaceAttribute);

    // Create test athletes
    $this->individuals = Individual::factory()->count(6)->create([
        'gender' => 'male',
    ]);

    foreach ($this->individuals as $athlete) {
        AthleteEnrollment::factory()->create([
            'event_id' => $this->event->id,
            'individual_id' => $athlete->id,
            'federation_id' => $this->federation->id,
            'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
            'discipline_id' => null,
        ]);
    }

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

test('prevents enrollment when exceeding athlete limit without out-of-race attribute', function () {

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'athlete',
    ])
        ->set('selectedDiscipline', $this->discipline->id)
        ->set('enrollmentType', 'ATHLETE')
        ->set('currentStep', 2)
        ->set('selectedIndividuals', [
            [
                'id' => $this->individuals[0]->id,
                'name' => $this->individuals[0]->name,
                'member_code' => $this->individuals[0]->member_code,
                'gender' => $this->individuals[0]->gender,
                'birthdate' => $this->individuals[0]->birthdate,
            ],
            [
                'id' => $this->individuals[1]->id,
                'name' => $this->individuals[1]->name,
                'member_code' => $this->individuals[1]->member_code,
                'gender' => $this->individuals[1]->gender,
                'birthdate' => $this->individuals[1]->birthdate,
            ],
        ]);

    // Verify initial state
    expect(AthleteEnrollment::count())->toBe(6)
        ->and($this->discipline->athlete_limit)->toBe(1);

    $component->call('submitEnrollment');

    // Assert validation error
    // $component->assertSee('The number of in-race athletes (2) exceeds the limit of 1 for this discipline.');
    // Verify no new enrollments were created
    expect(AthleteEnrollment::count())->toBe(6)
        ->and(AthleteEnrollment::where('discipline_id', $this->discipline->id)->count())->toBe(0);
});

test('allows enrollment beyond athlete limit with out-of-race attribute', function () {
    $this->discipline->attributes()->attach($this->outOfRaceAttribute);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'athlete',
    ])
        ->set('selectedDiscipline', $this->discipline->id)
        ->set('enrollmentType', 'ATHLETE')
        ->set('currentStep', 2)
        ->set('selectedIndividuals', [
            [
                'id' => $this->individuals[0]->id,
                'name' => $this->individuals[0]->name,
                'member_code' => $this->individuals[0]->member_code,
                'gender' => $this->individuals[0]->gender,
                'birthdate' => $this->individuals[0]->birthdate,
                'attributes' => [
                    $this->outOfRaceAttribute->id => [
                        'value' => 'yes', // Properly structured attribute value
                        'attribute_id' => $this->outOfRaceAttribute->id,
                    ],
                ],
            ],
            [
                'id' => $this->individuals[1]->id,
                'name' => $this->individuals[1]->name,
                'member_code' => $this->individuals[1]->member_code,
                'gender' => $this->individuals[1]->gender,
                'birthdate' => $this->individuals[1]->birthdate,
                'attributes' => [
                    $this->outOfRaceAttribute->id => [
                        'value' => 'yes', // Properly structured attribute value
                        'attribute_id' => $this->outOfRaceAttribute->id,
                    ],
                ],
            ],
        ]);
    // Verify initial state
    expect(AthleteEnrollment::count())->toBe(6)
        ->and($this->discipline->athlete_limit)->toBe(1);

    $component->call('submitEnrollment');

    // Verify new enrollments were created
    expect(AthleteEnrollment::count())->toBe(6)
        ->and(AthleteEnrollment::where('discipline_id', $this->discipline->id)->count())->toBe(2);
});

test('prevents enrollment when athlete exceeds max disciplines limit', function () {
    // Update competition to have a max of 1 discipline per athlete
    $this->competition->update(['max_disciplines_per_athlete' => 1]);

    // Create an existing enrollment for the first athlete
    AthleteEnrollment::factory()->create([
        'individual_id' => $this->individuals[0]->id,
        'event_id' => $this->event->id,
        'discipline_id' => Discipline::factory()->create(['enrollment_type' => 'individual'])->id,
    ]);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'athlete',
    ])
        ->set('selectedDiscipline', $this->discipline->id)
        ->set('enrollmentType', 'ATHLETE')
        ->set('currentStep', 2)
        ->set('selectedIndividuals', [
            [
                'id' => $this->individuals[0]->id,
                'name' => $this->individuals[0]->name,
                'member_code' => $this->individuals[0]->member_code,
                'gender' => $this->individuals[0]->gender,
                'birthdate' => $this->individuals[0]->birthdate,
            ],
        ]);

    $component->call('submitEnrollment');

    // Verify validation error
    $component->assertSee('The selected athlete(s) have reached the maximum number of individual disciplines. Limit is  (1)');

    // Verify no new enrollments were created
    expect(AthleteEnrollment::count())->toBe(7)
        ->and(AthleteEnrollment::where('discipline_id', $this->discipline->id)->count())->toBe(0);
});

test('allows enrollment when athlete has out-of-race attribute despite max disciplines limit', function () {
    // Update competition to have a max of 1 discipline per athlete
    $this->competition->update(['max_disciplines_per_athlete' => 1]);

    // Create and attach out-of-race attribute
    $outOfRaceAttribute = Attribute::factory()->create([
        'attribute_type' => EvtAttributeTypesEnum::OUTOFRACE,
        'name' => 'Out of Race',
        'attribute_data' => ['options' => ['yes' => 'Yes', 'no' => 'No']],
        'fillable_type' => 'MANUAL',
        'fillable_global' => 1,
    ]);
    $this->discipline->attributes()->attach($outOfRaceAttribute);

    // Create an existing enrollment for the first athlete with a different discipline
    $existingDiscipline = Discipline::factory()->create(['enrollment_type' => 'individual']);
    $existingEnrollment = AthleteEnrollment::factory()->create([
        'individual_id' => $this->individuals[0]->id,
        'event_id' => $this->event->id,
        'discipline_id' => $existingDiscipline->id,
        'federation_id' => $this->federation->id,
        'status_class' => EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
    ]);
    // Verify initial state
    expect(AthleteEnrollment::count())->toBe(7);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'athlete',
    ])
        ->set('selectedDiscipline', $this->discipline->id)
        ->set('enrollmentType', 'ATHLETE')
        ->set('currentStep', 2)
        ->set('selectedIndividuals', [
            [
                'id' => $this->individuals[0]->id,
                'name' => $this->individuals[0]->name,
                'member_code' => $this->individuals[0]->member_code,
                'gender' => $this->individuals[0]->gender,
                'birthdate' => $this->individuals[0]->birthdate,
            ],
        ]);

    // Set discipline attribute values for out-of-race
    $component->set("disciplineAttributeValues.{$this->individuals[0]->id}", [
        $outOfRaceAttribute->id => 'yes',
    ]);

    $component->call('submitEnrollment');

    // Verify new enrollment was created
    expect(AthleteEnrollment::count())->toBe(7)
        ->and(AthleteEnrollment::where('individual_id', $this->individuals[0]->id)->count())->toBe(2)
        ->and(AthleteEnrollment::where('discipline_id', $this->discipline->id)->count())->toBe(1);

    // Verify the out-of-race attribute was set correctly
    $newEnrollment = AthleteEnrollment::where('discipline_id', $this->discipline->id)
        ->where('individual_id', $this->individuals[0]->id)
        ->with('attributes')
        ->first();

    expect($newEnrollment->attributes->first()->value)->toBe('yes');
});

test('filters eligible athletes by required documents in entity enrollment', function () {
    $entity = Entity::factory()->create();

    // Create two athletes in the entity
    $athleteWithDoc = Individual::factory()->create(['gender' => 'male']);
    $athleteWithoutDoc = Individual::factory()->create(['gender' => 'male']);

    $athleteWithDoc->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $athleteWithoutDoc->individualEntities()->create([
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Give only one athlete the required medical document
    OfficialDocument::factory()->active()->create([
        'individual_id' => $athleteWithDoc->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    // Create event with required medical document
    $eventWithDocReq = Event::factory()->create([
        'event_category' => 'competition',
        'allow_individual_enrollment' => true,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addDay(),
    ]);

    Pricing::factory()->create([
        'event_id' => $eventWithDocReq->id,
        'price_type' => 'per_person',
        'price' => 0,
        'is_active' => true,
    ]);

    Competition::factory()->create([
        'event_id' => $eventWithDocReq->id,
        'required_athlete_documents' => [OfficialDocumentTypeEnum::MedicalStatement->value],
        'requires_local_federation_affiliation' => false,
        'requires_athlete_entity_sport_registration' => false,
    ]);

    Enrollment::factory()->create([
        'event_id' => $eventWithDocReq->id,
        'enrollable_id' => $entity->id,
        'enrollable_type' => Entity::class,
    ]);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $eventWithDocReq->fresh(),
        'model' => $entity,
        'enrollmentTypeSlug' => 'athlete',
    ]);

    $eligibleAthletes = $component->instance()->getAllEligibleAthletes()->get();

    expect($eligibleAthletes)->toHaveCount(1)
        ->and($eligibleAthletes->first()->id)->toBe($athleteWithDoc->id);
});
