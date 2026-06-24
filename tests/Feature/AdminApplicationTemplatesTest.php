<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\EventApplications\Models\ApplicationTemplate;
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

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);

    $this->template = ApplicationTemplate::factory()->create([
        'state' => 'draft',
    ]);
});

it('allows admin to view template create page', function () {
    actingAs($this->adminUser)
        ->get(route('admin.application-templates.create'))
        ->assertSuccessful();
});

it('allows admin to create a template', function () {
    $response = actingAs($this->adminUser)
        ->post(route('admin.application-templates.store'), [
            'name' => 'Test Template',
            'event_type' => 'competition',
            'submission_start_date' => now()->addDay()->format('Y-m-d'),
            'submission_end_date' => now()->addMonth()->format('Y-m-d'),
            'target_audience' => 'both',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('application_templates', [
        'name' => 'Test Template',
        'event_type' => 'competition',
    ]);
});

it('allows admin to view template show page', function () {
    actingAs($this->adminUser)
        ->get(route('admin.application-templates.show', $this->template))
        ->assertSuccessful()
        ->assertSee($this->template->name);
});

it('allows admin to view template edit page', function () {
    actingAs($this->adminUser)
        ->get(route('admin.application-templates.edit', $this->template))
        ->assertSuccessful();
});

it('allows admin to update a template', function () {
    actingAs($this->adminUser)
        ->put(route('admin.application-templates.update', $this->template), [
            'name' => 'Updated Template Name',
            'event_type' => $this->template->event_type,
            'submission_start_date' => $this->template->submission_start_date->format('Y-m-d'),
            'submission_end_date' => $this->template->submission_end_date->format('Y-m-d'),
            'target_audience' => 'both',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('application_templates', [
        'id' => $this->template->id,
        'name' => 'Updated Template Name',
    ]);
});

it('allows admin to update template state', function () {
    actingAs($this->adminUser)
        ->patch(route('admin.application-templates.update-state', $this->template), [
            'state' => 'open',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->template->fresh()->state)->toBe('open');
});

it('allows admin to activate a template', function () {
    actingAs($this->adminUser)
        ->post(route('admin.application-templates.activate', $this->template))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->template->fresh()->state)->toBe('open');
});

it('allows admin to deactivate a template', function () {
    $this->template->update(['state' => 'open']);

    actingAs($this->adminUser)
        ->post(route('admin.application-templates.deactivate', $this->template))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->template->fresh()->state)->toBe('closed');
});

it('allows admin to delete a template without applications', function () {
    actingAs($this->adminUser)
        ->delete(route('admin.application-templates.destroy', $this->template))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($this->template->fresh()->trashed())->toBeTrue();
});
