<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;

class ApproveApplicationAction
{
    public function execute(EventApplication $application, string $userId, ?string $notes = null): EventApplication
    {
        $previousState = $application->status_class;

        $application->update([
            'status_class' => ApprovedApplicationState::class,
            'decided_at' => now(),
            'admin_notes' => $notes,
        ]);

        ApplicationStateHistory::create([
            'application_id' => $application->id,
            'from_state' => $previousState,
            'to_state' => ApprovedApplicationState::class,
            'changed_by' => $userId,
            'notes' => $notes ?? 'Application approved',
        ]);

        return $application->fresh();
    }
}
