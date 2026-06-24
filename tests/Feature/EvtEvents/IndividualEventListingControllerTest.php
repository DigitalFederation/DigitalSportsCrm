<?php

use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an individual user
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=CountrySeeder');

    $this->freediverRole = ProfessionalRole::factory()->create(['name' => 'Freediver']);
    $this->federation = Federation::factory()->create();
    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->user = User::factory()->create(['group_id' => $group->id]);
    $this->individual = Individual::factory()->create(['user_id' => $this->user->id]);
    $this->individual->federations()->attach($this->federation->id);
    $this->actingAs($this->user);
});

it('does not display past or irrelevant events', function () {
    Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'only_individuals',
        'name' => 'Past Event',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(5),
    ]);

    $response = $this->get(route('individual.evt-events.events.index'));

    $response->assertStatus(200)
        ->assertDontSee('Past Event');
});

it('does not show freediving event for non freediver', function () {

    $event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'only_individuals',
        'name' => 'Freediver Event',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(5),
    ]);
    $event->professionalRoles()
        ->attach($this->freediverRole->id);

    $response = $this->get(route('individual.evt-events.events.index'));

    $response->assertStatus(200)
        ->assertDontSee('Freediver Event');

});
