<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class GetDisciplinesFromEventAction
{
    public function execute(Event $event): array
    {
        // Get disciplines collection or empty collection if none exist
        $disciplines = $event->competition?->disciplineTemplate?->disciplines()
            ->with('attributes')->get() ?? collect();

        // Return structured array with disciplines and filter options
        return [
            'disciplines' => $disciplines,
            'has_individual' => $disciplines->contains('enrollment_type', 'individual'),
            'has_relay' => $disciplines->contains('enrollment_type', 'relay'),
            'has_male' => $disciplines->contains('gender', 'male'),
            'has_female' => $disciplines->contains('gender', 'female'),
            'has_mixed' => $disciplines->contains('gender', 'mixed'),
            'styles' => $disciplines->pluck('style')->unique()->filter()->values(),
            'distances' => $disciplines->pluck('distance')->unique()->filter()->values(),
        ];
    }

    /**
     * Deprecated
     * Used to fetch only the discipline
     */
    public function deprecated_execute(Event $event): Collection
    {
        // Check if the event has a related competition
        if (! $event->competition) {
            // Return an empty Eloquent collection if no competition is associated
            return new Collection;
        }
        // Retrieve the discipline template via the competition model
        $template = $event->competition->load('disciplineTemplate')->disciplineTemplate;
        // Return an empty Eloquent collection if the discipline template is not set
        if (! $template) {
            return new Collection;
        }
        // Ensure that the disciplines are loaded by explicitly loading them
        $template->load('disciplines');

        // Fetch and return disciplines related to the template, including their attributes
        return $template->disciplines()->with('attributes')->get();
    }
}
