<?php

use Domain\EvtEvents\Models\ChiefJudgeReport;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventReportDocument;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->individual = Individual::factory()->create();
    $this->event = Event::factory()->create();
});

it('can create a chief judge report', function () {
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'technical_considerations' => 'Test technical considerations',
        'is_submitted' => false,
    ]);

    expect($report)->toBeInstanceOf(ChiefJudgeReport::class)
        ->and($report->event_id)->toBe($this->event->id)
        ->and($report->submitted_by)->toBe($this->individual->id)
        ->and($report->is_submitted)->toBeFalse();
});

it('belongs to an event', function () {
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
    ]);

    expect($report->event)->toBeInstanceOf(Event::class)
        ->and($report->event->id)->toBe($this->event->id);
});

it('belongs to an individual who submitted it', function () {
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
    ]);

    expect($report->submittedBy)->toBeInstanceOf(Individual::class)
        ->and($report->submittedBy->id)->toBe($this->individual->id);
});

it('can be submitted', function () {
    $report = ChiefJudgeReport::create([
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
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => false,
    ]);

    expect($report->isEditable())->toBeTrue();
});

it('is not editable when submitted', function () {
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    expect($report->isEditable())->toBeFalse();
});

it('can have multiple documents attached', function () {
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
    ]);

    EventReportDocument::create([
        'documentable_type' => ChiefJudgeReport::class,
        'documentable_id' => $report->id,
        'file_name' => 'document1.pdf',
        'file_path' => 'reports/document1.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'uploaded_by' => $this->individual->id,
    ]);

    EventReportDocument::create([
        'documentable_type' => ChiefJudgeReport::class,
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
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => 1,
    ]);

    expect($report->is_submitted)->toBeBool()->toBeTrue();
});

it('casts submitted_at to datetime', function () {
    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    expect($report->submitted_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

it('stores technical considerations text', function () {
    $considerations = 'These are the technical considerations for the competition. They include observations about the officiating, equipment, and venue.';

    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'technical_considerations' => $considerations,
    ]);

    expect($report->technical_considerations)->toBe($considerations);
});
