<?php

use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create base test data
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create();
    $this->individual = Individual::factory()->create();

    // Create base enrollment
    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('creates team official enrollment successfully', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]);

    expect($teamOfficialEnrollment)->toBeInstanceOf(TeamOfficialEnrollment::class)
        ->and($teamOfficialEnrollment->federation_id)->toBe($this->federation->id)
        ->and($teamOfficialEnrollment->individual_id)->toBe($this->individual->id)
        ->and($teamOfficialEnrollment->event_id)->toBe($this->event->id)
        ->and($teamOfficialEnrollment->status_class)->toBe(RegisteredTeamOfficialEnrollmentState::class);
});

it('requires either federation_id or entity_id', function () {
    expect(fn () => TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]))->toThrow(\InvalidArgumentException::class, 'TeamOfficialEnrollment must have either a federation_id or entity_id');
});

it('loads relationships correctly', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]);

    $loaded = TeamOfficialEnrollment::with(['federation', 'individual', 'event', 'enrollment'])
        ->find($teamOfficialEnrollment->id);

    expect($loaded->federation)->toBeInstanceOf(Federation::class)
        ->and($loaded->individual)->toBeInstanceOf(Individual::class)
        ->and($loaded->event)->toBeInstanceOf(Event::class)
        ->and($loaded->enrollment)->toBeInstanceOf(Enrollment::class);
});

it('transitions state from active to canceled', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]);

    $teamOfficialEnrollment->cancel();

    expect($teamOfficialEnrollment->fresh()->status_class)
        ->toBe(CanceledTeamOfficialEnrollmentState::class);
});

it('transitions state from canceled to active', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => CanceledTeamOfficialEnrollmentState::class,
    ]);

    $teamOfficialEnrollment->activate();

    expect($teamOfficialEnrollment->fresh()->status_class)
        ->toBe(RegisteredTeamOfficialEnrollmentState::class);
});

it('handles attributes correctly', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]);

    $attribute = \Domain\EvtEvents\Models\Attribute::factory()->create();

    $teamOfficialEnrollment->attributes()->create([
        'attribute_id' => $attribute->id,
        'value' => 'test_value',
    ]);

    $loaded = TeamOfficialEnrollment::with('attributes.attribute')
        ->find($teamOfficialEnrollment->id);

    expect($loaded->attributes)->toHaveCount(1)
        ->and($loaded->attributes->first()->value)->toBe('test_value')
        ->and($loaded->attributes->first()->attribute->id)->toBe($attribute->id);
});

it('deletes official attributes before deleting the enrollment', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]);

    $attribute = \Domain\EvtEvents\Models\Attribute::factory()->create();

    $teamOfficialEnrollment->attributes()->create([
        'attribute_id' => $attribute->id,
        'value' => 'test_value',
    ]);

    $teamOfficialEnrollment->delete();

    $this->assertDatabaseMissing('evt_officials_enrollment', [
        'id' => $teamOfficialEnrollment->id,
    ]);
    $this->assertDatabaseMissing('evt_officials_attributes', [
        'officials_enrollment_id' => $teamOfficialEnrollment->id,
    ]);
});

it('returns correct state name and color', function () {
    $teamOfficialEnrollment = TeamOfficialEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => RegisteredTeamOfficialEnrollmentState::class,
    ]);

    expect($teamOfficialEnrollment->stateName())->toBeString()
        ->and($teamOfficialEnrollment->stateColor())->toBeString();
});
