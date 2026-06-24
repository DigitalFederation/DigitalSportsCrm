<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use App\Enums\EvtEventCategoryTypeEnum;
use App\Models\Group;
use Domain\Documents\Models\DocumentType;
use Domain\EvtEvents\Actions\ActivateEnrollmentsAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentOrderAction;
use Domain\EvtEvents\Actions\GetWaitingListSelectedIndividualsAction;
use Domain\EvtEvents\Actions\ManualGenerateAthleteEnrollmentPaymentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\PendingIndividualEnrollmentState;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --------------------------------------------------------------------------
// Helper: set up an individual user that can hit /individual routes
// --------------------------------------------------------------------------
function createIndividualUser(): array
{
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->load('group');

    return [$user, $individual];
}

// --------------------------------------------------------------------------
// Helper: create an event open for organization enrollments
// --------------------------------------------------------------------------
function createOpenOrganizationEvent(array $overrides = []): Event
{
    return Event::factory()->create(array_merge([
        'event_category' => EvtEventCategoryTypeEnum::organization->value,
        'status_class' => ActiveEventState::class,
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addWeek(),
    ], $overrides));
}

// --------------------------------------------------------------------------
// Helper: create a pending individual enrollment in the waiting list
// --------------------------------------------------------------------------
function createPendingIndividualEnrollment(
    Event $event,
    Individual $individual,
    ?Pricing $pricing = null,
    ?float $price = null
): array {
    $enrollment = Enrollment::factory()->create([
        'event_id' => $event->id,
        'user_id' => $individual->user_id,
        'enrollable_id' => $individual->id,
        'enrollable_type' => Individual::class,
        'activated_at' => null,
        'pricing_id' => $pricing?->id,
    ]);

    $individualEnrollment = IndividualEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $event->id,
        'individual_id' => $individual->id,
        'status_class' => PendingIndividualEnrollmentState::class,
        'pricing_id' => $pricing?->id,
        'price' => $price ?? $pricing?->price ?? 0,
        'price_type' => $pricing?->price_type,
    ]);

    return [$enrollment, $individualEnrollment];
}

// ==========================================================================
// CreateIndividualEnrollmentOrderAction - Unit-level tests
// ==========================================================================

