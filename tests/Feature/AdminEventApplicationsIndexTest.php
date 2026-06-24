<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

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

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->adminUser->assignRole('admin');
});

it('renders the header card with title and subtitle on event applications index', function () {
    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee(__('event_applications.titles.applications'))
        ->assertSee(__('event_applications.header.subtitle'))
        ->assertSee(__('event_applications.header.total_records'));
});

it('renders both tab card-buttons on event applications index', function () {
    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee(__('event_applications.tabs.templates'))
        ->assertSee(__('event_applications.tabs.templates_description'))
        ->assertSee(__('event_applications.tabs.applications'))
        ->assertSee(__('event_applications.tabs.applications_description'));
});

it('displays templates data on event applications index', function () {
    $template = ApplicationTemplate::factory()->create([
        'name' => 'Test Template Alpha',
        'state' => 'open',
    ]);

    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee('Test Template Alpha');
});

it('shows template state badge and applications count', function () {
    $template = ApplicationTemplate::factory()->create([
        'name' => 'Draft Template',
        'state' => 'draft',
    ]);

    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee('Draft Template')
        ->assertSee(__('event_applications.template_states.draft'));
});

it('shows submission period dates for templates', function () {
    $template = ApplicationTemplate::factory()->create([
        'submission_start_date' => '2026-03-01',
        'submission_end_date' => '2026-04-15',
    ]);

    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee('01/03/2026')
        ->assertSee('15/04/2026');
});

it('shows empty state message when no templates exist', function () {
    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee(__('event_applications.messages.no_templates'));
});

it('shows empty state message when no applications exist', function () {
    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee(__('event_applications.messages.no_applications'));
});

it('renders section headers with count badges', function () {
    actingAs($this->adminUser)
        ->get(route('admin.event-applications.index'))
        ->assertSuccessful()
        ->assertSee(__('event_applications.tabs.templates'))
        ->assertSee(__('event_applications.tabs.applications'));
});
