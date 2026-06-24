<?php

namespace App\Exports;

use Domain\EvtEvents\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventMasterExport implements FromCollection, WithHeadings, WithMapping
{
    protected $events;

    public function __construct()
    {
        // Load events with necessary relationships
        $this->events = Event::with([
            'sport',
            'venueCountry',
            'geoZones',
            'subRegions',
            'countries',
            'organizer.organizable',
            'organizerDetails',
            'competition.sport',
            'competition.types',
            'competition.technicalDelegates.federation',
            'competition.venueCountry',
            'competition.disciplineTemplate.disciplines',
            'competition.antiDopingRecord',
        ])->get();
    }

    public function collection()
    {
        return $this->events;
    }

    public function map($event): array
    {
        return [
            $event->id,
            $event->name,
            \App\Enums\EvtEventCategoryTypeEnum::toString($event->event_category),
            $event->organizer->organizable?->member_code ?? 'N/A',
            isset($event->event_type) ? \App\Enums\EvtEventTypeEnum::toString($event->event_type) : \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type),
            ucfirst($event->stateName()),
            \App\Enums\EvtEventEnrollmentTypeEnum::toString($event->enrollment_type),
            $event->external_url,
            $event->organizerDetails->responsible_person ?? 'N/A',
            $event->organizerDetails->contact_email ?? 'N/A',
            $event->organizerDetails->contact_phone ?? 'N/A',
            $event->organizerDetails->bod_meeting_number ?? 'N/A',
            optional($event->organizerDetails)->date_sending_contract ? $event->organizerDetails->date_sending_contract->format('d-m-Y') : 'N/A',
            optional($event->organizerDetails)->date_sending_invoice ? $event->organizerDetails->date_sending_invoice->format('d-m-Y') : 'N/A',
            optional($event->organizerDetails)->reception_payment_loc ? $event->organizerDetails->reception_payment_loc->format('d-m-Y') : 'N/A',
            optional($event->organizerDetails)->reception_contract_signed ? $event->organizerDetails->reception_contract_signed->format('d-m-Y') : 'N/A',
            optional($event->organizerDetails)->reception_specific_rules ? $event->organizerDetails->reception_specific_rules->format('d-m-Y') : 'N/A',
            $event->competition->number ?? 'N/A',
            $event->competition->sport->name ?? 'N/A',
            \App\Enums\EvtEventTypeEnum::toString(optional($event->competition)->event_type) ?? 'N/A',
            $event->competition?->types->pluck('competition_type')->implode(', ') ?? 'N/A',

            optional($event->competition)->start_date ? $event->competition->start_date->format('d-m-Y') : 'N/A',
            optional($event->competition)->end_date ? $event->competition->end_date->format('d-m-Y') : 'N/A',
            optional($event->competition)->start_registration ? $event->competition->start_registration->format('d-m-Y') : 'N/A',
            optional($event->competition)->end_registration ? $event->competition->end_registration->format('d-m-Y') : 'N/A',

            $event->competition->rounds_total ?? 'N/A',
            $event->competition->cat_age ?? 'N/A',
            $event->competition->cat_competition ?? 'N/A',
            $event->competition->environment ?? 'N/A',
            $event->competition->disciplineTemplate->name ?? 'N/A',
            $event->allow_coach_enrollment ? __('Yes') : __('No'),
            $event->allow_referee_enrollment ? __('Yes') : __('No'),
            $event->allow_individual_enrollment ? __('Yes') : __('No'),
            $event->is_visible ? __('Yes') : __('No'),
            ucfirst($event->stateName()),
            $event->venue ?? 'N/A',
            $event->venue_address ?? 'N/A',
            $event->venue_city ?? 'N/A',
            $event->venueCountry->name ?? 'N/A',
            $event->countries->pluck('name')->implode(', ') ?? 'N/A',
            $event->competition->antiDopingRecord->num_controls_planned ?? 'N/A',
            $event->competition->antiDopingRecord->number_of_controls ?? 'N/A',
            $event->competition->antiDopingRecord->responsible_name ?? 'N/A',
            $event->competition->antiDopingRecord->responsible_email ?? 'N/A',
            $event->competition->antiDopingRecord->responsible_phone ?? 'N/A',
            $event->competition->antiDopingRecord->expected_athletes ?? 'N/A',
            $event->competition->medals_gold ?? 'N/A',
            $event->competition->medals_silver ?? 'N/A',
            $event->competition->medals_bronze ?? 'N/A',
            $event->competition?->technicalDelegates->pluck('name')->implode(', ') ?? 'N/A',
            $event->competition?->technicalDelegates->pluck('member_code_delegate_federation')->implode(', ') ?? 'N/A',
            $event->competition?->technicalDelegates->pluck('federation.name')->implode(', ') ?? 'N/A',
            $event->competition?->technicalDelegates->pluck('appointment_by_bod_number')->implode(', ') ?? 'N/A',
            $event->competition?->technicalDelegates->pluck('date_of_bod_appointment')->map(function ($date) {
                return optional($date)->format('d-m-Y');
            })->implode(', ') ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Category', 'Organizer', 'Type', 'Status', 'Enrollment Type',
            'External Url', 'Responsible Person', 'Contact Email', 'Contact Phone',
            'BoD Meeting Nº', 'Date Sending Contract', 'Date Sending Invoice',
            'Reception Payment LOC', 'Reception Contract Signed', 'Reception Specific Rules',
            'Competition Number', 'Sport', 'Event Type', 'Competition Type',
            'Competition Start Date', 'Competition End Date', 'Registration Start Date',
            'Registration End Date', 'Rounds Total', 'Category Age', 'Competition Category',
            'Environment', 'Discipline ClassGroup', 'Allow Coach Enrollment', 'Allow Referee Enrollment',
            'Allow Individual Enrollment', 'Visible in Listings', 'Event State',
            'Venue Name', 'Venue Address', 'Venue City', 'Venue Country',
            'Country', 'Planned Nº of Controls', 'Number of Controls',
            'Responsible Name (Anti-Doping)', 'Responsible Email (Anti-Doping)',
            'Responsible Phone (Anti-Doping)', 'Expected Athletes', 'Nº Gold',
            'Nº Silver', 'Nº Bronze', 'Technical Delegate Name', 'Technical Delegate Member Code',
            'Technical Delegate Federation', 'Technical Delegate Appointment by BOD Number',
            'Technical Delegate Date of BOD Appointment',
        ];
    }
}
