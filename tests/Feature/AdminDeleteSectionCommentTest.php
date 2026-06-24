<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationComment;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;

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

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->adminUser->assignRole('admin');
});

it('allows admin to delete a section comment', function () {
    $application = EventApplication::factory()->create();

    $comment = ApplicationComment::create([
        'application_id' => $application->id,
        'user_id' => $this->adminUser->id,
        'comment' => 'Test section comment',
        'section' => 'event_information',
        'is_internal' => false,
    ]);

    actingAs($this->adminUser)
        ->delete(route('admin.event-applications.comment.delete', [
            'application' => $application->id,
            'comment' => $comment->id,
        ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    assertDatabaseMissing('application_comments', [
        'id' => $comment->id,
    ]);
});

it('returns 404 when deleting a comment that does not belong to the application', function () {
    $application = EventApplication::factory()->create();
    $otherApplication = EventApplication::factory()->create();

    $comment = ApplicationComment::create([
        'application_id' => $otherApplication->id,
        'user_id' => $this->adminUser->id,
        'comment' => 'Comment on other application',
        'section' => 'event_information',
        'is_internal' => false,
    ]);

    actingAs($this->adminUser)
        ->delete(route('admin.event-applications.comment.delete', [
            'application' => $application->id,
            'comment' => $comment->id,
        ]))
        ->assertNotFound();
});
