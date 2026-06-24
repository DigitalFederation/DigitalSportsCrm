<?php

use App\Enums\EventApplicationTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates application with correct data', function () {
    $entity = Entity::factory()->create();
    $sport = Sport::factory()->create();
    $district = District::factory()->create();

    $action = new CreateApplicationAction;
    $application = $action->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'event_name' => 'Test Event',
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
        'district_id' => $district->id,
        'municipality' => 'Test City',
        'responsible_name' => 'John Doe',
        'responsible_phone' => '123456789',
        'target_audience' => 'athletes',
        'expected_participants' => 50,
    ]);

    expect($application)->toBeInstanceOf(EventApplication::class)
        ->and($application->event_name)->toBe('Test Event')
        ->and($application->entity_id)->toBe($entity->id)
        ->and($application->sport_id)->toBe($sport->id);
});

test('sets initial state to draft', function () {
    $entity = Entity::factory()->create();

    $action = new CreateApplicationAction;
    $application = $action->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'event_name' => 'Test Event',
        'event_type' => 'competition',
    ]);

    expect($application->status_class)->toBe(DraftApplicationState::class);
});

test('persists application to database', function () {
    $entity = Entity::factory()->create();

    $action = new CreateApplicationAction;
    $application = $action->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'event_name' => 'Test Event',
        'event_type' => 'competition',
    ]);

    expect(EventApplication::count())->toBe(1);
    $this->assertDatabaseHas('event_applications', [
        'id' => $application->id,
        'event_name' => 'Test Event',
    ]);
});
