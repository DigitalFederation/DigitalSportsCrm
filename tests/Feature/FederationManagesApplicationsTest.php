<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationComment;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $this->federation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    $this->fedUser = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->fedUser->federations()->attach($this->federation->id);

    $this->application = EventApplication::factory()->submitted()->create();
});

it('allows federation user to view the management index', function () {
    actingAs($this->fedUser)
        ->get(route('federation.event-applications.index'))
        ->assertSuccessful();
});

it('allows federation user to view an application in management view', function () {
    actingAs($this->fedUser)
        ->get(route('federation.event-applications.show', $this->application))
        ->assertSuccessful();
});

it('allows federation user to approve a submitted application', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.approve', $this->application), [
            'notes' => 'Looks good, approved.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->application->refresh();
    expect($this->application->status_class)->toBe(ApprovedApplicationState::class);
});

it('allows federation user to reject a submitted application', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.reject', $this->application), [
            'notes' => 'Missing required documentation.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->application->refresh();
    expect($this->application->status_class)->toBe(RejectedApplicationState::class);
});

it('allows federation user to return application for correction', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.return', $this->application), [
            'notes' => 'Please fix the budget section.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->application->refresh();
    expect($this->application->status_class)->toBe(ReturnedForCorrectionApplicationState::class);
});

it('requires notes when rejecting an application', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.reject', $this->application), [
            'notes' => '',
        ])
        ->assertSessionHasErrors('notes');
});

it('requires notes when returning for correction', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.return', $this->application), [
            'notes' => '',
        ])
        ->assertSessionHasErrors('notes');
});

it('allows federation user to add a comment', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.comment', $this->application), [
            'comment' => 'This is a test comment.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ApplicationComment::where('application_id', $this->application->id)->count())->toBe(1);
});

it('allows federation user to add a section comment', function () {
    actingAs($this->fedUser)
        ->post(route('federation.event-applications.comment', $this->application), [
            'comment' => 'Budget looks incomplete.',
            'section' => 'budget',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $comment = ApplicationComment::where('application_id', $this->application->id)->first();
    expect($comment->section)->toBe('budget');
});

it('allows federation user to delete a comment', function () {
    $comment = ApplicationComment::create([
        'application_id' => $this->application->id,
        'user_id' => $this->fedUser->id,
        'comment' => 'To be deleted',
        'section' => null,
        'is_internal' => false,
    ]);

    actingAs($this->fedUser)
        ->delete(route('federation.event-applications.comment.delete', [
            'application' => $this->application->id,
            'comment' => $comment->id,
        ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(ApplicationComment::find($comment->id))->toBeNull();
});

it('allows federation user to export pdf', function () {
    actingAs($this->fedUser)
        ->get(route('federation.event-applications.pdf', $this->application))
        ->assertSuccessful();
});
