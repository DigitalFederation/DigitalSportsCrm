<?php

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Exceptions\EnrollmentValidationException;
use App\Models\Group;
use App\Models\User;
use App\Services\EnrollmentEligibilityService;
use Domain\Documents\Models\DocumentType;
use Domain\EvtEvents\Actions\PreRegisterParticipantsAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create document type
    DocumentType::factory()->create([
        'code' => 'ORD',
        'name' => 'Order',
    ]);

    // Create one Discipline to reuse
    $discipline = Discipline::factory()->create();

    // Create event with proper state and enrollment settings
    $this->event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'allow_coach_enrollment' => true,
        'allow_referee_enrollment' => true,
        'allow_individual_enrollment' => true,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addDay(),
    ]);

    $this->federation = Federation::factory()->create();
    $this->individuals = Individual::factory()->count(3)->create()->each(function ($individual) {
        $individual->federations()->attach($this->federation->id);
    });

    // Find or create the FEDERATION group
    $federationGroup = Group::firstOrCreate(['code' => 'FEDERATION'], ['name' => 'Federation']);

    // Create user and associate with the Federation group
    $this->user = User::factory()->create([
        'group_id' => $federationGroup->id,
    ]);

    // Attach the user to the federation
    $this->user->federations()->attach($this->federation->id);

    // Create pricing for each role with specific prices
    $this->pricings = collect([
        'athlete' => Pricing::factory()->create([
            'event_id' => $this->event->id,
            'discipline_id' => $discipline->id,
            'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE,
            'is_active' => true,
            'price' => 100,
            'price_type' => 'PER_PERSON',
        ]),
        'coach' => Pricing::factory()->create([
            'event_id' => $this->event->id,
            'discipline_id' => $discipline->id,
            'enrollment_role' => EvtEventEnrollmentRoleEnum::COACH,
            'is_active' => true,
            'price' => 50,
            'price_type' => 'PER_PERSON',
        ]),
        'referee' => Pricing::factory()->create([
            'event_id' => $this->event->id,
            'discipline_id' => $discipline->id,
            'enrollment_role' => EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL,
            'is_active' => true,
            'price' => 75,
            'price_type' => 'PER_PERSON',
        ]),
    ]);

    // Mock the services
    $this->mock(EnrollmentEligibilityService::class, function ($mock) {
        $mock->shouldReceive('canEnrollInEvent')->andReturn(true);
    });

    $this->mock(EnrollmentsCostCalculationService::class, function ($mock) {
        $mock->shouldReceive('calculateTotalCost')
            ->andReturn(225.0);

        $mock->shouldReceive('getCostBreakdown')
            ->andReturn([
                ['type' => 'Per Person Fee', 'cost' => 150.0],
                ['type' => 'Event Fee', 'cost' => 75.0],
            ]);
    });
});

it('successfully pre-registers multiple role types', function () {
    $this->actingAs($this->user);

    $participants = [
        'athlete' => [
            [
                'id' => $this->individuals[0]->id,
                'role' => 'athlete',
                'pricing_id' => $this->pricings['athlete']->id,
            ],
        ],
        'coach' => [
            [
                'id' => $this->individuals[1]->id,
                'role' => 'coach',
                'pricing_id' => $this->pricings['coach']->id,
            ],
        ],
        'referee' => [
            [
                'id' => $this->individuals[2]->id,
                'role' => 'referee',
                'pricing_id' => $this->pricings['referee']->id,
                'attributes' => [],
            ],
        ],
    ];

    $action = new PreRegisterParticipantsAction;
    $enrollment = $action->execute($this->event, $this->federation, $participants);

    // Verify enrollment creation
    expect($enrollment)->toBeInstanceOf(Enrollment::class)
        ->and($enrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PENDING)
        ->and($enrollment->event_id)->toBe($this->event->id)
        ->and($enrollment->enrollable_id)->toBe($this->federation->id)
        ->and($enrollment->user_id)->toBe($this->user->id);

    // Verify role-specific enrollments
    expect($enrollment->athleteEnrollments)->toHaveCount(1)
        ->and($enrollment->coachEnrollments)->toHaveCount(1)
        ->and($enrollment->refereeEnrollments)->toHaveCount(1);

    // Verify payment document creation
    expect($enrollment->document)->not->toBeNull()
        ->and($enrollment->document->total_value)->toBe('225.00');
});

