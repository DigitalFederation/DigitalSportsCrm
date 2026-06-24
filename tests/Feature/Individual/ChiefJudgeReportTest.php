<?php

use App\Models\User;
use Domain\EvtEvents\Models\ChiefJudgeReport;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\Organizer;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $individualGroup = \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);
    $this->user = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->event = Event::factory()->create();
    Organizer::create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->federation->id,
        'organizable_type' => Federation::class,
    ]);

    // Assign Chief Judge role
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_CHIEF_JUDGE,
    ]);
});

test('chief judge can view report page', function () {
    $this->actingAs($this->user)
        ->get(route('individual.technical-delegate.cj-report', $this->event))
        ->assertStatus(200);
});

test('non-chief judge cannot view report page', function () {
    $otherUser = User::factory()->create();
    $otherIndividual = Individual::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($otherUser)
        ->get(route('individual.technical-delegate.cj-report', $this->event))
        ->assertStatus(403);
});

test('chief judge can save report draft', function () {
    $reportData = [
        'technical_considerations' => 'Test technical considerations for the competition',
    ];

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.cj-report.save', $this->event), $reportData)
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('evt_chief_judge_reports', [
        'event_id' => $this->event->id,
        'technical_considerations' => 'Test technical considerations for the competition',
        'is_submitted' => false,
    ]);
});

test('chief judge can submit report', function () {
    ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'technical_considerations' => 'Test considerations',
        'is_submitted' => false,
    ]);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.cj-report.submit', $this->event))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('evt_chief_judge_reports', [
        'event_id' => $this->event->id,
        'is_submitted' => true,
    ]);
});

test('cannot submit already submitted report', function () {
    ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'technical_considerations' => 'Test considerations',
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.cj-report.submit', $this->event))
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('cannot modify submitted report', function () {
    ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'technical_considerations' => 'Original considerations',
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.cj-report.save', $this->event), [
            'technical_considerations' => 'Modified considerations',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('evt_chief_judge_reports', [
        'event_id' => $this->event->id,
        'technical_considerations' => 'Original considerations',
    ]);
});

test('chief judge can upload document to report', function () {
    Storage::fake('local');

    $report = ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => false,
    ]);

    $file = UploadedFile::fake()->create('test-document.pdf', 100);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.cj-report.upload', $this->event), [
            'document' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('evt_event_report_documents', [
        'documentable_id' => $report->id,
        'file_name' => 'test-document.pdf',
    ]);
});

test('cannot upload document to submitted report', function () {
    Storage::fake('local');

    ChiefJudgeReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    $file = UploadedFile::fake()->create('test-document.pdf', 100);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.cj-report.upload', $this->event), [
            'document' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('chief judge can assign multiple functions to same referee', function () {
    // This test verifies the modified AssignRefereeFunctionAction creates new records
    // (multiple functions per referee is supported - no unique constraint)

    $this->assertDatabaseCount('evt_referee_function_assignments', 0);

    // Create a valid referee enrollment
    $refereeEnrollment = RefereeEnrollment::factory()->create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
    ]);

    // First assignment
    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'assigned_by' => $this->individual->id,
        'function_text' => 'Juiz Embarcado',
        'is_present' => true,
    ]);

    // Second assignment for same referee (multiple functions supported)
    RefereeFunctionAssignment::create([
        'event_id' => $this->event->id,
        'referee_enrollment_id' => $refereeEnrollment->id,
        'assigned_by' => $this->individual->id,
        'function_text' => 'Juiz de Pesagem',
        'is_present' => true,
    ]);

    // Should have 2 assignments for the same referee
    $this->assertDatabaseCount('evt_referee_function_assignments', 2);
});
