<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Event;

class GetAttributesAndRulesFromRolesAction
{
    /**
     * Get attributes and rules for a role, optionally filtered by event.
     *
     * @param  string  $role  The enrollment role type
     * @param  int|null  $eventId  Optional event ID to filter attributes
     * @return array Attributes and their rules
     */
    public function execute(string $role, ?int $eventId = null): array
    {
        // If an event ID is provided, get attributes from event-specific pivot tables
        if ($eventId) {
            $event = Event::find($eventId);

            if ($event) {
                $roleAttributes = $this->getEventAttributesForRole($event, $role);

                if ($roleAttributes->isNotEmpty()) {
                    return $this->formatAttributes($roleAttributes);
                }
            }
        }

        // Fallback: query global attributes by enrollment_type
        $roleAttributes = Attribute::with(['rules'])
            ->where('enrollment_type', $role)
            ->get();

        return $this->formatAttributes($roleAttributes);
    }

    /**
     * Get event-specific attributes for a role using the correct pivot table.
     */
    protected function getEventAttributesForRole(Event $event, string $role)
    {
        return match (strtoupper($role)) {
            'COACH' => $event->coachAttributes()->with('rules')->get(),
            'REFEREE', 'TECHNICAL_OFFICIAL' => $event->refereeAttributes()->with('rules')->get(),
            'OFFICIAL' => $event->officialAttributes()->with('rules')->get(),
            default => collect(),
        };
    }

    /**
     * Format attributes into the expected array structure.
     */
    protected function formatAttributes($roleAttributes): array
    {
        if ($roleAttributes->isEmpty()) {
            return [];
        }

        return $roleAttributes->mapWithKeys(function ($attribute) {
            return [
                $attribute->name => [
                    'attribute_data' => $attribute,
                    'rules' => $attribute->rules,
                ],
            ];
        })->toArray();
    }
}