it('validates coach enrollment permission', function () {
    $this->actingAs($this->user);
    $this->event->update(['allow_coach_enrollment' => false]);

    $participants = [
        'coach' => [
            ['id' => $this->individuals[0]->id],
        ],
    ];

    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, $participants))
        ->toThrow(EnrollmentValidationException::class, 'Coach enrollment is not allowed for this event.');
});

it('validates referee enrollment permission', function () {
    $this->actingAs($this->user);
    $this->event->update(['allow_referee_enrollment' => false]);

    $participants = [
        'referee' => [
            ['id' => $this->individuals[0]->id],
        ],
    ];

    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, $participants))
        ->toThrow(EnrollmentValidationException::class, 'Referee enrollment is not allowed for this event.');
});

it('handles empty participant lists', function () {
    $this->actingAs($this->user);
    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, []))
        ->toThrow(EnrollmentValidationException::class, 'No athletes provided for enrollment.');
});

it('calculates correct total cost for mixed roles', function () {
    $this->actingAs($this->user);

    // Define specific mock expectations for this test
    $this->mock(EnrollmentsCostCalculationService::class, function ($mock) {
        // Expect calculation for 1 athlete (100) + 1 coach (50) = 150
        $mock->shouldReceive('calculateTotalCost')
            // ->withArgs(...) // Optionally add specific args check if needed
            ->zeroOrMoreTimes()
            ->andReturn(150.0);

        // Expect breakdown reflecting the 100 + 50 cost
        $mock->shouldReceive('getCostBreakdown')
            // ->withArgs(...) // Optionally add specific args check if needed
            ->zeroOrMoreTimes()
            ->andReturn([
                [
                    'description' => 'Athlete Fee', // Example description
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'total_value' => 100.00,
                    'pricing_id' => $this->pricings['athlete']->id, // Use the actual pricing ID
                    'type' => 'PER_PERSON', // Example type
                ],
                [
                    'description' => 'Coach Fee', // Example description
                    'quantity' => 1,
                    'unit_price' => 50.00,
                    'total_value' => 50.00,
                    'pricing_id' => $this->pricings['coach']->id, // Use the actual pricing ID
                    'type' => 'PER_PERSON', // Example type
                ],
            ]);
        // Add ->shouldNotReceive(...) for other methods if necessary
    });

    // These participants should total to 150.00:
    $participants = [
        'athlete' => [
            [
                'id' => $this->individuals[0]->id,
                'role' => 'athlete',
            ],
        ],
        'coach' => [
            [
                'id' => $this->individuals[1]->id,
                'role' => 'coach',
            ],
        ],
    ];

    // Ensure we have a fresh event instance
    $event = Event::find($this->event->id);

    $action = new PreRegisterParticipantsAction;
    $enrollment = $action->execute($event, $this->federation, $participants);

    // Verify the total cost calculation
    expect($enrollment->document->total_value)->toBe('150.00')
        ->and($enrollment->document->details)->toHaveCount(2)
        ->and($enrollment->document->details[0]->total_value)->toBe('100.00') // Athlete
        ->and($enrollment->document->details[1]->total_value)->toBe('50.00'); // Coach
});

it('fails for inactive event', function () {
    $this->actingAs($this->user);
    $this->event->update(['status_class' => CanceledEventState::class]);

    $participants = [
        'athlete' => [
            ['id' => $this->individuals[0]->id],
        ],
    ];

    $action = new PreRegisterParticipantsAction;

    expect(fn () => $action->execute($this->event, $this->federation, $participants))
        ->toThrow(EnrollmentValidationException::class, 'Event is not open for enrollment.');
});
