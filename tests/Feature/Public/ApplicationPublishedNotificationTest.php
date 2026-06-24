<?php

use App\Models\User;
use App\Notifications\EventApplications\ApplicationPublishedNotification;
use Domain\EventApplications\Models\EventApplication;
use Domain\EvtEvents\Models\Event;

it('uses the public event detail route when an application has a published event', function (): void {
    $event = Event::factory()->create();
    $application = EventApplication::factory()->create([
        'published_event_id' => $event->id,
    ]);

    $payload = (new ApplicationPublishedNotification($application))->toArray(User::factory()->make());

    expect($payload['event_url'])->toBe(route('public.event.show', $event));
});

it('falls back to the public events calendar when no published event exists', function (): void {
    $application = EventApplication::factory()->create([
        'published_event_id' => null,
    ]);

    $payload = (new ApplicationPublishedNotification($application))->toArray(User::factory()->make());

    expect($payload['event_url'])->toBe(route('public.events'));
});
