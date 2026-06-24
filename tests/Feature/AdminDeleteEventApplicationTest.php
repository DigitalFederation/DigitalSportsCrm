<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationComment;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

it('allows admin to force-delete an application', function () {
    $application = EventApplication::factory()->create();

    actingAs($this->adminUser)
        ->delete(route('admin.event-applications.destroy', $application))
        ->assertRedirect(route('admin.event-applications.index'))
        ->assertSessionHas('success');

    assertDatabaseMissing('event_applications', [
        'id' => $application->id,
    ]);
});

it('cascade-deletes related comments and state history', function () {
    $application = EventApplication::factory()->create();

    $comment = ApplicationComment::create([
        'application_id' => $application->id,
        'user_id' => $this->adminUser->id,
        'comment' => 'Test comment',
        'section' => null,
        'is_internal' => false,
    ]);

    $history = ApplicationStateHistory::create([
        'application_id' => $application->id,
        'from_state' => DraftApplicationState::class,
        'to_state' => SubmittedApplicationState::class,
        'changed_by' => (string) $this->adminUser->id,
        'notes' => 'Submitted',
    ]);

    actingAs($this->adminUser)
        ->delete(route('admin.event-applications.destroy', $application))
        ->assertRedirect(route('admin.event-applications.index'));

    assertDatabaseMissing('application_comments', ['id' => $comment->id]);
    assertDatabaseMissing('application_state_history', ['id' => $history->id]);
});

it('deletes physical document files from storage', function () {
    Storage::fake('secure-media');

    $application = EventApplication::factory()->create();

    $filePath = 'application-documents/test-file.pdf';
    Storage::disk('secure-media')->put($filePath, 'fake content');

    ApplicationDocument::factory()->create([
        'application_id' => $application->id,
        'file_path' => $filePath,
    ]);

    Storage::disk('secure-media')->assertExists($filePath);

    actingAs($this->adminUser)
        ->delete(route('admin.event-applications.destroy', $application))
        ->assertRedirect(route('admin.event-applications.index'));

    Storage::disk('secure-media')->assertMissing($filePath);
});

it('works for any application state', function (string $factoryState) {
    $application = EventApplication::factory()->{$factoryState}()->create();

    actingAs($this->adminUser)
        ->delete(route('admin.event-applications.destroy', $application))
        ->assertRedirect(route('admin.event-applications.index'))
        ->assertSessionHas('success');

    assertDatabaseMissing('event_applications', ['id' => $application->id]);
})->with([
    'draft',
    'submitted',
    'inValidation',
    'returnedForCorrection',
    'approved',
    'rejected',
    'published',
]);
