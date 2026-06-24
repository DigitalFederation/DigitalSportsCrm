<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Events\ActivateAfterPayment;
use App\Listeners\ActivateAfterPaymentEnrollmentListener;
use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PendingDocumentState;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();

    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->user = User::factory()->create(['group_id' => $group->id]);
    $this->event = Event::factory()->create([
        'event_category' => 'organization',
        'enrollment_type' => 'only_individuals',
    ]);

    $this->pricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'PER_PERSON',
        'target_group' => 'individual',
        'price' => 125.00,
    ]);
    $this->federation = Federation::factory()->create();
    $this->individual = Individual::factory()->create(['user_id' => $this->user->id]);
    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);
    $this->athleteEnrollment = AthleteEnrollment::factory()
        ->forEvent($this->event)
        ->forEnrollment($this->enrollment)
        ->create(['individual_id' => $this->individual->id]);

    $this->documentType = DocumentType::factory()->create(['code' => 'ORD']);
    $this->document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
        'owner_id' => $this->federation->id,
        'owner_type' => Federation::class,
    ]);

    $this->documentDetail = DocumentDetail::factory()->create([
        'document_id' => $this->document->id,
        'owner_id' => $this->enrollment->id,
        'owner_type' => Enrollment::class,
        'unit_value' => 100.00,
        'quantity' => 1,
    ]);
});

test('activates the enrollment and related enrollments after payment', function () {
    $event = new ActivateAfterPayment($this->document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);
    $listener->handle($event);

    $this->enrollment->refresh();
    expect($this->enrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value);

    $this->athleteEnrollment->refresh();
    expect($this->athleteEnrollment->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED);
});

test('activates multiple athlete enrollments for same enrollment', function () {
    $individual2 = Individual::factory()->create();
    $athleteEnrollment2 = AthleteEnrollment::factory()
        ->forEvent($this->event)
        ->forEnrollment($this->enrollment)
        ->create(['individual_id' => $individual2->id]);

    $event = new ActivateAfterPayment($this->document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);
    $listener->handle($event);

    $this->athleteEnrollment->refresh();
    $athleteEnrollment2->refresh();

    expect($this->athleteEnrollment->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED);
    expect($athleteEnrollment2->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED);
});

test('activates coach enrollments after payment', function () {
    $coachIndividual = Individual::factory()->create();
    $coachEnrollment = CoachEnrollment::factory()
        ->forEvent($this->event)
        ->create([
            'individual_id' => $coachIndividual->id,
            'enrollment_id' => $this->enrollment->id,
            'status_class' => PendingCoachEnrollmentState::class,
        ]);

    $event = new ActivateAfterPayment($this->document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);
    $listener->handle($event);

    $coachEnrollment->refresh();
    expect($coachEnrollment->status_class)->toBe(RegisteredCoachEnrollmentState::class);
});

test('activates individual enrollments after payment', function () {
    $individualEnrollment = IndividualEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'status_class' => EvtEnrollmentStatusEnum::PRE_ENROLLED->value,
    ]);

    $event = new ActivateAfterPayment($this->document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);
    $listener->handle($event);

    $individualEnrollment->refresh();
    expect($individualEnrollment->status_class)->toBe(EvtEnrollmentStatusEnum::PAID->value);
});

test('notifies user when enrollment is activated', function () {
    $event = new ActivateAfterPayment($this->document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);
    $listener->handle($event);

    Notification::assertSentTo($this->user, \App\Notifications\UserAlert::class);
});

test('handles enrollment with different user gracefully', function () {
    $anotherUser = User::factory()->create(['group_id' => Group::where('code', 'INDIVIDUAL')->first()->id]);

    $enrollmentWithAnotherUser = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $anotherUser->id,
    ]);

    $athleteEnrollmentForNewEnrollment = AthleteEnrollment::factory()
        ->forEvent($this->event)
        ->forEnrollment($enrollmentWithAnotherUser)
        ->create();

    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 50.00,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $enrollmentWithAnotherUser->id,
        'owner_type' => Enrollment::class,
    ]);

    $event = new ActivateAfterPayment($document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);

    expect(fn () => $listener->handle($event))->not->toThrow(\Exception::class);

    $enrollmentWithAnotherUser->refresh();
    expect($enrollmentWithAnotherUser->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value);

    Notification::assertSentTo($anotherUser, \App\Notifications\UserAlert::class);
});

test('does not process non-enrollment document details', function () {
    $document = Document::factory()->create([
        'type_id' => $this->documentType->id,
        'status_class' => PendingDocumentState::class,
        'total_value' => 100.00,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => null,
        'owner_type' => null,
        'description' => 'Generic service',
    ]);

    $event = new ActivateAfterPayment($document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);

    expect(fn () => $listener->handle($event))->not->toThrow(\Exception::class);
});

test('handles multiple enrollments in same document', function () {
    $enrollment2 = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $athleteEnrollment2 = AthleteEnrollment::factory()
        ->forEvent($this->event)
        ->forEnrollment($enrollment2)
        ->create(['individual_id' => $this->individual->id]);

    DocumentDetail::factory()->create([
        'document_id' => $this->document->id,
        'owner_id' => $enrollment2->id,
        'owner_type' => Enrollment::class,
        'unit_value' => 50.00,
    ]);

    $event = new ActivateAfterPayment($this->document->id);
    $listener = app(ActivateAfterPaymentEnrollmentListener::class);
    $listener->handle($event);

    $this->enrollment->refresh();
    $enrollment2->refresh();

    expect($this->enrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value);
    expect($enrollment2->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value);
});
