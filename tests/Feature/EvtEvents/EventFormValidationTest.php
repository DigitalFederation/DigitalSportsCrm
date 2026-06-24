<?php

use App\Enums\EvtCompetitionStatusEnum;
use App\Enums\EvtEventEnrollmentTypeEnum;
use App\Models\Country;
use App\Models\Group;
use App\Models\Sport;
use Database\Factories\DistrictFactory;
use Database\Factories\UserFactory;
use Database\Factories\ZoneFactory;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\PreparationEventState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
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
    $this->country = Country::factory()->create();
    $this->district = DistrictFactory::new()->create(['country_id' => $this->country->id]);
    $this->zone = ZoneFactory::new()->create(['is_active' => true]);

    // Base organizational event data
    $this->baseEventData = [
        'name' => 'Test Event',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
        'event_category' => 'organization',
        'status_class' => 'ActiveEventState',
        'enrollment_type' => 'open',
    ];

    // Base competition event data
    $this->baseCompetitionData = [
        'name' => 'Test Competition Event',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDays(10)->format('Y-m-d'),
        'event_category' => 'competition',
        'status_class' => 'ActiveEventState',
        'enrollment_type' => EvtEventEnrollmentTypeEnum::only_federations->name,
        'competition' => [
            'status_class' => EvtCompetitionStatusEnum::APPROVED->value,
            'sport_id' => null, // Will be set in tests
            'competition_start_date' => now()->format('Y-m-d'),
            'competition_end_date' => now()->addDays(5)->format('Y-m-d'),
        ],
    ];
});

