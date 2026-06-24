<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\SubmittedApplicationState;

class SubmitApplicationAction
{
    public function execute(EventApplication $application, string $userId): EventApplication
    {
        $previousState = $application->status_class;

        $application->update([
            'status_class' => SubmittedApplicationState::class,
            'submitted_at' => now(),
        ]);

        ApplicationStateHistory::create([
            'application_id' => $application->id,
            'from_state' => $previousState,
            'to_state' => SubmittedApplicationState::class,
            'changed_by' => $userId,
            'notes' => 'Application submitted',
        ]);

        return $application->fresh();
    }
}
