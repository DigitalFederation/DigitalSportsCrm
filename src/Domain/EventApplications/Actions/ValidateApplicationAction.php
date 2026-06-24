<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\InValidationApplicationState;

class ValidateApplicationAction
{
    public function execute(EventApplication $application, string $userId, ?string $notes = null): EventApplication
    {
        $previousState = $application->status_class;

        $application->update([
            'status_class' => InValidationApplicationState::class,
            'validated_at' => now(),
        ]);

        ApplicationStateHistory::create([
            'application_id' => $application->id,
            'from_state' => $previousState,
            'to_state' => InValidationApplicationState::class,
            'changed_by' => $userId,
            'notes' => $notes ?? 'Application moved to validation',
        ]);

        return $application->fresh();
    }
}
