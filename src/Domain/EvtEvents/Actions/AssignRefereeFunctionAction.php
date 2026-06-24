<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignRefereeFunctionAction
{
    /**
     * Add a new referee function assignment for an event.
     * A referee can have multiple functions assigned.
     *
     * @param  Individual  $assignedBy  The Chief Judge assigning the function
     * @param  array  $data  Contains: referee_function_id (optional), function_text (optional), notes (optional), is_present (optional)
     */
    public function execute(
        Event $event,
        RefereeEnrollment $refereeEnrollment,
        Individual $assignedBy,
        array $data
    ): RefereeFunctionAssignment {
        DB::beginTransaction();

        try {
            // Validate that the referee enrollment belongs to this event
            if ($refereeEnrollment->enrollment->event_id !== $event->id) {
                throw new \Exception('Referee enrollment does not belong to this event.');
            }

            // Always create a new function assignment (supports multiple functions per referee)
            $assignment = RefereeFunctionAssignment::create([
                'event_id' => $event->id,
                'referee_enrollment_id' => $refereeEnrollment->id,
                'is_present' => $data['is_present'] ?? true,
                'referee_function_id' => $data['referee_function_id'] ?? null,
                'function_text' => $data['function_text'] ?? null,
                'assigned_by' => $assignedBy->id,
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Referee function assigned successfully', [
                'event_id' => $event->id,
                'referee_enrollment_id' => $refereeEnrollment->id,
                'assignment_id' => $assignment->id,
                'assigned_by' => $assignedBy->id,
            ]);

            return $assignment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign referee function', [
                'event_id' => $event->id,
                'referee_enrollment_id' => $refereeEnrollment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
