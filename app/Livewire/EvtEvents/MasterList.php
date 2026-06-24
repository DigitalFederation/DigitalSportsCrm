<?php

namespace App\Livewire\EvtEvents;

use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class MasterList extends Component
{
    public function render()
    {
        return view('livewire.evt-events.master-list');
    }

    public function getEvents()
    {
        $events = Event::with([
            'sport',
            'venueCountry',
            'geoZones',
            'subRegions',
            'countries',
            'organizer.organizable',
            'organizerDetails',
            'competition.sport',
            'competition.types',
            'competition.antiDopingRecord',
            'competition.technicalDelegates.federation',
            'competition.venueCountry',
            'competition.disciplineTemplate.disciplines',
        ])->get();

        return response()->json($events);
    }

    public function updateEvent($data)
    {
        $validator = Validator::make($data, [
            'id' => 'required|exists:evt_events,id',
            'field' => 'required|string',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::findOrFail($data['id']);
        $field = $data['field'];
        $value = $data['value'];

        $parts = explode('.', $field);
        $modelInstance = $event;

        for ($i = 0; $i < count($parts) - 1; $i++) {
            $relation = $parts[$i];
            $modelInstance = $modelInstance->$relation;
        }

        $finalField = end($parts);
        $modelInstance->$finalField = $value;
        $modelInstance->save();

        // Reload the event with all its relationships
        $updatedEvent = Event::with([
            'sport',
            'venueCountry',
            'geoZones',
            'subRegions',
            'countries',
            'organizer.organizable',
            'organizerDetails',
            'competition.sport',
            'competition.types',
            'competition.antiDopingRecord',
            'competition.technicalDelegates.federation',
            'competition.venueCountry',
            'competition.disciplineTemplate.disciplines',
        ])->find($event->id);

        return response()->json(['success' => true, 'event' => $updatedEvent]);
    }

}
