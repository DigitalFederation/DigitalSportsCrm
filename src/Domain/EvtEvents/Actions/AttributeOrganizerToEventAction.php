<?php

namespace Domain\EvtEvents\Actions;

use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Organizer;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Support\Facades\DB;

class AttributeOrganizerToEventAction
{
    /**
     * Execute the action.
     *
     * @param  string|null  $organizerData  Format: "federation_123" or "entity_456" or null to remove
     */
    public function execute(int $eventId, ?string $organizerData): void
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($eventId);

            // Delete any existing organizers for this event
            Organizer::where('event_id', $event->id)->delete();

            // If no organizer data provided, just remove existing and return
            if (empty($organizerData)) {
                DB::commit();

                return;
            }

            // Parse the organizer data format: "type_id"
            if (! str_contains($organizerData, '_')) {
                // Legacy format - assume it's a federation ID for backward compatibility
                $type = 'federation';
                $id = $organizerData;
            } else {
                [$type, $id] = explode('_', $organizerData, 2);
            }

            // Determine the model class and find the organizable entity
            $organizableType = match ($type) {
                'federation' => Federation::class,
                'entity' => Entity::class,
                default => throw new \InvalidArgumentException("Invalid organizer type: {$type}")
            };

            $organizable = $organizableType::findOrFail($id);

            // Create new organizer with polymorphic relationship
            $organizer = new Organizer([
                'organizable_id' => $organizable->id,
                'organizable_type' => $organizableType,
                'event_id' => $event->id,
            ]);

            $organizer->save();

            DB::commit();

            // Log the activity with appropriate details
            $organizerName = $type === 'federation'
                ? $organizable->member_code
                : $organizable->name;

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'organizer_type' => $type,
                    'organizer_id' => $organizable->id,
                    'event_id' => $event->id,
                ])
                ->log("New {$type} organizer for {$event->name}: {$organizerName}");

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
