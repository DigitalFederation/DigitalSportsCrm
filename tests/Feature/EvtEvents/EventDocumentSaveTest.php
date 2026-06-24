<?php

use App\Enums\EvtCompetitionStatusEnum;
use App\Enums\EvtEventEnrollmentTypeEnum;
use App\Models\Group;
use App\Models\Sport;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->group = Group::factory()->create(['code' => 'ADMIN']);
    $this->user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    $this->user->assignRole('admin');
    $this->sport = Sport::factory()->create();
});

describe('Competition Event Document Persistence', function () {
    it('saves required documents on store', function () {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Competition With Docs',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'event_category' => 'competition',
            'status_class' => 'ActiveEventState',
            'enrollment_type' => EvtEventEnrollmentTypeEnum::only_federations->name,
            'competition' => [
                'status_class' => EvtCompetitionStatusEnum::APPROVED->value,
                'sport_id' => $this->sport->id,
                'required_athlete_documents' => ['CmasAthleteCodeOfConduct', 'MedicalStatement'],
                'required_coach_documents' => ['CmasCoachCodeOfConduct'],
                'required_referee_documents' => ['CmasRefereeJudgeCodeOfConduct'],
                'required_official_documents' => ['TeamOfficialCodeOfConduct'],
            ],
        ];

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $event = Event::where('name', 'Test Competition With Docs')->with('competition')->first();

        expect($event)->not->toBeNull();
        expect($event->competition)->not->toBeNull();
        expect($event->competition->required_athlete_documents)->toBe(['CmasAthleteCodeOfConduct', 'MedicalStatement']);
        expect($event->competition->required_coach_documents)->toBe(['CmasCoachCodeOfConduct']);
        expect($event->competition->required_referee_documents)->toBe(['CmasRefereeJudgeCodeOfConduct']);
        expect($event->competition->required_official_documents)->toBe(['TeamOfficialCodeOfConduct']);
    });

    it('saves required documents on update', function () {
        $this->actingAs($this->user);

        $event = Event::factory()->create([
            'name' => 'Existing Competition',
            'event_category' => 'competition',
            'status_class' => ActiveEventState::class,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'enrollment_type' => 'open',
        ]);

        $competition = Competition::factory()->create([
            'event_id' => $event->id,
            'sport_id' => $this->sport->id,
        ]);

        expect($competition->required_athlete_documents)->toBeNull();

        $data = [
            'name' => 'Existing Competition',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'event_category' => 'competition',
            'status_class' => 'ActiveEventState',
            'enrollment_type' => 'open',
            'competition' => [
                'sport_id' => $this->sport->id,
                'required_athlete_documents' => ['CmasAthleteCodeOfConduct', 'MedicalStatement'],
                'required_coach_documents' => ['CmasCoachCodeOfConduct'],
                'required_referee_documents' => ['CmasRefereeJudgeCodeOfConduct', 'MedicalStatement'],
                'required_official_documents' => ['TeamOfficialCodeOfConduct'],
            ],
            'anti_doping' => [
                'responsible_name' => '',
                'responsible_phone' => '',
                'responsible_email' => '',
                'num_controls_planned' => '',
                'number_of_controls' => '',
                'expected_athletes' => '',
            ],
        ];

        $response = $this->put(route('admin.evt-events.events.update', $event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
        $response->assertSessionHas('success');

        $competition->refresh();
        expect($competition->required_athlete_documents)->toBe(['CmasAthleteCodeOfConduct', 'MedicalStatement']);
        expect($competition->required_coach_documents)->toBe(['CmasCoachCodeOfConduct']);
        expect($competition->required_referee_documents)->toBe(['CmasRefereeJudgeCodeOfConduct', 'MedicalStatement']);
        expect($competition->required_official_documents)->toBe(['TeamOfficialCodeOfConduct']);
    });

    it('saves documents on update even without anti_doping data', function () {
        $this->actingAs($this->user);

        $event = Event::factory()->create([
            'name' => 'Competition No Doping',
            'event_category' => 'competition',
            'status_class' => ActiveEventState::class,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'enrollment_type' => 'open',
        ]);

        $competition = Competition::factory()->create([
            'event_id' => $event->id,
            'sport_id' => $this->sport->id,
        ]);

        $data = [
            'name' => 'Competition No Doping',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'event_category' => 'competition',
            'status_class' => 'ActiveEventState',
            'enrollment_type' => 'open',
            'competition' => [
                'sport_id' => $this->sport->id,
                'required_athlete_documents' => ['ADELCertificate'],
            ],
        ];

        $response = $this->put(route('admin.evt-events.events.update', $event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
        $response->assertSessionHas('success');

        $competition->refresh();
        expect($competition->required_athlete_documents)->toBe(['ADELCertificate']);
    });

    it('clears documents when empty array is submitted on update', function () {
        $this->actingAs($this->user);

        $event = Event::factory()->create([
            'name' => 'Competition Clear Docs',
            'event_category' => 'competition',
            'status_class' => ActiveEventState::class,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'enrollment_type' => 'open',
        ]);

        $competition = Competition::factory()->create([
            'event_id' => $event->id,
            'sport_id' => $this->sport->id,
            'required_athlete_documents' => ['CmasAthleteCodeOfConduct'],
        ]);

        expect($competition->required_athlete_documents)->toBe(['CmasAthleteCodeOfConduct']);

        $data = [
            'name' => 'Competition Clear Docs',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'event_category' => 'competition',
            'status_class' => 'ActiveEventState',
            'enrollment_type' => 'open',
            'competition' => [
                'sport_id' => $this->sport->id,
                // No document fields - simulates all checkboxes unchecked
            ],
            'anti_doping' => [
                'responsible_name' => '',
            ],
        ];

        $response = $this->put(route('admin.evt-events.events.update', $event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));

        $competition->refresh();
        // Documents should be cleared to null (empty arrays become null)
        expect($competition->required_athlete_documents)->toBeNull();
    });
});
