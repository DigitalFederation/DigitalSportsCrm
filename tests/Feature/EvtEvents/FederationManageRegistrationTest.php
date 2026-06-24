<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\Sport;
use App\Models\User;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;

test('component properly loads relationships when assigning disciplines', function () {
    // Setup base models
    $federation = Federation::factory()->create();
    $sport = Sport::factory()->create();

    // Create event with proper relationships
    $event = Event::factory()
        ->has(Competition::factory()->for($sport))
        ->create();

    // Create discipline with proper relationships
    $discipline = Discipline::factory()->create([
        'athlete_limit' => 3,
        'enrollment_type' => 'individual',
    ]);

    // Attach the discipline to the competition
    $event->competition->disciplines()->attach($discipline);

    // Create athletes with proper federation relationships and users
    $athletes = Individual::factory()
        ->count(3)
        ->create(['gender' => 'male'])
        ->each(function ($athlete) use ($federation) {
            // Create user for individual
            $user = User::factory()->create();
            $athlete->update(['user_id' => $user->id]);

            // Create federation relationship
            $athlete->individualFederations()->create([
                'federation_id' => $federation->id,
                'status_class' => ActiveIndividualFederationState::class,
            ]);
        });

    // Create base enrollments first with user_id
    $enrollments = $athletes->map(function ($athlete) use ($event) {
        return Enrollment::create([
            'event_id' => $event->id,
            'enrollable_id' => $athlete->id,
            'enrollable_type' => Individual::class,
            'user_id' => $athlete->user_id,
            'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
        ]);
    });

    // Create athlete enrollments
    $athleteEnrollments = $athletes->map(function ($athlete) use ($event, $federation, $discipline, $enrollments) {
        return AthleteEnrollment::create([
            'event_id' => $event->id,
            'federation_id' => $federation->id,
            'discipline_id' => $discipline->id,
            'individual_id' => $athlete->id,
            'enrollment_id' => $enrollments->firstWhere('enrollable_id', $athlete->id)->id,
            'status_class' => EvtAthleteEnrollmentStatusEnum::PAID->value,
        ]);
    });

    // Refresh the athlete enrollments with relationships loaded
    $athleteEnrollments = AthleteEnrollment::with('enrollment')
        ->whereIn('individual_id', $athletes->pluck('id'))
        ->get();

    // Test the relationships
    expect($athleteEnrollments)->toHaveCount(3)
        ->and($athleteEnrollments->first()->enrollment)->toBeInstanceOf(Enrollment::class)
        ->and($athleteEnrollments->first()->enrollment->enrollable_id)->toBe($athletes->first()->id)
        ->and($athleteEnrollments->first()->enrollment->enrollable_type)->toBe(Individual::class);
});
