<?php

use App\Enums\EventApplicationTypeEnum;
use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);
    $this->admin->assignRole('admin');

    $this->entity = Entity::factory()->create();

    $this->application = EventApplication::factory()->create([
        'entity_id' => $this->entity->id,
        'entity_type' => Entity::class,
        'status_class' => SubmittedApplicationState::class,
        'event_name' => 'Test Championship',
        'event_type' => 'competition',
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
    ]);
});

test('admin can move a submitted candidatura to in-validation state', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.event-applications.validate', $this->application), [
            'notes' => 'Moving to validation.',
        ])
        ->assertRedirect();

    $this->application->refresh();

    expect($this->application->status_class)->toBe(InValidationApplicationState::class);
});

test('admin cannot validate a candidatura that is not in submitted state', function () {
    $this->application->update(['status_class' => InValidationApplicationState::class]);

    $this->actingAs($this->admin)
        ->post(route('admin.event-applications.validate', $this->application), [
            'notes' => 'Trying to re-validate.',
        ])
        ->assertRedirect();

    // State should not have changed (validate action guards against invalid state)
    $this->application->refresh();
    expect($this->application->status_class)->toBe(InValidationApplicationState::class);
});

test('submitted state only allows transition to in-validation', function () {
    $application = EventApplication::factory()->create([
        'entity_id' => $this->entity->id,
        'entity_type' => Entity::class,
        'status_class' => SubmittedApplicationState::class,
    ]);

    $state = $application->state;

    expect($state->canTransitionTo(InValidationApplicationState::class))->toBeTrue()
        ->and($state->canTransitionTo(\Domain\EventApplications\States\ApprovedApplicationState::class))->toBeFalse()
        ->and($state->canTransitionTo(\Domain\EventApplications\States\RejectedApplicationState::class))->toBeFalse();
});
