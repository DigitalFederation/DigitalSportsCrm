<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\PublishedApplicationState;

class PublishApplicationAction
{
    public function execute(EventApplication $application, string $userId, ?int $publishedEventId = null): EventApplication
    {
        $previousState = $application->status_class;

        $application->update([
            'status_class' => PublishedApplicationState::class,
            'published_at' => now(),
            'published_event_id' => $publishedEventId,
        ]);

        ApplicationStateHistory::create([
            'application_id' => $application->id,
            'from_state' => $previousState,
            'to_state' => PublishedApplicationState::class,
            'changed_by' => $userId,
            'notes' => 'Application published to calendar',
        ]);

        return $application->fresh();
    }
}
