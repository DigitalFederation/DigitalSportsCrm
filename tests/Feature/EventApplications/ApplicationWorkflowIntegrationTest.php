<?php

use App\Enums\EventApplicationTypeEnum;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\ApproveApplicationAction;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Actions\PublishApplicationAction;
use Domain\EventApplications\Actions\RejectApplicationAction;
use Domain\EventApplications\Actions\ReturnForCorrectionAction;
use Domain\EventApplications\Actions\SubmitApplicationAction;
use Domain\EventApplications\Actions\ValidateApplicationAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\PublishedApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('complete federation initiated workflow from template to published', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'event_name' => 'Championship Event',
        'event_type' => 'competition',
    ]);

    expect($application->status_class)->toBe(DraftApplicationState::class);

    $submitAction = new SubmitApplicationAction;
    $application = $submitAction->execute($application, $user->id);

    expect($application->status_class)->toBe(SubmittedApplicationState::class)
        ->and($application->submitted_at)->not->toBeNull();

    $validateAction = new ValidateApplicationAction;
    $application = $validateAction->execute($application, $admin->id);

    expect($application->status_class)->toBe(InValidationApplicationState::class)
        ->and($application->validated_at)->not->toBeNull();

    $approveAction = new ApproveApplicationAction;
    $application = $approveAction->execute($application, $admin->id, 'Meets all criteria');

    expect($application->status_class)->toBe(ApprovedApplicationState::class)
        ->and($application->decided_at)->not->toBeNull();

    $publishAction = new PublishApplicationAction;
    $application = $publishAction->execute($application, $admin->id, 999);

    expect($application->status_class)->toBe(PublishedApplicationState::class)
        ->and($application->published_at)->not->toBeNull()
        ->and($application->published_event_id)->toBe(999);

    expect(ApplicationStateHistory::where('application_id', $application->id)->count())->toBe(4);
});

test('complete direct submission workflow', function () {
    $entity = Entity::factory()->create();
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'event_name' => 'Direct Event',
        'event_type' => 'competition',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(32),
    ]);

    expect($application->status_class)->toBe(DraftApplicationState::class)
        ->and($application->template_id)->toBeNull();

    $submitAction = new SubmitApplicationAction;
    $application = $submitAction->execute($application, $user->id);

    $validateAction = new ValidateApplicationAction;
    $application = $validateAction->execute($application, $admin->id);

    $approveAction = new ApproveApplicationAction;
    $application = $approveAction->execute($application, $admin->id);

    expect($application->status_class)->toBe(ApprovedApplicationState::class);
});

test('return for correction workflow allows resubmission', function () {
    $entity = Entity::factory()->create();
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $application = EventApplication::factory()->inValidation()->create([
        'entity_id' => $entity->id,
    ]);

    $returnAction = new ReturnForCorrectionAction;
    $application = $returnAction->execute($application, $admin->id, 'Missing documents');

    expect($application->status_class)->toBe(ReturnedForCorrectionApplicationState::class)
        ->and($application->admin_notes)->toBe('Missing documents');

    expect($application->state->canEdit())->toBeTrue()
        ->and($application->state->canSubmit())->toBeTrue();

    $submitAction = new SubmitApplicationAction;
    $application = $submitAction->execute($application, $user->id);

    expect($application->status_class)->toBe(SubmittedApplicationState::class);

    $history = ApplicationStateHistory::where('application_id', $application->id)->get();
    expect($history)->toHaveCount(2);
});

test('rejection workflow terminates application', function () {
    $entity = Entity::factory()->create();
    $admin = User::factory()->create();

    $application = EventApplication::factory()->inValidation()->create([
        'entity_id' => $entity->id,
    ]);

    $rejectAction = new RejectApplicationAction;
    $application = $rejectAction->execute($application, $admin->id, 'Does not meet requirements');

    expect($application->status_class)->toBe(RejectedApplicationState::class)
        ->and($application->admin_notes)->toBe('Does not meet requirements')
        ->and($application->decided_at)->not->toBeNull();

    expect($application->state->canEdit())->toBeFalse()
        ->and($application->state->canSubmit())->toBeFalse();

    $reflection = new ReflectionClass($application->state);
    $method = $reflection->getMethod('allowedTransitions');
    $method->setAccessible(true);
    $transitions = $method->invoke($application->state);

    expect($transitions)->toBeEmpty();
});

test('duplicate prevention throughout workflow', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();
    $user = User::factory()->create();

    $createAction = new CreateApplicationAction;
    $app1 = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'event_name' => 'First Application',
        'event_type' => 'competition',
    ]);

    $submitAction = new SubmitApplicationAction;
    $submitAction->execute($app1, $user->id);

    expect($template->hasApplied($entity->id))->toBeTrue();

    expect(EventApplication::where('entity_id', $entity->id)
        ->where('template_id', $template->id)
        ->whereNull('deleted_at')
        ->count())->toBe(1);
});

test('state transitions are properly recorded in history', function () {
    $entity = Entity::factory()->create();
    $user = User::factory()->create();
    $admin = User::factory()->create();

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $entity->id,
    ]);

    $submitAction = new SubmitApplicationAction;
    $submitAction->execute($application, $user->id);

    $validateAction = new ValidateApplicationAction;
    $validateAction->execute($application->fresh(), $admin->id, 'Reviewing application');

    $approveAction = new ApproveApplicationAction;
    $approveAction->execute($application->fresh(), $admin->id, 'Approved');

    $history = ApplicationStateHistory::where('application_id', $application->id)
        ->orderBy('created_at')
        ->get();

    expect($history)->toHaveCount(3);

    expect($history[0]->from_state)->toBe(DraftApplicationState::class)
        ->and($history[0]->to_state)->toBe(SubmittedApplicationState::class);

    expect($history[1]->from_state)->toBe(SubmittedApplicationState::class)
        ->and($history[1]->to_state)->toBe(InValidationApplicationState::class);

    expect($history[2]->from_state)->toBe(InValidationApplicationState::class)
        ->and($history[2]->to_state)->toBe(ApprovedApplicationState::class);
});

test('multiple entities can progress through workflow independently', function () {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();
    $user = User::factory()->create();

    $createAction = new CreateApplicationAction;

    $app1 = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $entity1->id,
        'entity_type' => Entity::class,
        'event_name' => 'Entity 1 Event',
        'event_type' => 'competition',
    ]);

    $app2 = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $entity2->id,
        'entity_type' => Entity::class,
        'event_name' => 'Entity 2 Event',
        'event_type' => 'competition',
    ]);

    $submitAction = new SubmitApplicationAction;
    $submitAction->execute($app1, $user->id);

    expect($app1->fresh()->status_class)->toBe(SubmittedApplicationState::class)
        ->and($app2->fresh()->status_class)->toBe(DraftApplicationState::class);
});
