<?php

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventReportDocument;
use Domain\EvtEvents\Models\TechnicalDelegateReport;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->individual = Individual::factory()->create();
    $this->event = Event::factory()->create();
});

it('can create a technical delegate report', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'participants_withdrawals' => 'Test participants data',
        'is_submitted' => false,
    ]);

    expect($report)->toBeInstanceOf(TechnicalDelegateReport::class)
        ->and($report->event_id)->toBe($this->event->id)
        ->and($report->submitted_by)->toBe($this->individual->id)
        ->and($report->is_submitted)->toBeFalse();
});

it('belongs to an event', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
    ]);

    expect($report->event)->toBeInstanceOf(Event::class)
        ->and($report->event->id)->toBe($this->event->id);
});

it('belongs to an individual who submitted it', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
    ]);

    expect($report->submittedBy)->toBeInstanceOf(Individual::class)
        ->and($report->submittedBy->id)->toBe($this->individual->id);
});

it('can be submitted', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => false,
    ]);

    expect($report->is_submitted)->toBeFalse();

    $report->submit();

    expect($report->fresh()->is_submitted)->toBeTrue()
        ->and($report->fresh()->submitted_at)->not->toBeNull();
});

it('is editable when not submitted', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => false,
    ]);

    expect($report->isEditable())->toBeTrue();
});

it('is not editable when submitted', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    expect($report->isEditable())->toBeFalse();
});

it('can have multiple documents attached', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
    ]);

    EventReportDocument::create([
        'documentable_type' => TechnicalDelegateReport::class,
        'documentable_id' => $report->id,
        'file_name' => 'document1.pdf',
        'file_path' => 'reports/document1.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'uploaded_by' => $this->individual->id,
    ]);

    EventReportDocument::create([
        'documentable_type' => TechnicalDelegateReport::class,
        'documentable_id' => $report->id,
        'file_name' => 'document2.pdf',
        'file_path' => 'reports/document2.pdf',
        'file_size' => 2048,
        'mime_type' => 'application/pdf',
        'uploaded_by' => $this->individual->id,
    ]);

    expect($report->documents)->toHaveCount(2);
});

it('casts is_submitted to boolean', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => 1,
    ]);

    expect($report->is_submitted)->toBeBool()->toBeTrue();
});

it('casts submitted_at to datetime', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    expect($report->submitted_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

it('has all 8 text fields fillable', function () {
    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'participants_withdrawals' => 'Participants data',
        'incidents_occurrences' => 'Incidents data',
        'officials_performance' => 'Officials data',
        'facilities_evaluation' => 'Facilities data',
        'safety_first_aid' => 'Safety data',
        'anti_doping_control' => 'Anti-doping data',
        'sports_protests' => 'Protests data',
        'observations_recommendations' => 'Observations data',
    ]);

    expect($report->participants_withdrawals)->toBe('Participants data')
        ->and($report->incidents_occurrences)->toBe('Incidents data')
        ->and($report->officials_performance)->toBe('Officials data')
        ->and($report->facilities_evaluation)->toBe('Facilities data')
        ->and($report->safety_first_aid)->toBe('Safety data')
        ->and($report->anti_doping_control)->toBe('Anti-doping data')
        ->and($report->sports_protests)->toBe('Protests data')
        ->and($report->observations_recommendations)->toBe('Observations data');
});
