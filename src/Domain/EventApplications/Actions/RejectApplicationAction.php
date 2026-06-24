<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\RejectedApplicationState;

class RejectApplicationAction
{
    public function execute(EventApplication $application, string $userId, string $notes): EventApplication
    {
        $previousState = $application->status_class;

        $application->update([
            'status_class' => RejectedApplicationState::class,
            'decided_at' => now(),
            'admin_notes' => $notes,
        ]);

        ApplicationStateHistory::create([
            'application_id' => $application->id,
            'from_state' => $previousState,
            'to_state' => RejectedApplicationState::class,
            'changed_by' => $userId,
            'notes' => $notes,
        ]);

        return $application->fresh();
    }
}
