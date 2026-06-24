<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncRefereeFunctionAssignmentsAction
{
    /**
     * Sync referee function assignments for a given enrollment within an event.
     * Removes deselected functions, creates new ones, and updates notes on remaining.
     *
     * @param  array  $data  Contains: functions (array of function texts), notes (string|null)
     */
    public function execute(
        Event $event,
        RefereeEnrollment $refereeEnrollment,
        Individual $assignedBy,
        array $data
    ): void {
        DB::beginTransaction();

        try {
            $selectedFunctions = $data['functions'] ?? [];
            $notes = $data['notes'] ?? null;
            $competitionDays = $data['competition_days'] ?? null;
            $numberOfGames = $data['number_of_games'] ?? null;

            $existingAssignments = $refereeEnrollment->refereeFunctionAssignments()
                ->where('event_id', $event->id)
                ->get();

            $existingTexts = $existingAssignments->pluck('function_text')->filter()->toArray();

            $refereeEnrollment->refereeFunctionAssignments()
                ->where('event_id', $event->id)
                ->whereNotIn('function_text', $selectedFunctions)
                ->delete();

            $newFunctions = array_diff($selectedFunctions, $existingTexts);
            foreach ($newFunctions as $functionText) {
                RefereeFunctionAssignment::create([
                    'event_id' => $event->id,
                    'referee_enrollment_id' => $refereeEnrollment->id,
                    'is_present' => true,
                    'function_text' => $functionText,
                    'assigned_by' => $assignedBy->id,
                    'notes' => $notes,
                    'competition_days' => $competitionDays,
                    'number_of_games' => $numberOfGames,
                ]);
            }

            $refereeEnrollment->refereeFunctionAssignments()
                ->where('event_id', $event->id)
                ->whereIn('function_text', $selectedFunctions)
                ->update([
                    'notes' => $notes,
                    'competition_days' => $competitionDays,
                    'number_of_games' => $numberOfGames,
                ]);

            DB::commit();

            Log::info('Referee function assignments synced', [
                'event_id' => $event->id,
                'referee_enrollment_id' => $refereeEnrollment->id,
                'assigned_by' => $assignedBy->id,
                'functions_count' => count($selectedFunctions),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync referee function assignments', [
                'event_id' => $event->id,
                'referee_enrollment_id' => $refereeEnrollment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
