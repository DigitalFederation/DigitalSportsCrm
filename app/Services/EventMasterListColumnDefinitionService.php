<?php

namespace App\Services;

use App\Enums\EvtCompetitionEnvironmentEnum;
use App\Enums\EvtEventTypeEnum;
use Domain\EvtEvents\Models\Event;

class EventMasterListColumnDefinitionService
{
    public function getColumnDefinitions(): array
    {
        return [
            [
                'title' => 'Event',
                'columns' => [
                    [
                        'title' => 'Event Name',
                        'field' => 'name',
                        'editor' => 'input',
                        'width' => 300,
                    ],
                ],
                'frozen' => true,
            ],
            [
                'title' => 'Event Information',
                'columns' => [
                    [
                        'title' => 'Competition Number',
                        'field' => 'competition.number',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'External URL',
                        'field' => 'external_url',
                        'editor' => 'input',
                        'width' => 200,
                    ],
                    [
                        'title' => 'Event Type',
                        'field' => 'event_type',
                        'editor' => 'list',
                        'editorParams' => [
                            'values' => array_reduce(
                                EvtEventTypeEnum::cases(),
                                function ($carry, $case) {
                                    $carry[$case->name] = $case->name; // Use the value directly

                                    return $carry;
                                },
                                []
                            ),
                        ],
                    ],
                    [
                        'title' => 'Competition Type',
                        'field' => 'competition.types_names',
                        'editor' => false,

                    ],
                    [
                        'title' => 'Sport',
                        'field' => 'competition.sport.name',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Environment',
                        'field' => 'competition.environment',
                        'editor' => 'list',
                        'editorParams' => [
                            'values' => array_reduce(
                                EvtCompetitionEnvironmentEnum::cases(),
                                function ($carry, $case) {
                                    $carry[$case->name] = $case->name; // Use the value directly

                                    return $carry;
                                },
                                []
                            ),
                        ],
                    ],
                    [
                        'title' => 'Rounds Total',
                        'field' => 'competition.rounds_total',
                        'editor' => 'number',
                    ],
                    [
                        'title' => 'Category Age',
                        'field' => 'competition.cat_age',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Competition Category',
                        'field' => 'competition.cat_competition',
                        'editor' => 'input',
                    ],
                ],
            ],
            [
                'title' => 'Competition Dates',
                'columns' => [
                    [
                        'title' => 'Competition Start Date',
                        'field' => 'competition.start_date',
                        'editor' => 'date',
                    ],
                    [
                        'title' => 'Competition End Date',
                        'field' => 'competition.end_date',
                        'editor' => 'date',
                    ],
                    [
                        'title' => 'Registration Start Date',
                        'field' => 'start_registration',
                        'editor' => 'date',
                    ],
                    [
                        'title' => 'Registration End Date',
                        'field' => 'end_registration',
                        'editor' => 'date',

                    ],
                ],
            ],

            [
                'title' => 'Competition Medals',
                'columns' => [
                    [
                        'title' => 'Nº Gold',
                        'field' => 'competition.medals_gold',
                        'editor' => 'number',
                    ],
                    [
                        'title' => 'Nº Silver',
                        'field' => 'competition.medals_silver',
                        'editor' => 'number',
                    ],
                    [
                        'title' => 'Nº Bronze',
                        'field' => 'competition.medals_bronze',
                        'editor' => 'number',
                    ],
                    [
                        'title' => 'Type of Medals',
                        'field' => 'competition.medals_type',
                        'editor' => 'input',
                    ],
                ],
            ],
            [
                'title' => 'Event Status',
                'columns' => [
                    [
                        'title' => 'Visible in Listings',
                        'field' => 'is_visible',
                        'editor' => 'tickCross',
                        'formatter' => 'tickCross',
                    ],
                    [
                        'title' => 'Event State',
                        'field' => 'status_class',
                        'editor' => 'list',
                        'editorParams' => [
                            'values' => Event::availableStates(),
                        ],
                    ],
                    [
                        'title' => 'Organizer',
                        'field' => 'organizer.organizable.member_code',
                        'editor' => 'input',
                    ],
                ],
            ],
            [
                'title' => 'Geography',
                'columns' => [
                    [
                        'title' => 'Geographical Coverage',
                        'field' => 'event_geographical_coverage',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Geo Zone',
                        'field' => 'geoZones',
                        'formatter' => 'list',
                    ],
                    [
                        'title' => 'Country',
                        'field' => 'countries',
                        'formatter' => 'list',
                    ],
                ],
            ],
            [
                'title' => 'Venue Information',
                'columns' => [
                    [
                        'title' => 'Venue Name',
                        'field' => 'venue',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Venue Address',
                        'field' => 'venue_address',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Venue City',
                        'field' => 'venue_city',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Venue Country',
                        'field' => 'venue_country.name',
                        'editor' => 'input',
                    ],
                ],
            ],
            [
                'title' => 'LOC Details',
                'columns' => [
                    [
                        'title' => 'Responsible Person',
                        'field' => 'organizer_details.responsible_person',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Contact Email',
                        'field' => 'organizer_details.email_contact',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Contact Phone',
                        'field' => 'organizer_details.phone_contact',
                        'editor' => 'input',
                    ],

                ],
            ],
            [
                'title' => 'Loc Contract & Rules',
                'columns' => [
                    [
                        'title' => 'BoD Meeting Nº',
                        'field' => 'organizer_details.bod_meeting_no',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Date Sending Contract and Invoice',
                        'field' => 'organizer_details.date_sending_contract',
                        'editor' => 'date',
                    ],
                    [
                        'title' => 'Date Reception Contract Signed',
                        'field' => 'organizer_details.date_reception_contract_signed',
                        'editor' => 'date',
                    ],
                    [
                        'title' => 'Date Reception Payment LOC',
                        'field' => 'organizer_details.date_reception_payment_loc',
                        'editor' => 'date',
                    ],
                    [
                        'title' => 'Date Reception Specific Rules',
                        'field' => 'organizer_details.date_reception_specific_rules',
                        'editor' => 'date',
                    ],
                ],
            ],
            [
                'title' => 'Enrollment Settings',
                'columns' => [
                    [
                        'title' => 'Enrollment Type',
                        'field' => 'enrollment_type',
                        'editor' => 'list',
                        'editorParams' => [
                            'values' => [
                                'only_federations' => 'Only Federations',
                                'only_entities' => 'Only Clubs',
                                'only_individuals' => 'Only Individuals',
                                'only_federations_and_entities' => 'Only Federations and Clubs',
                                'all' => 'Federations, Clubs and Individuals',
                                'only_federations_and_individuals' => 'Only Federations and Individuals',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Allow Coach Enrollment',
                        'field' => 'allow_coach_enrollment',
                        'editor' => 'tickCross',
                        'formatter' => 'tickCross',
                    ],
                    [
                        'title' => 'Allow Referee Enrollment',
                        'field' => 'allow_referee_enrollment',
                        'editor' => 'tickCross',
                        'formatter' => 'tickCross',
                    ],
                    [
                        'title' => 'Allow Individual Enrollment',
                        'field' => 'allow_individual_enrollment',
                        'editor' => 'tickCross',
                        'formatter' => 'tickCross',
                    ],
                ],
            ],
            [
                'title' => 'Technical Delegate',
                'columns' => [
                    [
                        'title' => 'Delegate Name',
                        'field' => 'competition.technical_delegates.0.name',
                        'editor' => 'input',
                        'formatter' => function ($cell, $formatterParams, $onRendered) {
                            $delegates = $cell->getValue();
                            if (is_array($delegates) && ! empty($delegates)) {
                                return $delegates[0]['name'] ?? '';
                            }

                            return '';
                        },
                    ],
                    [
                        'title' => 'International Code',
                        'field' => 'competition.technical_delegates.0.member_code_delegate_federation',
                        'editor' => 'input',
                        'formatter' => function ($cell, $formatterParams, $onRendered) {
                            $delegates = $cell->getValue();
                            if (is_array($delegates) && ! empty($delegates)) {
                                return $delegates[0]['member_code_delegate_federation'] ?? '';
                            }

                            return '';
                        },
                    ],
                    [
                        'title' => 'Appointment BoD Number',
                        'field' => 'competition.technical_delegates.0.appointment_by_bod_number',
                        'editor' => 'input',
                        'formatter' => function ($cell, $formatterParams, $onRendered) {
                            $delegates = $cell->getValue();
                            if (is_array($delegates) && ! empty($delegates)) {
                                return $delegates[0]['appointment_by_bod_number'] ?? '';
                            }

                            return '';
                        },
                    ],
                    [
                        'title' => 'Date of BoD Appointment',
                        'field' => 'competition.technical_delegates.0.date_of_bod_appointment',
                        'editor' => 'date',
                        'formatter' => function ($cell, $formatterParams, $onRendered) {
                            $delegates = $cell->getValue();
                            if (is_array($delegates) && ! empty($delegates)) {
                                return $delegates[0]['date_of_bod_appointment'] ?? '';
                            }

                            return '';
                        },
                    ],
                    [
                        'title' => 'Date of Report Reception',
                        'field' => 'competition.technical_delegates.0.date_of_report_reception',
                        'editor' => 'date',
                        'formatter' => function ($cell, $formatterParams, $onRendered) {
                            $delegates = $cell->getValue();
                            if (is_array($delegates) && ! empty($delegates)) {
                                return $delegates[0]['date_of_report_reception'] ?? '';
                            }

                            return '';
                        },
                    ],
                    [
                        'title' => 'Remarks',
                        'field' => 'competition.technical_delegates.0.remarks',
                        'editor' => 'textarea',
                        'formatter' => function ($cell, $formatterParams, $onRendered) {
                            $delegates = $cell->getValue();
                            if (is_array($delegates) && ! empty($delegates)) {
                                return $delegates[0]['remarks'] ?? '';
                            }

                            return '';
                        },
                    ],
                ],
            ],
            [
                'title' => 'Anti-Doping Information',
                'columns' => [
                    [
                        'title' => 'Nº of Doping Tests Planned',
                        'field' => 'competition.anti_doping_record.num_controls_planned',
                        'editor' => 'number',
                    ],
                    [
                        'title' => 'Number of Controls',
                        'field' => 'competition.anti_doping_record.number_of_controls',
                        'editor' => 'number',
                    ],
                    [
                        'title' => 'Responsible Name (Anti-Doping)',
                        'field' => 'competition.anti_doping_record.responsible_name',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Responsible Phone (Anti-Doping)',
                        'field' => 'competition.anti_doping_record.responsible_phone',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Email (Anti-Doping)',
                        'field' => 'competition.anti_doping_record.responsible_email',
                        'editor' => 'input',
                    ],
                    [
                        'title' => 'Expected Athletes',
                        'field' => 'competition.anti_doping_record.expected_athletes',
                        'editor' => 'number',
                    ],
                ],
            ],
            [
                'title' => 'Broadcast',
                'columns' => [
                    [
                        'title' => 'Broadcast',
                        'field' => 'broadcast',
                        'editor' => 'tickCross',
                    ],
                    [
                        'title' => 'Broadcast information',
                        'field' => 'broadcast_information',
                        'editor' => 'input',
                    ],
                ],
            ],
        ];
    }
}
