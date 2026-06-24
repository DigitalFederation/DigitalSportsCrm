<?php

use App\Models\Group;
use App\Models\Sport as AppSport;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create(['code' => 'ADMIN']);
    $this->admin = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    $this->admin->assignRole('admin');
});

it('displays the sports index page', function () {
    Sport::factory()->create(['name' => 'Finswimming', 'sport_type' => 'individual']);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.sport.index'))
        ->assertSuccessful()
        ->assertSee(__('sports.individual'));
});

it('redirects to create page when no sports exist', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.sport.index'))
        ->assertRedirect(action([\App\Http\Controllers\Admin\EvtEvents\SportController::class, 'create']));
});

it('displays the create sport form', function () {
    actingAs($this->admin);

    $this->get(route('admin.evt-events.sport.create'))
        ->assertSuccessful();
});

it('can create a sport and syncs both tables', function () {
    actingAs($this->admin);

    $this->post(route('admin.evt-events.sport.store'), [
        'name' => 'New Sport',
        'sport_type' => 'team',
    ])->assertRedirect(route('admin.evt-events.sport.index'));

    $this->assertDatabaseHas('evt_sports', ['name' => 'New Sport', 'sport_type' => 'team']);
    $this->assertDatabaseHas('sports', ['name' => 'New Sport', 'sport_type' => 'team']);
});

it('displays the edit sport form', function () {
    $sport = Sport::factory()->create(['name' => 'Freediving', 'sport_type' => 'individual']);

    actingAs($this->admin);

    $this->get(route('admin.evt-events.sport.edit', $sport))
        ->assertSuccessful();
});

it('can update a sport and syncs both tables', function () {
    $evtSport = Sport::factory()->create(['name' => 'Old Name', 'sport_type' => 'individual']);
    AppSport::factory()->create(['name' => 'Old Name', 'sport_type' => 'individual']);

    actingAs($this->admin);

    $this->put(route('admin.evt-events.sport.update', $evtSport), [
        'name' => 'New Name',
        'sport_type' => 'team',
    ])->assertRedirect(route('admin.evt-events.sport.index'));

    $this->assertDatabaseHas('evt_sports', ['id' => $evtSport->id, 'name' => 'New Name', 'sport_type' => 'team']);
    $this->assertDatabaseHas('sports', ['name' => 'New Name', 'sport_type' => 'team']);
    $this->assertDatabaseMissing('sports', ['name' => 'Old Name']);
});

it('can delete a sport with no associations', function () {
    $evtSport = Sport::factory()->create(['name' => 'Deletable Sport', 'sport_type' => 'individual']);
    AppSport::factory()->create(['name' => 'Deletable Sport', 'sport_type' => 'individual']);

    actingAs($this->admin);

    $this->delete(route('admin.evt-events.sport.destroy', $evtSport))
        ->assertRedirect(route('admin.evt-events.sport.index'));

    $this->assertDatabaseMissing('evt_sports', ['id' => $evtSport->id]);
    $this->assertDatabaseMissing('sports', ['name' => 'Deletable Sport']);
});

it('cannot delete a sport with associated disciplines', function () {
    $appSport = AppSport::factory()->create(['name' => 'Protected Sport', 'sport_type' => 'individual']);
    $evtSport = Sport::factory()->create(['name' => 'Protected Sport', 'sport_type' => 'individual']);

    Discipline::factory()->create(['sport_id' => $appSport->id]);

    actingAs($this->admin);

    $this->delete(route('admin.evt-events.sport.destroy', $evtSport))
        ->assertRedirect(route('admin.evt-events.sport.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('evt_sports', ['id' => $evtSport->id]);
});

it('validates name is required', function () {
    actingAs($this->admin);

    $this->post(route('admin.evt-events.sport.store'), [
        'name' => '',
        'sport_type' => 'individual',
    ])->assertSessionHasErrors('name');
});

it('validates sport_type must be individual or team', function () {
    actingAs($this->admin);

    $this->post(route('admin.evt-events.sport.store'), [
        'name' => 'Test Sport',
        'sport_type' => 'invalid',
    ])->assertSessionHasErrors('sport_type');
});

it('validates sport_type is required', function () {
    actingAs($this->admin);

    $this->post(route('admin.evt-events.sport.store'), [
        'name' => 'Test Sport',
        'sport_type' => '',
    ])->assertSessionHasErrors('sport_type');
});
