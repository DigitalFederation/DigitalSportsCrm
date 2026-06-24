<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\EvtEvents\Actions\PreRegisterParticipantsAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\Services\EnrollmentCreditService;
use Domain\EvtEvents\States\ActiveCoachEnrollmentState;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\ActiveTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create and authenticate user with proper federation group
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $this->user = User::factory()->create(['group_id' => $group->id]);
    // Use Auth facade
    Auth::login($this->user);

    // Create document type for ordering
    \Domain\Documents\Models\DocumentType::create([
        'name' => 'Order',
        'code' => 'ORD',
        'description' => 'Default order document type',
    ]);

    // Create sport for the competition
    $this->sport = Sport::factory()->create();

    // Create the event
    $this->event = Event::factory()->create([
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'allow_coach_enrollment' => true,
        'allow_referee_enrollment' => true,
        'allow_individual_enrollment' => true,
        'start_registration' => now()->subDays(1),
        'end_registration' => now()->addDays(10),
        'start_date' => now()->addDays(15),
        'end_date' => now()->addDays(20),
    ]);

    // Create discipline template
    $this->disciplineTemplate = DisciplineTemplate::factory()->create();

    // Create a discipline
    $this->discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'athlete_limit' => 10,
        'sport_id' => $this->sport->id,
    ]);

    // Attach the discipline to the template
    $this->disciplineTemplate->disciplines()->attach($this->discipline->id);

    // Create competition with proper settings
    $this->competition = Competition::factory()->create([
        'event_id' => $this->event->id,
        'sport_id' => $this->sport->id,
        'discipline_template_id' => $this->disciplineTemplate->id,
        'max_disciplines_per_athlete' => 2,
        'requires_athlete_adel' => false,
        'requires_coach_adel' => false,
        'requires_referee_adel' => false,
    ]);

    // Create federation
    $this->federation = Federation::factory()->create();

    // Attach user to federation
    $this->user->federations()->attach($this->federation->id);

    // Create pricing for athletes and coaches
    $this->athletePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'enrollment_role' => 'ATHLETE',
        'price_type' => 'PER_PERSON',
        'price' => 10.00, // Price per athlete
        'is_active' => true,
    ]);

    $this->coachPricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'enrollment_role' => 'COACH',
        'price_type' => 'PER_PERSON',
        'price' => 15.00, // Price per coach
        'is_active' => true,
    ]);

    // Create individuals for the federation
    $this->athletes = Individual::factory()->count(3)->create();
    $this->coaches = Individual::factory()->count(2)->create();

    // Attach all individuals to the federation with active status
    foreach ($this->athletes as $athlete) {
        $this->federation->individuals()->attach($athlete->id, [
            'status_class' => ActiveIndividualFederationState::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    foreach ($this->coaches as $coach) {
        $this->federation->individuals()->attach($coach->id, [
            'status_class' => ActiveIndividualFederationState::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Create the enrollment credit service
    $this->creditService = new EnrollmentCreditService;
});

it('generates credits when removing an athlete with PAID status', function () {
    // Create an enrollment with a paid athlete
    $enrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => get_class($this->federation),
        'payment_status' => EvtEventPaymentStatusEnum::PAID,
        'user_id' => $this->user->id,
        'total_price' => 10.00,
    ]);

    $athlete = $this->athletes[0];
    $athleteEnrollment = AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $athlete->id,
        'per_person_pricing_id' => $this->athletePricing->id,
        'per_person_price' => 10.00,
        'total_price' => 10.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
    ]);

    // Verify no credits exist initially
    $initialCredits = DB::table('evt_enrollment_credits')
        ->where('event_id', $this->event->id)
        ->where('enrollable_id', $this->federation->id)
        ->where('enrollable_type', get_class($this->federation))
        ->where('role_type', 'athlete')
        ->first();
    expect($initialCredits)->toBeNull('No credits should exist initially');

    // Generate a credit by removing the athlete enrollment
    $creditResult = $this->creditService->addCredit($athleteEnrollment);

    // Verify credit was added
    expect($creditResult['role_type'])->toBe('athlete');
    expect($creditResult['slots_added'])->toBe(1);
    expect($creditResult['monetary_value'])->toEqual(10.00);

    // Check the database record
    $storedCredit = DB::table('evt_enrollment_credits')
        ->where('event_id', $this->event->id)
        ->where('enrollable_id', $this->federation->id)
        ->where('enrollable_type', get_class($this->federation))
        ->where('role_type', 'athlete')
        ->first();

    expect($storedCredit)->not->toBeNull('Credit should be stored in database');
    expect($storedCredit->available_slots)->toBe(1);
    expect((float) $storedCredit->monetary_value)->toEqual(10.00);
});

