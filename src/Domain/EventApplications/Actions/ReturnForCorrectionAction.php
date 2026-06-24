<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;

class ReturnForCorrectionAction
{
    public function execute(EventApplication $application, string $userId, string $notes): EventApplication
    {
        $previousState = $application->status_class;

        $application->update([
            'status_class' => ReturnedForCorrectionApplicationState::class,
            'admin_notes' => $notes,
        ]);

        ApplicationStateHistory::create([
            'application_id' => $application->id,
            'from_state' => $previousState,
            'to_state' => ReturnedForCorrectionApplicationState::class,
            'changed_by' => $userId,
            'notes' => $notes,
        ]);

        return $application->fresh();
    }
}