describe('Event Store Validation', function () {
    it('requires name field', function () {
        $this->actingAs($this->user);

        $data = $this->baseEventData;
        unset($data['name']);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('name');
    });

    it('requires start_date field', function () {
        $this->actingAs($this->user);

        $data = $this->baseEventData;
        unset($data['start_date']);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('start_date');
    });

    it('requires end_date field', function () {
        $this->actingAs($this->user);

        $data = $this->baseEventData;
        unset($data['end_date']);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('end_date');
    });

    it('validates start_date is before or equal to end_date', function () {
        $this->actingAs($this->user);

        $data = $this->baseEventData;
        $data['start_date'] = now()->addDays(10)->format('Y-m-d');
        $data['end_date'] = now()->format('Y-m-d');

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('start_date');
    });

    it('requires event_category field', function () {
        $this->actingAs($this->user);

        $data = $this->baseEventData;
        unset($data['event_category']);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('event_category');
    });

    it('requires enrollment_type field', function () {
        $this->actingAs($this->user);

        $data = $this->baseEventData;
        unset($data['enrollment_type']);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('enrollment_type');
    });

    it('accepts empty strings for nullable integer fields and converts them to null', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => '',
            'chief_judge_id' => '',
            'competition_director_id' => '',
            'venue_country_id' => '',
            'venue_district_id' => '',
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionDoesntHaveErrors([
            'technical_delegate_id',
            'chief_judge_id',
            'competition_director_id',
            'venue_country_id',
            'venue_district_id',
        ]);

        $event = Event::where('name', $data['name'])->first();
        expect($event)->not->toBeNull();
    });

    it('accepts valid individual IDs for event role fields', function () {
        $this->actingAs($this->user);

        $technicalDelegate = Individual::factory()->create();
        $chiefJudge = Individual::factory()->create();
        $competitionDirector = Individual::factory()->create();

        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => $technicalDelegate->id,
            'chief_judge_id' => $chiefJudge->id,
            'competition_director_id' => $competitionDirector->id,
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionDoesntHaveErrors([
            'technical_delegate_id',
            'chief_judge_id',
            'competition_director_id',
        ]);

        $event = Event::where('name', $data['name'])->first();
        expect($event)->not->toBeNull();

        // Verify roles were assigned
        expect(EventRole::where('event_id', $event->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->where('individual_id', $technicalDelegate->id)
            ->exists())->toBeTrue();

        expect(EventRole::where('event_id', $event->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->where('individual_id', $chiefJudge->id)
            ->exists())->toBeTrue();

        expect(EventRole::where('event_id', $event->id)
            ->where('role', EventRole::ROLE_COMPETITION_DIRECTOR)
            ->where('individual_id', $competitionDirector->id)
            ->exists())->toBeTrue();
    });

    it('validates venue_country_id exists in country table', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'venue_country_id' => 99999,
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('venue_country_id');
    });

    it('validates venue_district_id exists in districts table', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'venue_district_id' => 99999,
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('venue_district_id');
    });

    it('accepts valid venue location fields', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'venue' => 'Olympic Stadium',
            'venue_address' => '123 Sports Avenue',
            'venue_postal_code' => '12345',
            'venue_city' => 'Sports City',
            'venue_country_id' => $this->country->id,
            'venue_district_id' => $this->district->id,
            'location_url' => 'https://maps.google.com/example',
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionDoesntHaveErrors([
            'venue',
            'venue_address',
            'venue_postal_code',
            'venue_city',
            'venue_country_id',
            'venue_district_id',
            'location_url',
        ]);

        $event = Event::where('name', $data['name'])->first();
        expect($event)->not->toBeNull();
        expect($event->venue)->toBe('Olympic Stadium');
        expect($event->venue_city)->toBe('Sports City');
    });

    it('validates location_url is a valid URL', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'location_url' => 'not-a-valid-url',
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('location_url');
    });

    it('creates event with all optional fields', function () {
        $this->actingAs($this->user);

        $professionalRole = ProfessionalRole::factory()->create();
        $attribute = Attribute::factory()->create(['enrollment_type' => 'STAFF']);

        $data = array_merge($this->baseEventData, [
            'notes' => 'Test notes for the event',
            'location' => 'Test Location',
            'event_type' => 'conference',
            'organization_type' => 'meeting',
            'is_visible' => true,
            'external_url' => 'https://example.com/event',
            'venue' => 'Grand Hotel',
            'venue_address' => '456 Main Street',
            'venue_postal_code' => '54321',
            'venue_city' => 'Event City',
            'venue_country_id' => $this->country->id,
            'venue_district_id' => $this->district->id,
            'allow_coach_enrollment' => true,
            'allow_referee_enrollment' => true,
            'allow_official_enrollment' => true,
            'allow_individual_enrollment' => false,
            'public_athlete_list' => true,
            'public_coach_list' => false,
            'public_referee_list' => true,
            'professional_roles' => [$professionalRole->id],
            'selected_staff_attributes' => [$attribute->id],
            'selected_zones' => [$this->zone->id],
            'selected_districts' => [$this->district->id],
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $event = Event::where('name', $data['name'])->first();

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
        $response->assertSessionHas('success');

        expect($event)->not->toBeNull();
        expect($event->notes)->toBe('Test notes for the event');
        expect((bool) $event->is_visible)->toBeTrue();
        expect((bool) $event->allow_coach_enrollment)->toBeTrue();
        expect((bool) $event->public_athlete_list)->toBeTrue();
    });

    it('requires sport_id for competition events', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        unset($data['competition']['sport_id']);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('competition.sport_id');
    });

    it('creates competition event with all competition fields', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        $data['competition']['sport_id'] = $this->sport->id;
        $data['competition']['rounds_total'] = 3;
        $data['competition']['cat_age'] = 'senior';
        $data['competition']['cat_competition'] = 'national';
        $data['competition']['environment'] = 'indoor';
        $data['competition']['medals_gold'] = 1;
        $data['competition']['medals_silver'] = 1;
        $data['competition']['medals_bronze'] = 1;
        $data['competition']['max_disciplines_per_athlete'] = 5;
        $data['competition']['max_relays_per_athlete'] = 2;
        $data['competition']['max_teams_per_athlete'] = 1;
        $data['start_registration'] = now()->format('Y-m-d');
        $data['end_registration'] = now()->addDays(3)->format('Y-m-d');

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $event = Event::where('name', $data['name'])->with('competition')->first();

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
        expect($event)->not->toBeNull();
        expect($event->competition)->not->toBeNull();
        expect($event->competition->sport_id)->toBe($this->sport->id);
        expect($event->competition->rounds_total)->toBe(3);
    });
});

