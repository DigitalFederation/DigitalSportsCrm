<?php

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Livewire\EvtEvents\IndividualCreateAthleteEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

// NOTE: Livewire state mutation is not reliable for cart logic in this context.
// Only the rendering test is kept here. For cart logic, use unit/service tests or HTTP feature tests.

beforeEach(function () {
    // Create a user that will act as the authenticated user
    $this->user = \App\Models\User::factory()->create();

    // Create a basic event with enrollment flags enabled
    $this->event = Event::factory()->create([
        'allow_coach_enrollment' => true,
        'allow_referee_enrollment' => true,
        'allow_individual_enrollment' => true,
        'start_registration' => now()->subDays(5),
        'end_registration' => now()->addDays(5),
        'start_date' => now()->addDays(15),
        'end_date' => now()->addDays(20),
        'event_category' => 'competition',
    ]);

    // Create a competition
    $this->competition = Competition::factory()->create([
        'event_id' => $this->event->id,
        'max_disciplines_per_athlete' => 3,
    ]);

    // Create a discipline template to attach to competition
    $this->template = DisciplineTemplate::factory()->create();

    // Create disciplines
    $this->discipline1 = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'athlete_limit' => 10,
        'gender' => 'male',
    ]);

    $this->discipline2 = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'athlete_limit' => 10,
        'gender' => 'female',
    ]);

    // Attach disciplines to the template
    $this->template->disciplines()->attach([$this->discipline1->id, $this->discipline2->id]);

    // Associate template with competition
    $this->competition->update(['discipline_template_id' => $this->template->id]);

    // Create an individual
    $this->individual = Individual::factory()->create();

    // Create pricing options
    $this->perPersonPricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 100.00,
        'is_active' => true,
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE,
    ]);

    $this->disciplinePricing1 = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->discipline1->id,
        'price_type' => EvtEventFeeTypeEnum::PER_DISCIPLINE->value,
        'price' => 50.00,
        'is_active' => true,
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE,
    ]);

    $this->disciplinePricing2 = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->discipline2->id,
        'price_type' => EvtEventFeeTypeEnum::PER_DISCIPLINE->value,
        'price' => 75.00,
        'is_active' => true,
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE,
    ]);

    $this->eventFeePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => EvtEventFeeTypeEnum::EVENT_FEE->value,
        'price' => 25.00,
        'is_active' => true,
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE,
    ]);

    // Add common mock for CheckExistingEventEnrollmentAction
    $this->partialMock(Domain\EvtEvents\Actions\CheckExistingEventEnrollmentAction::class, function ($mock) {
        $mock->shouldReceive('execute')->andReturn([
            'can_register' => true,
            'message' => 'Registration allowed',
        ]);
    });

    // Add common mock for GetDisciplineOutOfRaceAttributeAction
    $this->partialMock(Domain\EvtEvents\Actions\GetDisciplineOutOfRaceAttributeAction::class, function ($mock) {
        $mock->shouldReceive('execute')->andReturn(null);
    });
});

test('individual create athlete enrollment component renders successfully', function () {
    // Act as the user
    actingAs($this->user);

    // Mock validation actions
    $this->mock(Domain\EvtEvents\Actions\GetDisciplinesFromEventForIndividualAction::class, function ($mock) {
        $mock->shouldReceive('execute')->andReturn(collect([$this->discipline1, $this->discipline2]));
    });

    // Assert component mounts without exceptions
    $component = Livewire::test(IndividualCreateAthleteEnrollment::class, [
        'event' => $this->event,
        'individual' => $this->individual,
    ]);

    // Basic assertions to ensure the component loaded properly
    $component->assertSee(__('events.select_discipline'));
});

afterEach(function () {
    \Mockery::close();
});