describe('CreateIndividualEnrollmentOrderAction', function () {

    beforeEach(function () {
        DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order', 'prefix' => 'ORD']);
    });

    it('creates an order document with correct owner type and id for an individual', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $pricing = Pricing::factory()->create([
            'event_id' => $event->id,
            'price_type' => 'PER_PERSON',
            'price' => 25.00,
            'is_active' => true,
            'enrollment_role' => 'INDIVIDUAL',
        ]);

        [$enrollment] = createPendingIndividualEnrollment($event, $individual, $pricing, 25.00);

        $selectedIndividuals = new EloquentCollection([
            [
                'id' => $individual->id,
                'individual_id' => $individual->id,
                'name' => $individual->name,
                'surname' => $individual->surname,
                'role' => 'INDIVIDUAL',
                'pricing_id' => $pricing->id,
                'price' => 25.00,
            ],
        ]);

        $costCalculationService = new EnrollmentsCostCalculationService;
        $action = new CreateIndividualEnrollmentOrderAction($costCalculationService);

        $document = $action->execute(
            $event,
            $enrollment,
            $individual->id,
            Individual::class,
            $selectedIndividuals,
            [$enrollment->id => $pricing->id]
        );

        expect($document)->not->toBeNull();

        $this->assertDatabaseHas('document', [
            'id' => $document->id,
            'owner_id' => $individual->id,
            'owner_type' => 'individual',
        ]);

        $this->assertDatabaseHas('document_detail', [
            'document_id' => $document->id,
            'owner_type' => Enrollment::class,
            'owner_id' => $enrollment->id,
        ]);
    });

    it('returns null when total cost is zero', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $pricing = Pricing::factory()->create([
            'event_id' => $event->id,
            'price_type' => 'PER_PERSON',
            'price' => 0,
            'is_active' => true,
            'enrollment_role' => 'INDIVIDUAL',
        ]);

        [$enrollment] = createPendingIndividualEnrollment($event, $individual, $pricing, 0);

        $selectedIndividuals = new EloquentCollection([
            [
                'id' => $individual->id,
                'individual_id' => $individual->id,
                'name' => $individual->name,
                'surname' => $individual->surname,
                'role' => 'INDIVIDUAL',
                'pricing_id' => $pricing->id,
                'price' => 0,
            ],
        ]);

        $costCalculationService = new EnrollmentsCostCalculationService;
        $action = new CreateIndividualEnrollmentOrderAction($costCalculationService);

        $document = $action->execute(
            $event,
            $enrollment,
            $individual->id,
            Individual::class,
            $selectedIndividuals,
            [$enrollment->id => $pricing->id]
        );

        expect($document)->toBeNull();
        $this->assertDatabaseMissing('document', [
            'owner_id' => $individual->id,
        ]);
    });

    it('creates document with correct total for multiple individuals', function () {
        $event = createOpenOrganizationEvent();
        $individual1 = Individual::factory()->create();
        $individual2 = Individual::factory()->create();

        $pricing = Pricing::factory()->create([
            'event_id' => $event->id,
            'price_type' => 'PER_PERSON',
            'price' => 15.00,
            'is_active' => true,
            'enrollment_role' => 'INDIVIDUAL',
        ]);

        [$enrollment1] = createPendingIndividualEnrollment($event, $individual1, $pricing, 15.00);
        IndividualEnrollment::create([
            'enrollment_id' => $enrollment1->id,
            'event_id' => $event->id,
            'individual_id' => $individual2->id,
            'status_class' => PendingIndividualEnrollmentState::class,
            'pricing_id' => $pricing->id,
            'price' => 15.00,
            'price_type' => 'PER_PERSON',
        ]);

        $selectedIndividuals = new EloquentCollection([
            [
                'id' => $individual1->id,
                'individual_id' => $individual1->id,
                'name' => $individual1->name,
                'surname' => $individual1->surname,
                'role' => 'INDIVIDUAL',
                'pricing_id' => $pricing->id,
                'price' => 15.00,
            ],
            [
                'id' => $individual2->id,
                'individual_id' => $individual2->id,
                'name' => $individual2->name,
                'surname' => $individual2->surname,
                'role' => 'INDIVIDUAL',
                'pricing_id' => $pricing->id,
                'price' => 15.00,
            ],
        ]);

        $costCalculationService = new EnrollmentsCostCalculationService;
        $action = new CreateIndividualEnrollmentOrderAction($costCalculationService);

        $document = $action->execute(
            $event,
            $enrollment1,
            $individual1->id,
            Individual::class,
            $selectedIndividuals,
            [$enrollment1->id => $pricing->id]
        );

        expect($document)->not->toBeNull();
        expect($document->details)->not->toBeEmpty();
        expect((float) $document->total_value)->toBe(30.00);
    });
});

// ==========================================================================
// GetWaitingListSelectedIndividualsAction
// ==========================================================================

describe('GetWaitingListSelectedIndividualsAction', function () {

    it('extracts individual enrollments from pending enrollments', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        [$enrollment, $individualEnrollment] = createPendingIndividualEnrollment($event, $individual);

        $pendingEnrollments = Enrollment::where('id', $enrollment->id)
            ->with('individualEnrollments.individual', 'athleteEnrollments.individual',
                'coachEnrollments.individual', 'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual')
            ->get();

        $action = new GetWaitingListSelectedIndividualsAction;
        $result = $action->execute($pendingEnrollments);

        expect($result)->toHaveCount(1)
            ->and($result[0]['individual_id'])->toBe($individual->id)
            ->and($result[0]['name'])->toBe($individual->name)
            ->and($result[0]['surname'])->toBe($individual->surname)
            ->and($result[0]['role'])->toBe('INDIVIDUAL');
    });

    it('extracts athlete enrollments with discipline data', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'event_id' => $event->id,
            'enrollable_id' => $individual->id,
            'enrollable_type' => Individual::class,
            'activated_at' => null,
        ]);

        $athleteEnrollment = AthleteEnrollment::factory()->create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $event->id,
            'individual_id' => $individual->id,
            'per_person_price' => 10.00,
            'discipline_price' => 5.00,
        ]);

        $pendingEnrollments = Enrollment::where('id', $enrollment->id)
            ->with('individualEnrollments.individual', 'athleteEnrollments.individual',
                'coachEnrollments.individual', 'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual')
            ->get();

        $action = new GetWaitingListSelectedIndividualsAction;
        $result = $action->execute($pendingEnrollments);

        expect($result)->toHaveCount(1)
            ->and($result[0]['role'])->toBe('ATHLETE')
            ->and($result[0]['discipline_id'])->toBe($athleteEnrollment->discipline_id)
            ->and($result[0]['price'])->toBe(10.00)
            ->and($result[0]['discipline_price'])->toBe(5.00);
    });

    it('returns empty array when no enrollments exist', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'event_id' => $event->id,
            'enrollable_id' => $individual->id,
            'enrollable_type' => Individual::class,
            'activated_at' => null,
        ]);

        $pendingEnrollments = Enrollment::where('id', $enrollment->id)
            ->with('individualEnrollments.individual', 'athleteEnrollments.individual',
                'coachEnrollments.individual', 'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual')
            ->get();

        $action = new GetWaitingListSelectedIndividualsAction;
        $result = $action->execute($pendingEnrollments);

        expect($result)->toBeEmpty();
    });
});