describe('Event Update Validation', function () {
    beforeEach(function () {
        $this->event = Event::factory()->create([
            'name' => 'Existing Event',
            'event_category' => 'organization',
            'status_class' => ActiveEventState::class,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'enrollment_type' => 'open',
        ]);
    });

    it('updates event with valid data', function () {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Updated Event Name',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'event_category' => 'organization',
            'status_class' => 'ActiveEventState',
            'enrollment_type' => 'open',
        ];

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));
        $response->assertSessionHas('success', 'Event updated successfully.');

        $this->event->refresh();
        expect($this->event->name)->toBe('Updated Event Name');
    });

    it('accepts empty strings for nullable integer fields on update', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => '',
            'chief_judge_id' => '',
            'competition_director_id' => '',
            'venue_country_id' => '',
            'venue_district_id' => '',
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertSessionDoesntHaveErrors([
            'technical_delegate_id',
            'chief_judge_id',
            'competition_director_id',
            'venue_country_id',
            'venue_district_id',
        ]);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));
    });

    it('can assign event roles on update', function () {
        $this->actingAs($this->user);

        $technicalDelegate = Individual::factory()->create();
        $chiefJudge = Individual::factory()->create();
        $competitionDirector = Individual::factory()->create();

        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => $technicalDelegate->id,
            'chief_judge_id' => $chiefJudge->id,
            'competition_director_id' => $competitionDirector->id,
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));

        // Verify roles were assigned
        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->where('individual_id', $technicalDelegate->id)
            ->exists())->toBeTrue();

        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->where('individual_id', $chiefJudge->id)
            ->exists())->toBeTrue();

        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_COMPETITION_DIRECTOR)
            ->where('individual_id', $competitionDirector->id)
            ->exists())->toBeTrue();
    });

    it('can clear event roles by passing empty strings', function () {
        $this->actingAs($this->user);

        // First assign a role
        $individual = Individual::factory()->create();
        EventRole::firstOrCreate([
            'event_id' => $this->event->id,
            'individual_id' => $individual->id,
            'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
        ]);

        // Verify role exists
        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->exists())->toBeTrue();

        // Update with empty string to clear the role
        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => '',
            'chief_judge_id' => '',
            'competition_director_id' => '',
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));
        $response->assertSessionHas('success');

        // Verify role was removed
        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->exists())->toBeFalse();
    });

    it('can change event role to different individual', function () {
        $this->actingAs($this->user);

        // First assign a role
        $originalIndividual = Individual::factory()->create();
        EventRole::firstOrCreate([
            'event_id' => $this->event->id,
            'individual_id' => $originalIndividual->id,
            'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
        ]);

        // Create new individual to assign
        $newIndividual = Individual::factory()->create();

        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => $newIndividual->id,
            'chief_judge_id' => '',
            'competition_director_id' => '',
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));

        // Verify old role was removed and new role was assigned
        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->where('individual_id', $originalIndividual->id)
            ->exists())->toBeFalse();

        expect(EventRole::where('event_id', $this->event->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->where('individual_id', $newIndividual->id)
            ->exists())->toBeTrue();
    });

    it('updates venue information', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'venue' => 'New Venue Name',
            'venue_address' => '789 Updated Street',
            'venue_postal_code' => '99999',
            'venue_city' => 'Updated City',
            'venue_country_id' => $this->country->id,
            'venue_district_id' => $this->district->id,
            'location_url' => 'https://maps.example.test/location',
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));

        $this->event->refresh();
        expect($this->event->venue)->toBe('New Venue Name');
        expect($this->event->venue_city)->toBe('Updated City');
        expect($this->event->venue_country_id)->toBe($this->country->id);
    });

    it('updates enrollment settings', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'allow_coach_enrollment' => true,
            'allow_referee_enrollment' => true,
            'allow_official_enrollment' => false,
            'allow_individual_enrollment' => true,
            'public_athlete_list' => true,
            'public_coach_list' => true,
            'public_referee_list' => false,
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));

        $this->event->refresh();
        expect((bool) $this->event->allow_coach_enrollment)->toBeTrue();
        expect((bool) $this->event->allow_referee_enrollment)->toBeTrue();
        expect((bool) $this->event->allow_individual_enrollment)->toBeTrue();
        expect((bool) $this->event->public_athlete_list)->toBeTrue();
    });

    it('updates event status', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'status_class' => 'PreparationEventState',
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $this->event->id));

        $this->event->refresh();
        expect($this->event->status_class)->toBe(PreparationEventState::class);
    });

    it('rejects invalid individual_id for event roles', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'technical_delegate_id' => 'non-existent-uuid', // Non-existent individual
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertSessionHasErrors('technical_delegate_id');
    });

    it('validates organizer_details email format', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'organizer_details' => [
                'email_contact' => 'not-a-valid-email',
            ],
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertSessionHasErrors('organizer_details.email_contact');
    });

    it('accepts valid organizer_details', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'organizer_details' => [
                'responsible_person' => 'Example Organizer',
                'email_contact' => 'organizer@example.test',
                'phone_contact' => '+15550101000',
                'bod_meeting_no' => 'BOD-2024-001',
            ],
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $this->event->id), $data);

        $response->assertSessionDoesntHaveErrors([
            'organizer_details.responsible_person',
            'organizer_details.email_contact',
            'organizer_details.phone_contact',
            'organizer_details.bod_meeting_no',
        ]);
    });
});

