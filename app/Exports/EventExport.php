<?php

namespace App\Exports;

use Domain\EvtEvents\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventExport implements FromCollection, WithHeadings, WithMapping
{
    protected $headings;

    protected $eventId;

    protected $events;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
        $this->events = Event::where('id', $this->eventId)
            ->with([
                'competitions',
                'competitions.antiDopingRecords',
                'competitions.technicalDelegates',
                'geographicLimitations',
            ])->get();

        // Determine the type of events in collection to set headings
        if ($this->events->first()->isSportEvent()) {
            $this->headings = $this->getSportEventHeadings();
        } else {
            $this->headings = $this->getOrganizationEventHeadings();
        }

    }

    public function collection()
    {
        return $this->events;
    }

    public function map($event): array
    {
        $baseData = [
            $event->id,
            $event->name,
            $event->event_type,
            $event->event_scope,
            $event->organization_type,
            $event->type,
            $event->location,
            $event->address,
            $event->start_date,
            $event->end_date,
            $event->start_registration,
            $event->end_registration,
        ];

        if ($event->isOrganizationEvent()) {
            return array_merge($baseData, [
                // ... Additional fields for 'Organization' type
            ]);
        }

        if ($event->isSportEvent()) {
            $competitionData = $event->competitions->map(function ($competition) {
                return [
                    $competition->year,
                    $competition->month,
                    $competition->number,
                    $competition->sport_id,
                    $competition->rounds_total,
                    $competition->competition_type,
                    $competition->cat_age,
                    $competition->cat_competition,
                    $competition->environment,
                    $competition->full_name,
                    $competition->status_class,
                    $competition->venue,
                    $competition->venue_address,
                    $competition->start_date,
                    $competition->end_date,
                ];
            })->first();

            if ($competitionData !== null) {
                return array_merge($baseData, $competitionData);
            }
        }

        return $baseData;  // Fallback
    }

    private function getOrganizationEventHeadings(): array
    {
        return $this->getCommonHeadings();
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function getSportEventHeadings(): array
    {
        return array_merge(
            $this->getCommonHeadings(),
            [
                'Competition Year',
                'Competition Month',
                'Competition Number',
                'Competition Sport ID',
                'Competition Rounds Total',
                'Competition Type',
                'Competition Cat Age',
                'Competition Cat Competition',
                'Competition Environment',
                'Competition Full Name',
                'Competition Status Class',
                'Competition Venue',
                'Competition Venue Address',
                'Competition Start Date',
                'Competition End Date',
            ],
        );

    }

    private function getCommonHeadings(): array
    {
        return [
            'ID',
            'Name',
            'Event Type',
            'Event Scope',
            'Organization Type',
            'Type',
            'Location',
            'Address',
            'Start Date',
            'End Date',
            'Start Registration',
            'End Registration',
        ];
    }
}