// ==========================================================================
// ActivateEnrollmentsAction
// ==========================================================================

describe('ActivateEnrollmentsAction', function () {

    it('activates individual enrollments to PAID status', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        [$enrollment, $individualEnrollment] = createPendingIndividualEnrollment($event, $individual);

        $action = new ActivateEnrollmentsAction;
        $action->execute($enrollment->id);

        $individualEnrollment->refresh();
        expect($individualEnrollment->status_class)->toBe(EvtEnrollmentStatusEnum::PAID->value);
    });

    it('activates athlete enrollments to DISCIPLINE_ASSIGNED status', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'event_id' => $event->id,
            'enrollable_id' => $individual->id,
            'enrollable_type' => Individual::class,
            'activated_at' => null,
        ]);

        $athleteEnrollment = AthleteEnrollment::factory()->create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $event->id,
            'individual_id' => $individual->id,
        ]);

        $action = new ActivateEnrollmentsAction;
        $action->execute($enrollment->id);

        $athleteEnrollment->refresh();
        expect($athleteEnrollment->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED);
    });
});

// ==========================================================================
// WaitingListController HTTP tests
// ==========================================================================

describe('WaitingListController', function () {

    it('shows waiting list page for individual with pending enrollments', function () {
        [$user, $individual] = createIndividualUser();
        $event = createOpenOrganizationEvent();

        createPendingIndividualEnrollment($event, $individual);

        $response = $this->actingAs($user)
            ->get(route('individual.evt-events.events.waiting-list.index', $event));

        $response->assertSuccessful()
            ->assertViewHas('pendingEnrollments')
            ->assertViewHas('event')
            ->assertViewHas('totalCost')
            ->assertViewHas('costBreakdown');
    });

    it('redirects when individual has no pending enrollments', function () {
        [$user, $individual] = createIndividualUser();
        $event = createOpenOrganizationEvent();

        $response = $this->actingAs($user)
            ->get(route('individual.evt-events.events.waiting-list.index', $event));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('confirms enrollments and creates payment document when cost is positive', function () {
        [$user, $individual] = createIndividualUser();
        $event = createOpenOrganizationEvent();

        DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order', 'prefix' => 'ORD']);

        $pricing = Pricing::factory()->create([
            'event_id' => $event->id,
            'price_type' => 'PER_PERSON',
            'price' => 50.00,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'target_group' => 'individual',
            'enrollment_role' => 'INDIVIDUAL',
        ]);

        [$enrollment, $individualEnrollment] = createPendingIndividualEnrollment(
            $event,
            $individual,
            $pricing,
            50.00
        );

        $response = $this->actingAs($user)
            ->post(route('individual.evt-events.events.waiting-list.store', $event));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Enrollment should be marked as activated
        $enrollment->refresh();
        expect($enrollment->activated_at)->not->toBeNull();

        // Document should exist with individual as owner
        $this->assertDatabaseHas('document', [
            'owner_id' => $individual->id,
            'owner_type' => 'individual',
        ]);
    });

    it('activates enrollments directly when cost is zero', function () {
        [$user, $individual] = createIndividualUser();
        $event = createOpenOrganizationEvent();

        [$enrollment, $individualEnrollment] = createPendingIndividualEnrollment($event, $individual);

        $response = $this->actingAs($user)
            ->post(route('individual.evt-events.events.waiting-list.store', $event));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Enrollment should be activated
        $enrollment->refresh();
        expect($enrollment->activated_at)->not->toBeNull();

        // Individual enrollment should be set to PAID
        $individualEnrollment->refresh();
        expect($individualEnrollment->status_class)->toBe(EvtEnrollmentStatusEnum::PAID->value);

        // No document should be created for zero-cost
        $this->assertDatabaseMissing('document', [
            'owner_id' => $individual->id,
        ]);
    });

    it('rejects unauthenticated access', function () {
        $event = createOpenOrganizationEvent();

        $this->post(route('individual.evt-events.events.waiting-list.store', $event))
            ->assertRedirect(route('login'));
    });

    it('rejects access for non-individual users', function () {
        $group = Group::firstOrCreate(['code' => 'ENTITY'], ['name' => 'Entity']);
        $entityUser = \App\Models\User::factory()->create(['group_id' => $group->id]);
        $event = createOpenOrganizationEvent();

        $this->actingAs($entityUser)
            ->post(route('individual.evt-events.events.waiting-list.store', $event))
            ->assertForbidden();
    });

    it('can delete a pending individual enrollment from waiting list', function () {
        [$user, $individual] = createIndividualUser();
        $event = createOpenOrganizationEvent();

        [$enrollment, $individualEnrollment] = createPendingIndividualEnrollment($event, $individual);
        $enrollment->update(['payment_status' => \App\Enums\EvtEventPaymentStatusEnum::PENDING->value]);

        $response = $this->actingAs($user)
            ->delete(route('individual.evt-events.events.waiting-list.destroy', [
                'event' => $event,
                'enrollmentType' => 'individual',
                'id' => $individualEnrollment->id,
            ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('evt_individuals_enrollment', [
            'id' => $individualEnrollment->id,
        ]);
    });
});

// ==========================================================================
// ManualGenerateAthleteEnrollmentPaymentAction - integration test
// ==========================================================================

describe('ManualGenerateAthleteEnrollmentPaymentAction', function () {

    beforeEach(function () {
        DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order', 'prefix' => 'ORD']);
    });

    it('generates a payment document with Individual owner for individual athlete enrollments', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $pricing = Pricing::factory()->create([
            'event_id' => $event->id,
            'price_type' => 'PER_PERSON',
            'price' => 30.00,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'enrollment_role' => 'ATHLETE',
        ]);

        $enrollment = Enrollment::factory()->create([
            'event_id' => $event->id,
            'enrollable_id' => $individual->id,
            'enrollable_type' => Individual::class,
            'activated_at' => null,
            'pricing_id' => $pricing->id,
        ]);

        AthleteEnrollment::factory()->create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $event->id,
            'individual_id' => $individual->id,
            'per_person_price' => 30.00,
            'per_person_pricing_id' => $pricing->id,
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        ]);

        // Resolve the action from the container (has 3 constructor dependencies)
        $action = app(ManualGenerateAthleteEnrollmentPaymentAction::class);
        $document = $action($enrollment);

        expect($document)->not->toBeNull();

        // The critical assertion: document owner must be Individual, not something else
        $this->assertDatabaseHas('document', [
            'id' => $document->id,
            'owner_id' => $individual->id,
            'owner_type' => 'individual',
        ]);
    });

    it('returns null when athlete enrollment costs are zero', function () {
        $event = createOpenOrganizationEvent();
        $individual = Individual::factory()->create();

        $pricing = Pricing::factory()->create([
            'event_id' => $event->id,
            'price_type' => 'PER_PERSON',
            'price' => 0,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'enrollment_role' => 'ATHLETE',
        ]);

        $enrollment = Enrollment::factory()->create([
            'event_id' => $event->id,
            'enrollable_id' => $individual->id,
            'enrollable_type' => Individual::class,
            'activated_at' => null,
            'pricing_id' => $pricing->id,
        ]);

        AthleteEnrollment::factory()->create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $event->id,
            'individual_id' => $individual->id,
            'per_person_price' => 0,
            'per_person_pricing_id' => $pricing->id,
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        ]);

        $action = app(ManualGenerateAthleteEnrollmentPaymentAction::class);
        $document = $action($enrollment);

        expect($document)->toBeNull();
    });
});
