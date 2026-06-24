<?php

use App\Livewire\EvtEvents\ManageEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\PendingTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();

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

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);

    // Create individuals with active federation membership
    $this->individuals = Individual::factory()->count(3)->create();

    // Associate individuals with federation
    foreach ($this->individuals as $individual) {
        $individual->individualFederations()->create([
            'federation_id' => $this->federation->id,
            'status_class' => ActiveIndividualFederationState::class,
        ]);
    }
});

it('can mount manage enrollment component with official type', function () {
    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ]);

    $component->assertSet('enrollmentType', 'OFFICIAL')
        ->assertStatus(200);
});

it('can enroll officials successfully', function () {
    expect(TeamOfficialEnrollment::count())->toBe(0);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ])
        ->set('enrollmentType', 'OFFICIAL')
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

    expect(TeamOfficialEnrollment::count())->toBe(1);

    $enrollment = TeamOfficialEnrollment::first();
    expect($enrollment->individual_id)->toBe($this->individuals[0]->id)
        ->and($enrollment->event_id)->toBe($this->event->id)
        ->and($enrollment->federation_id)->toBe($this->federation->id)
        ->and($enrollment->status_class)->toBe(PendingTeamOfficialEnrollmentState::class);
});

it('can enroll multiple officials at once', function () {
    expect(TeamOfficialEnrollment::count())->toBe(0);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ])
        ->set('enrollmentType', 'OFFICIAL')
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

    expect(TeamOfficialEnrollment::count())->toBe(2);
});

it('excludes already enrolled officials from eligible list', function () {
    // Create an existing official enrollment
    TeamOfficialEnrollment::factory()->create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individuals[0]->id,
        'federation_id' => $this->federation->id,
        'enrollment_id' => $this->enrollment->id,
        'status_class' => PendingTeamOfficialEnrollmentState::class,
    ]);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ]);

    // The query should exclude the already enrolled individual
    $eligibleQuery = $component->instance()->getEligibleIndividualsQuery();
    $eligibleIds = $eligibleQuery->pluck('id')->toArray();

    expect($eligibleIds)->not->toContain($this->individuals[0]->id)
        ->and($eligibleIds)->toContain($this->individuals[1]->id)
        ->and($eligibleIds)->toContain($this->individuals[2]->id);
});

it('allows enrollment of official with canceled status', function () {
    // Create a canceled official enrollment
    TeamOfficialEnrollment::factory()->create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individuals[0]->id,
        'federation_id' => $this->federation->id,
        'enrollment_id' => $this->enrollment->id,
        'status_class' => CanceledTeamOfficialEnrollmentState::class,
    ]);

    expect(TeamOfficialEnrollment::where('individual_id', $this->individuals[0]->id)->count())->toBe(1);

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ])
        ->set('enrollmentType', 'OFFICIAL')
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

    // Should have active enrollment
    $activeEnrollment = TeamOfficialEnrollment::where('individual_id', $this->individuals[0]->id)
        ->where('status_class', PendingTeamOfficialEnrollmentState::class)
        ->first();

    expect($activeEnrollment)->not->toBeNull();
});

it('displays correct heading for official enrollment', function () {
    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ]);

    $component->assertSet('enrollmentType', 'OFFICIAL');
});

it('only shows individuals with active federation membership', function () {
    // Create an individual without federation membership
    $individualWithoutMembership = Individual::factory()->create();

    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ]);

    $eligibleQuery = $component->instance()->getEligibleIndividualsQuery();
    $eligibleIds = $eligibleQuery->pluck('id')->toArray();

    expect($eligibleIds)->not->toContain($individualWithoutMembership->id)
        ->and($eligibleIds)->toContain($this->individuals[0]->id);
});

it('creates enrollment record with correct data after successful official enrollment', function () {
    $component = Livewire::test(ManageEnrollment::class, [
        'event' => $this->event,
        'model' => $this->federation,
        'enrollmentTypeSlug' => 'official',
    ])
        ->set('enrollmentType', 'OFFICIAL')
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

    $enrollment = TeamOfficialEnrollment::where('individual_id', $this->individuals[0]->id)
        ->where('event_id', $this->event->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->federation_id)->toBe($this->federation->id);
});
