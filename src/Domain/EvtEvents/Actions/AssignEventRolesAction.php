<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignEventRolesAction
{
    /**
     * Assign or update event roles (Technical Delegate, Chief Judge, Competition Director)
     *
     * @param  array  $roles  Array with keys: technical_delegate_id, chief_judge_id, competition_director_id
     */
    public function execute(Event $event, array $roles): void
    {
        DB::beginTransaction();

        try {
            // Process each role type
            $roleMapping = [
                'technical_delegate_id' => EventRole::ROLE_TECHNICAL_DELEGATE,
                'chief_judge_id' => EventRole::ROLE_CHIEF_JUDGE,
                'competition_director_id' => EventRole::ROLE_COMPETITION_DIRECTOR,
            ];

            foreach ($roleMapping as $inputKey => $roleType) {
                if (! array_key_exists($inputKey, $roles)) {
                    continue;
                }

                $individualId = $roles[$inputKey];

                if (empty($individualId)) {
                    // Remove the role if no individual is selected
                    EventRole::where('event_id', $event->id)
                        ->where('role', $roleType)
                        ->delete();
                } else {
                    // Check if this individual already has a different role in this event
                    $existingRole = EventRole::where('event_id', $event->id)
                        ->where('individual_id', $individualId)
                        ->where('role', '!=', $roleType)
                        ->first();

                    if ($existingRole) {
                        throw new \Exception("Individual already has the role of {$existingRole->role_name} in this event.");
                    }

                    // Update or create the role assignment
                    EventRole::updateOrCreate(
                        [
                            'event_id' => $event->id,
                            'role' => $roleType,
                        ],
                        [
                            'individual_id' => $individualId,
                            'notes' => $roles['notes'][$roleType] ?? null,
                        ]
                    );
                }
            }

            DB::commit();

            Log::info('Event roles assigned successfully', [
                'event_id' => $event->id,
                'roles' => $roles,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign event roles', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
