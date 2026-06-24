<?php

use Domain\EvtEvents\Actions\AttributeOrganizerToEventAction;
use Domain\Federations\Models\Federation;

it('assigns a federation as an organizer to an event', function () {
    $event = \Domain\EvtEvents\Models\Event::factory()->create();
    $federation = Federation::factory()->create();

    $action = new AttributeOrganizerToEventAction;
    $action->execute($event->id, $federation->id);

    $this->assertDatabaseHas('evt_organizers', [
        'organizable_id' => $federation->id,
        'organizable_type' => Federation::class,
        'event_id' => $event->id,
    ]);
});
