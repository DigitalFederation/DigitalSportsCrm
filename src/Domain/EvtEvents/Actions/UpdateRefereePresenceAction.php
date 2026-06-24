<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateRefereePresenceAction
{
    /**
     * Update presence status, competition data, and function assignments for referees in an event.
     * Creates a presence record if one doesn't exist for the referee.
     *
     * @param  array  $presenceData  Array of [referee_enrollment_id => is_present]
     * @param  array  $competitionDaysData  Array of [referee_enrollment_id => days]
     * @param  array  $numberOfGamesData  Array of [referee_enrollment_id => games]
     * @param  array  $functionsData  Array of [referee_enrollment_id => [function_ids]]
     */
    public function execute(
        Event $event,
        Individual $updatedBy,
        array $presenceData,
        array $competitionDaysData = [],
        array $numberOfGamesData = [],
        array $functionsData = []
    ): void {
        DB::beginTransaction();

        try {
            foreach ($presenceData as $enrollmentId => $isPresent) {
                $competitionDays = $competitionDaysData[$enrollmentId] ?? null;
                $numberOfGames = $numberOfGamesData[$enrollmentId] ?? null;
                $functionIds = $functionsData[$enrollmentId] ?? [];

                // Convert empty strings to null
                $competitionDays = $competitionDays !== '' ? $competitionDays : null;
                $numberOfGames = $numberOfGames !== '' ? $numberOfGames : null;

                // Filter out null/empty function IDs
                $functionIds = array_filter($functionIds, fn ($id) => ! empty($id));

                // Get existing assignments for this referee in this event
                $existingAssignments = RefereeFunctionAssignment::where('event_id', $event->id)
                    ->where('referee_enrollment_id', $enrollmentId)
                    ->get();

                if (! empty($functionIds)) {
                    // Sync function assignments: delete old ones not in the new list, create new ones
                    $existingFunctionIds = $existingAssignments->pluck('referee_function_id')->filter()->toArray();
                    $functionIdsToCreate = array_diff($functionIds, $existingFunctionIds);
                    $functionIdsToDelete = array_diff($existingFunctionIds, $functionIds);

                    // Delete assignments for functions no longer selected
                    if (! empty($functionIdsToDelete)) {
                        RefereeFunctionAssignment::where('event_id', $event->id)
                            ->where('referee_enrollment_id', $enrollmentId)
                            ->whereIn('referee_function_id', $functionIdsToDelete)
                            ->delete();
                    }

                    // Delete any null function assignments if we have real functions now
                    RefereeFunctionAssignment::where('event_id', $event->id)
                        ->where('referee_enrollment_id', $enrollmentId)
                        ->whereNull('referee_function_id')
                        ->delete();

                    // Create new function assignments
                    foreach ($functionIdsToCreate as $functionId) {
                        RefereeFunctionAssignment::create([
                            'event_id' => $event->id,
                            'referee_enrollment_id' => $enrollmentId,
                            'referee_function_id' => $functionId,
                            'is_present' => $isPresent,
                            'competition_days' => $competitionDays,
                            'number_of_games' => $numberOfGames,
                            'assigned_by' => $updatedBy->id,
                        ]);
                    }

                    // Update existing assignments with presence and competition data
                    RefereeFunctionAssignment::where('event_id', $event->id)
                        ->where('referee_enrollment_id', $enrollmentId)
                        ->whereIn('referee_function_id', array_intersect($functionIds, $existingFunctionIds))
                        ->update([
                            'is_present' => $isPresent,
                            'competition_days' => $competitionDays,
                            'number_of_games' => $numberOfGames,
                        ]);
                } elseif ($existingAssignments->isNotEmpty()) {
                    // No functions selected - update existing records with presence/competition data
                    // but keep a record for presence tracking
                    RefereeFunctionAssignment::where('event_id', $event->id)
                        ->where('referee_enrollment_id', $enrollmentId)
                        ->update([
                            'is_present' => $isPresent,
                            'competition_days' => $competitionDays,
                            'number_of_games' => $numberOfGames,
                        ]);
                } else {
                    // No functions and no existing assignments - create a presence-only record
                    RefereeFunctionAssignment::create([
                        'event_id' => $event->id,
                        'referee_enrollment_id' => $enrollmentId,
                        'is_present' => $isPresent,
                        'competition_days' => $competitionDays,
                        'number_of_games' => $numberOfGames,
                        'assigned_by' => $updatedBy->id,
                    ]);
                }
            }

            DB::commit();

            Log::info('Referee presence, functions and competition data updated', [
                'event_id' => $event->id,
                'updated_by' => $updatedBy->id,
                'count' => count($presenceData),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update referee presence', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
