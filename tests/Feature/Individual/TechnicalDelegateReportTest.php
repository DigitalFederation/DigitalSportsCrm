<?php

use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\Organizer;
use Domain\EvtEvents\Models\TechnicalDelegateReport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=UserGroupSeeder');

    $individualGroup = Group::where('code', 'INDIVIDUAL')->first();

    $this->federation = Federation::factory()->create();
    $this->user = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');

    $this->event = Event::factory()->create();

    Organizer::create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->federation->id,
        'organizable_type' => Federation::class,
    ]);

    // Assign Technical Delegate role
    EventRole::create([
        'event_id' => $this->event->id,
        'individual_id' => $this->individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);
});

test('technical delegate can view report page', function () {
    $this->actingAs($this->user)
        ->get(route('individual.technical-delegate.td-report', $this->event))
        ->assertStatus(200);
});

test('non-technical delegate cannot view report page', function () {
    $individualGroup = Group::where('code', 'INDIVIDUAL')->first();
    $otherUser = User::factory()->create(['group_id' => $individualGroup->id]);
    $otherIndividual = Individual::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $otherIndividual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');

    $this->actingAs($otherUser)
        ->get(route('individual.technical-delegate.td-report', $this->event))
        ->assertStatus(403);
});

test('technical delegate can save report draft', function () {
    $reportData = [
        'participants_withdrawals' => 'Test participants data',
        'incidents_occurrences' => 'Test incidents',
        'officials_performance' => 'Test performance',
        'facilities_evaluation' => 'Test facilities',
        'safety_first_aid' => 'Test safety',
        'anti_doping_control' => 'Test anti-doping',
        'sports_protests' => 'Test protests',
        'observations_recommendations' => 'Test observations',
    ];

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.td-report.save', $this->event), $reportData)
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('evt_technical_delegate_reports', [
        'event_id' => $this->event->id,
        'participants_withdrawals' => 'Test participants data',
        'is_submitted' => false,
    ]);
});

test('technical delegate can submit report', function () {
    // First create a draft report
    TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'participants_withdrawals' => 'Test data',
        'is_submitted' => false,
    ]);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.td-report.submit', $this->event))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('evt_technical_delegate_reports', [
        'event_id' => $this->event->id,
        'is_submitted' => true,
    ]);
});

test('cannot submit already submitted report', function () {
    TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'participants_withdrawals' => 'Test data',
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.td-report.submit', $this->event))
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('cannot modify submitted report', function () {
    TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'participants_withdrawals' => 'Original data',
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.td-report.save', $this->event), [
            'participants_withdrawals' => 'Modified data',
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertDatabaseHas('evt_technical_delegate_reports', [
        'event_id' => $this->event->id,
        'participants_withdrawals' => 'Original data',
    ]);
});

test('technical delegate can upload document to report', function () {
    Storage::fake('local');

    $report = TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => false,
    ]);

    $file = UploadedFile::fake()->create('test-document.pdf', 100);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.td-report.upload', $this->event), [
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

    TechnicalDelegateReport::create([
        'event_id' => $this->event->id,
        'submitted_by' => $this->individual->id,
        'is_submitted' => true,
        'submitted_at' => now(),
    ]);

    $file = UploadedFile::fake()->create('test-document.pdf', 100);

    $this->actingAs($this->user)
        ->post(route('individual.technical-delegate.td-report.upload', $this->event), [
            'document' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});
