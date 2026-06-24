<?php

use App\Models\User;
use Domain\EventApplications\Actions\CreateApplicationTemplateAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates template with correct data', function () {
    $user = User::factory()->create();
    $sport = Sport::factory()->create();

    $action = new CreateApplicationTemplateAction;
    $template = $action->execute([
        'name' => 'Summer Championship 2025',
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'submission_start_date' => now()->addDays(5),
        'submission_end_date' => now()->addDays(30),
        'event_start_date' => now()->addDays(60),
        'event_end_date' => now()->addDays(62),
        'description' => 'Annual summer championship',
        'is_active' => true,
        'max_applications' => 50,
        'created_by' => $user->id,
    ]);

    expect($template)->toBeInstanceOf(ApplicationTemplate::class)
        ->and($template->name)->toBe('Summer Championship 2025')
        ->and($template->event_type)->toBe('competition')
        ->and($template->sport_id)->toBe($sport->id)
        ->and($template->created_by)->toBe($user->id);
});

test('sets is active to true by default', function () {
    $user = User::factory()->create();

    $action = new CreateApplicationTemplateAction;
    $template = $action->execute([
        'name' => 'Test Template',
        'event_type' => 'competition',
        'submission_start_date' => now()->addDays(5),
        'submission_end_date' => now()->addDays(30),
        'created_by' => $user->id,
    ]);

    expect($template->is_active)->toBeTrue();
});

test('handles optional fields', function () {
    $user = User::factory()->create();

    $action = new CreateApplicationTemplateAction;
    $template = $action->execute([
        'name' => 'Minimal Template',
        'event_type' => 'organization',
        'submission_start_date' => now()->addDays(5),
        'submission_end_date' => now()->addDays(30),
        'created_by' => $user->id,
    ]);

    expect($template->sport_id)->toBeNull()
        ->and($template->event_category_id)->toBeNull()
        ->and($template->description)->toBeNull()
        ->and($template->max_applications)->toBeNull();
});

test('can set max applications', function () {
    $user = User::factory()->create();

    $action = new CreateApplicationTemplateAction;
    $template = $action->execute([
        'name' => 'Limited Template',
        'event_type' => 'competition',
        'submission_start_date' => now()->addDays(5),
        'submission_end_date' => now()->addDays(30),
        'max_applications' => 25,
        'created_by' => $user->id,
    ]);

    expect($template->max_applications)->toBe(25);
});

test('can create inactive template', function () {
    $user = User::factory()->create();

    $action = new CreateApplicationTemplateAction;
    $template = $action->execute([
        'name' => 'Inactive Template',
        'event_type' => 'organization',
        'submission_start_date' => now()->addDays(5),
        'submission_end_date' => now()->addDays(30),
        'is_active' => false,
        'created_by' => $user->id,
    ]);

    expect($template->is_active)->toBeFalse();
});

test('persists template to database', function () {
    $user = User::factory()->create();

    $action = new CreateApplicationTemplateAction;
    $template = $action->execute([
        'name' => 'Test Template',
        'event_type' => 'competition',
        'submission_start_date' => now()->addDays(5),
        'submission_end_date' => now()->addDays(30),
        'created_by' => $user->id,
    ]);

    expect(ApplicationTemplate::count())->toBe(1);
    $this->assertDatabaseHas('application_templates', [
        'id' => $template->id,
        'name' => 'Test Template',
    ]);
});