describe('Competition Event Specific Validation', function () {
    it('validates competition max limits are non-negative integers', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        $data['competition']['sport_id'] = $this->sport->id;
        $data['competition']['max_disciplines_per_athlete'] = -1;

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('competition.max_disciplines_per_athlete');
    });

    it('validates registration dates order', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        $data['competition']['sport_id'] = $this->sport->id;
        $data['start_registration'] = now()->addDays(5)->format('Y-m-d');
        $data['end_registration'] = now()->format('Y-m-d'); // Before start

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('start_registration');
    });

    it('validates competition dates order', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        $data['competition']['sport_id'] = $this->sport->id;
        $data['competition']['competition_start_date'] = now()->addDays(10)->format('Y-m-d');
        $data['competition']['competition_end_date'] = now()->format('Y-m-d'); // Before start

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('competition.competition_start_date');
    });

    it('accepts valid anti-doping data', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        $data['competition']['sport_id'] = $this->sport->id;
        $data['anti_doping'] = [
            'responsible_name' => 'Example Responsible Person',
            'responsible_phone' => '+15550101000',
            'responsible_email' => 'antidoping@example.test',
            'num_controls_planned' => 10,
            'number_of_controls' => 5,
            'expected_athletes' => 100,
        ];

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionDoesntHaveErrors([
            'anti_doping.responsible_name',
            'anti_doping.responsible_email',
            'anti_doping.num_controls_planned',
        ]);
    });

    it('validates anti-doping email format', function () {
        $this->actingAs($this->user);

        $data = $this->baseCompetitionData;
        $data['competition']['sport_id'] = $this->sport->id;
        $data['anti_doping'] = [
            'responsible_email' => 'invalid-email',
        ];

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('anti_doping.responsible_email');
    });
});

describe('Registration Dates for Organization Events', function () {
    it('saves registration dates for organization events on store', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'start_registration' => now()->format('Y-m-d'),
            'end_registration' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $event = Event::where('name', $data['name'])->first();

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
        expect($event)->not->toBeNull();
        expect($event->start_registration)->not->toBeNull();
        expect($event->end_registration)->not->toBeNull();
    });

    it('saves registration dates for organization events on update', function () {
        $this->actingAs($this->user);

        $event = Event::factory()->create([
            'event_category' => 'organization',
            'status_class' => ActiveEventState::class,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'enrollment_type' => 'open',
            'start_registration' => null,
            'end_registration' => null,
        ]);

        $data = array_merge($this->baseEventData, [
            'start_registration' => now()->format('Y-m-d'),
            'end_registration' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->put(route('admin.evt-events.events.update', $event->id), $data);

        $response->assertRedirect(route('admin.evt-events.events.show', $event->id));

        $event->refresh();
        expect($event->start_registration)->not->toBeNull();
        expect($event->end_registration)->not->toBeNull();
    });

    it('validates registration dates order for organization events', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'start_registration' => now()->addDays(5)->format('Y-m-d'),
            'end_registration' => now()->format('Y-m-d'),
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('start_registration');
    });
});

describe('Geographic and Attribute Validation', function () {
    it('validates selected_zones exist in zones table', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'selected_zones' => [99999],
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('selected_zones.0');
    });

    it('validates selected_districts exist in districts table', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'selected_districts' => [99999],
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('selected_districts.0');
    });

    it('validates professional_roles exist', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'professional_roles' => [99999],
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('professional_roles.0');
    });

    it('accepts valid attribute selections', function () {
        $this->actingAs($this->user);

        $staffAttribute = Attribute::factory()->create(['enrollment_type' => 'STAFF']);
        $refereeAttribute = Attribute::factory()->create(['enrollment_type' => 'REFEREE']);
        $coachAttribute = Attribute::factory()->create(['enrollment_type' => 'COACH']);
        $officialAttribute = Attribute::factory()->create(['enrollment_type' => 'OFFICIAL']);

        $data = array_merge($this->baseEventData, [
            'selected_staff_attributes' => [$staffAttribute->id],
            'selected_referee_attributes' => [$refereeAttribute->id],
            'selected_coach_attributes' => [$coachAttribute->id],
            'selected_official_attributes' => [$officialAttribute->id],
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionDoesntHaveErrors([
            'selected_staff_attributes',
            'selected_referee_attributes',
            'selected_coach_attributes',
            'selected_official_attributes',
        ]);
    });

    it('validates selected_attributes exist in evt_attributes table', function () {
        $this->actingAs($this->user);

        $data = array_merge($this->baseEventData, [
            'selected_attributes' => [99999],
        ]);

        $response = $this->post(route('admin.evt-events.events.store'), $data);

        $response->assertSessionHasErrors('selected_attributes.0');
    });
});