it('generates credits when removing a coach with ActiveCoachEnrollmentState status', function () {
    // Create an enrollment with a paid coach
    $enrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => get_class($this->federation),
        'payment_status' => EvtEventPaymentStatusEnum::PAID,
        'user_id' => $this->user->id,
        'total_price' => 15.00,
    ]);

    $coach = $this->coaches[0];
    $coachEnrollment = CoachEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $coach->id,
        'pricing_id' => $this->coachPricing->id,
        'total_price' => 15.00,
        'status_class' => ActiveCoachEnrollmentState::class,
    ]);

    // Generate a credit by removing the coach enrollment
    $creditResult = $this->creditService->addCredit($coachEnrollment);

    // Verify credit was added
    expect($creditResult['role_type'])->toBe('coach');
    expect($creditResult['slots_added'])->toBe(1);
    expect($creditResult['monetary_value'])->toEqual(15.00);

    // Check the database record
    $storedCredit = DB::table('evt_enrollment_credits')
        ->where('event_id', $this->event->id)
        ->where('enrollable_id', $this->federation->id)
        ->where('enrollable_type', get_class($this->federation))
        ->where('role_type', 'coach')
        ->first();

    expect($storedCredit)->not->toBeNull('Credit should be stored in database');
    expect($storedCredit->available_slots)->toBe(1);
    expect((float) $storedCredit->monetary_value)->toEqual(15.00);
});

it('generates credits when removing a team official with active status', function () {
    // Create an enrollment with a paid team official
    $enrollment = Enrollment::create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => get_class($this->federation),
        'payment_status' => EvtEventPaymentStatusEnum::PAID,
        'user_id' => $this->user->id,
        'total_price' => 20.00,
    ]);

    // Create pricing for team officials
    $officialPricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'enrollment_role' => 'OFFICIAL',
        'price_type' => 'PER_PERSON',
        'price' => 20.00, // Price per official
        'is_active' => true,
    ]);

    // Create a team official individual
    $official = Individual::factory()->create();
    $this->federation->individuals()->attach($official->id, [
        'status_class' => ActiveIndividualFederationState::class,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $officialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $official->id,
        'pricing_id' => $officialPricing->id,
        'price' => 20.00,
        'status_class' => ActiveTeamOfficialEnrollmentState::class,
    ]);

    // Generate a credit by removing the team official enrollment
    $creditResult = $this->creditService->addCredit($officialEnrollment);

    // Verify credit was added
    expect($creditResult['role_type'])->toBe('official');
    expect($creditResult['slots_added'])->toBe(1);
    expect($creditResult['monetary_value'])->toEqual(20.00);

    // Check the database record
    $storedCredit = DB::table('evt_enrollment_credits')
        ->where('event_id', $this->event->id)
        ->where('enrollable_id', $this->federation->id)
        ->where('enrollable_type', get_class($this->federation))
        ->where('role_type', 'official')
        ->first();

    expect($storedCredit)->not->toBeNull('Credit should be stored in database');
    expect($storedCredit->available_slots)->toBe(1);
    expect((float) $storedCredit->monetary_value)->toEqual(20.00);
});

it('applies credits when registering new participants of the same role', function () {
    // First, add a credit for an athlete
    DB::table('evt_enrollment_credits')->insert([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => get_class($this->federation),
        'role_type' => 'athlete',
        'available_slots' => 1,
        'monetary_value' => 10.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Set up participants data for registration
    $participants = [
        'athlete' => [
            ['id' => $this->athletes[1]->id],
        ],
    ];

    // Check if credits are applied during registration
    $creditsUsed = $this->creditService->useCredits($this->event, $this->federation, $participants);

    // Verify credit usage
    expect($creditsUsed)->toHaveKey('athlete');
    expect($creditsUsed['athlete']['slots_used'])->toBe(1);
    expect($creditsUsed['athlete']['monetary_value'])->toBe(10.00);

    // Check that credits were deducted from the database
    $remainingCredit = DB::table('evt_enrollment_credits')
        ->where('event_id', $this->event->id)
        ->where('enrollable_id', $this->federation->id)
        ->where('enrollable_type', get_class($this->federation))
        ->where('role_type', 'athlete')
        ->first();

    expect($remainingCredit->available_slots)->toBe(0);
    expect((float) $remainingCredit->monetary_value)->toEqual(0.00);
});

it('creates a zero-cost enrollment when credits fully cover the cost', function () {
    // First, add a credit for an athlete
    DB::table('evt_enrollment_credits')->insert([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => get_class($this->federation),
        'role_type' => 'athlete',
        'available_slots' => 1,
        'monetary_value' => 10.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Set up participants data for registration
    $participants = [
        'athlete' => [
            ['id' => $this->athletes[1]->id],
        ],
    ];

    // Use PreRegisterParticipantsAction to register with credits
    $action = new PreRegisterParticipantsAction;
    $formattedParticipants = [];
    foreach ($participants as $roleType => $roleParticipants) {
        $formattedParticipants[$roleType] = array_map(function ($participant) {
            return [
                'id' => $participant['id'],
                'discipline_id' => null,
            ];
        }, $roleParticipants);
    }

    $creditsUsed = $this->creditService->useCredits($this->event, $this->federation, $participants);
    $enrollment = $action->execute($this->event, $this->federation, $formattedParticipants, $creditsUsed);

    // Verify the enrollment has zero cost
    expect((float) $enrollment->total_price)->toEqual(0.0);

    // Verify the athlete enrollment is marked as PAID
    $athleteEnrollment = AthleteEnrollment::where('enrollment_id', $enrollment->id)->first();
    expect($athleteEnrollment->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::PAID);

    // Verify no document was created
    expect($enrollment->document_id)->toBeNull();
});
