<?php

use App\Livewire\EvtEvents\ManageEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();

    $this->event = Event::factory()->create([
        'event_category' => 'competition',
        'allow_individual_enrollment' => true,
        'allow_referee_enrollment' => true,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addDay(),
    ]);

    $this->pricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'per_person',
        'price' => 0,
        'is_active' => true,
    ]);

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    // Create a referee professional role
    $this->refereeRole = ProfessionalRole::factory()->create([
        'role' => 'TECHNICAL_OFFICIAL',
        'name' => 'Test Technical Official',
        'code' => 'TESTTECHNICALOFFICIAL',
    ]);

    // Create individuals with active federation membership and referee role
    $this->individuals = Individual::factory()->count(3)->create();

    foreach ($this->individuals as $individual) {
        // Add federation membership
        $individual->individualFederations()->create([
            'federation_id' => $this->federation->id,
            'status_class' => ActiveIndividualFederationState::class,
        ]);

        // Add referee professional role
        $individual->professionalRoles()->attach($this->refereeRole->id);
    }
});

it('can mount manage enrollment component with referee type', function () {
    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ]);

    $component->assertSet('enrollmentType', 'TECHNICAL_OFFICIAL')
        ->assertStatus(200);
});

it('can enroll referees successfully', function () {
    expect(RefereeEnrollment::count())->toBe(0);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ])
        ->set('enrollmentType', 'TECHNICAL_OFFICIAL')
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

    expect(RefereeEnrollment::count())->toBe(1);

    $enrollment = RefereeEnrollment::first();
    expect($enrollment->individual_id)->toBe($this->individuals[0]->id)
        ->and($enrollment->event_id)->toBe($this->event->id)
        ->and($enrollment->federation_id)->toBe($this->federation->id)
        ->and($enrollment->status_class)->toBe(PendingRefereeEnrollmentState::class);
});

it('can enroll multiple referees at once', function () {
    expect(RefereeEnrollment::count())->toBe(0);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ])
        ->set('enrollmentType', 'TECHNICAL_OFFICIAL')
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

    $component->call('submitEnrollment');

    expect(RefereeEnrollment::count())->toBe(2);
});

it('excludes already enrolled referees from eligible list', function () {
    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forFederation($this->federation)
        ->forIndividual($this->individuals[0])
        ->create([
            'enrollment_id' => $this->enrollment->id,
            'status_class' => PendingRefereeEnrollmentState::class,
        ]);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ]);

    $component->set('enrollmentType', 'TECHNICAL_OFFICIAL')
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

    expect(RefereeEnrollment::where('individual_id', $this->individuals[0]->id)->count())->toBe(1);
});

it('allows enrollment of referee with canceled status', function () {
    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forFederation($this->federation)
        ->forIndividual($this->individuals[0])
        ->canceled()
        ->create([
            'enrollment_id' => $this->enrollment->id,
        ]);

    expect(RefereeEnrollment::where('individual_id', $this->individuals[0]->id)->count())->toBe(1);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ])
        ->set('enrollmentType', 'TECHNICAL_OFFICIAL')
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

    $enrollment = RefereeEnrollment::where('individual_id', $this->individuals[0]->id)->first();
    expect($enrollment->status_class)->toBe(PendingRefereeEnrollmentState::class);
});

it('displays correct heading for referee enrollment', function () {
    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ]);

    $component->assertSet('enrollmentType', 'TECHNICAL_OFFICIAL');
});

it('creates enrollment record with correct data after successful referee enrollment', function () {
    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ])
        ->set('enrollmentType', 'TECHNICAL_OFFICIAL')
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

    $enrollment = RefereeEnrollment::where('individual_id', $this->individuals[0]->id)
        ->where('event_id', $this->event->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->federation_id)->toBe($this->federation->id)
        ->and($enrollment->price)->toBe(0.0)
        ->and($enrollment->price_type)->toBe('FREE');
});

it('only shows individuals with REFEREE professional role in eligible list', function () {
    // Create an individual with federation membership but WITHOUT referee role
    $individualWithoutRole = Individual::factory()->create();
    $individualWithoutRole->individualFederations()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ]);

    $eligibleQuery = $component->instance()->getEligibleIndividualsQuery();
    $eligibleIds = $eligibleQuery->pluck('id')->toArray();

    // Should NOT contain individual without referee role
    expect($eligibleIds)->not->toContain($individualWithoutRole->id)
        // Should contain individuals with referee role
        ->and($eligibleIds)->toContain($this->individuals[0]->id)
        ->and($eligibleIds)->toContain($this->individuals[1]->id)
        ->and($eligibleIds)->toContain($this->individuals[2]->id);
});

it('only shows individuals with active federation membership', function () {
    // Create an individual with referee role but WITHOUT federation membership
    $individualWithoutMembership = Individual::factory()->create();
    $individualWithoutMembership->professionalRoles()->attach($this->refereeRole->id);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ]);

    $eligibleQuery = $component->instance()->getEligibleIndividualsQuery();
    $eligibleIds = $eligibleQuery->pluck('id')->toArray();

    expect($eligibleIds)->not->toContain($individualWithoutMembership->id)
        ->and($eligibleIds)->toContain($this->individuals[0]->id);
});

it('excludes already enrolled referees from eligible list via query', function () {
    // Create an existing referee enrollment
    RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forFederation($this->federation)
        ->forIndividual($this->individuals[0])
        ->create([
            'enrollment_id' => $this->enrollment->id,
            'status_class' => PendingRefereeEnrollmentState::class,
        ]);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'referee',
    ]);

    // The query should exclude the already enrolled individual
    $eligibleQuery = $component->instance()->getEligibleIndividualsQuery();
    $eligibleIds = $eligibleQuery->pluck('id')->toArray();

    expect($eligibleIds)->not->toContain($this->individuals[0]->id)
        ->and($eligibleIds)->toContain($this->individuals[1]->id)
        ->and($eligibleIds)->toContain($this->individuals[2]->id);
});
