<div>

    <div x-data="{
        notification: { show: false, type: 'success', message: '' },
        showNotification(type, message) {
            this.notification.type = type;
            this.notification.message = message;
            this.notification.show = true;
            setTimeout(() => this.notification.show = false, 3000); // Hide after 3 seconds
        }
     }"
         x-init="$nextTick(() => window.addEventListener('notify', (event) => showNotification(event.detail.type, event.detail.message)))"
         class="fixed bottom-5 right-5 z-50"
    >
        <div x-show="notification.show"
             x-transition
             :class="{'bg-green-500': notification.type === 'success', 'bg-red-500': notification.type === 'error'}"
             class="px-4 py-2 rounded text-white shadow-lg">
            <span x-text="notification.message"></span>
        </div>
    </div>


    <div id="events-table"></div>


    @push('footer-scripts')

        <script>
            document.addEventListener("livewire:init", function() {
                var table = new Tabulator("#events-table", {
                    ajaxURL: "{{ route('admin.evt-events.events.get') }}",
                    ajaxConfig: {
                        headers: {
                            "X-CSRF-TOKEN": '{{ csrf_token() }}'
                        }
                    },
                    ajaxResponse: function(url, params, response) {
                        if (response && response.data && Array.isArray(response.data)) {
                            return response;
                        } else {
                            console.error("Invalid response format:", response);
                            return [];
                        }
                    },
                    layout: "fitDataFill",
                    pagination: true,
                    paginationMode: "remote",
                    paginationSize: 50,
                    paginationSizeSelector: [10, 50, 100, 200],
                    ajaxParams: {
                        size: 50
                    },
                    paginationInitialPage: 1,
                    height: "700px",  // Set a fixed height to enable vertical scrolling

                    movableColumns: false,  // Allow column reordering
                    resizableColumns: true,  // Allow column resizing
                    columns: [
                        { title: "Event Name", field: "name", editor: "input", width: 300, frozen: true },
                        {
                            title: "Category", field: "event_category", editor: "select", editorParams: {
                                values: { "competition": "Competition", "organization": "Organization" }
                            }
                        },
                        { title: "Organizer", field: "organizer.organizable.member_code", editor: "input" },
                        {
                            title: "Type", field: "event_type", editor: "select", editorParams: {
                                values: {
                                    "Clubs Competition": "Clubs Competition",
                                    "National Team Competition": "National Team Competition",
                                    "Open Competition": "Open Competition"
                                }
                            }
                        },
                        {
                            title: "Status", field: "status_class", editor: "select", editorParams: {
                                values: {
                                    "Domain\\EvtEvents\\States\\ActiveEventState": "Active",
                                    "Domain\\EvtEvents\\States\\ArchiveEventState": "Archive",
                                    "Domain\\EvtEvents\\States\\PreparationEventState": "Preparation",
                                    "Domain\\EvtEvents\\States\\CanceledEventState": "Canceled"
                                }
                            }
                        },
                        {
                            title: "Enrollment Type", field: "enrollment_type", editor: "select", editorParams: {
                                values: {
                                    "only_federations": "Only Federations",
                                    "only_entities": "Only Clubs",
                                    "only_individuals": "Only Individuals",
                                    "only_federations_and_entities": "Only Federations and Clubs",
                                    "all": "Federations, Clubs and Individuals",
                                    "only_federations_and_individuals": "Only Federations and Individuals"
                                }
                            }
                        },
                        { title: "External Url", field: "external_url", editor: "input" },
                        { title: "Responsible Person", field: "organizerDetails.responsible_person", editor: "input" },
                        { title: "Contact Email", field: "organizerDetails.contact_email", editor: "input" },
                        { title: "Contact Phone", field: "organizerDetails.contact_phone", editor: "input" },
                        { title: "BoD Meeting Nº", field: "organizerDetails.bod_meeting_number", editor: "input" },
                        {
                            title: "Date Sending Contract",
                            field: "organizerDetails.date_sending_contract",
                            editor: "date"
                        },
                        {
                            title: "Date Sending Invoice",
                            field: "organizerDetails.date_sending_invoice",
                            editor: "date"
                        },
                        {
                            title: "Reception Payment LOC",
                            field: "organizerDetails.reception_payment_loc",
                            editor: "date"
                        },
                        {
                            title: "Reception Contract Signed",
                            field: "organizerDetails.reception_contract_signed",
                            editor: "date"
                        },
                        {
                            title: "Reception Specific Rules",
                            field: "organizerDetails.reception_specific_rules",
                            editor: "date"
                        },
                        { title: "Competition Number", field: "competition.number", editor: "input" },
                        { title: "Sport", field: "competition.sport.name", editor: "input" },
                        {
                            title: "Event Type", field: "competition.event_type", editor: "select", editorParams: {
                                values: {
                                    "Clubs Competition": "Clubs Competition",
                                    "National Team Competition": "National Team Competition",
                                    "Open Competition": "Open Competition"
                                }
                            }
                        },
                        {
                            title: "Competition Type", field: "competition.types", editor: "select", editorParams: {
                                values: {
                                    "WorldChampionships": "World Championships",
                                    "WorldCup": "World Cup",
                                    "WorldSeries": "World Series",
                                    "WorldRecordAttempt": "World Record Attempt",
                                    "ContinentalChampionships": "Continental Championships",
                                    "Bi-ContinentalChampionship": "Bi-Continental Championship",
                                    "Bi-ContinentalCup": "Bi-Continental Cup",
                                    "ContinentalZoneChampionships": "Continental Zone Championships",
                                    "AfricanGames": "African Games",
                                    "AfricanChampionship": "African Championship",
                                    "AfricanCup": "African Cup",
                                    "PanAmericanGames": "Pan American Games",
                                    "AmericanGames": "American Games",
                                    "AmericanChampionship": "American Championship",
                                    "AmericanCup": "American Cup",
                                    "AsianGames": "Asian Games",
                                    "AsianChampionship": "Asian Championship",
                                    "AsianCup": "Asian Cup",
                                    "SouthEastAsianGames": "South East Asian Games",
                                    "EuropeanGames": "European Games",
                                    "EuropeanChampionships": "European Championships",
                                    "EuropeanCup": "European Cup",
                                    "MediterraneanGames": "Mediterranean Games",
                                    "PacificGames": "Pacific Games",
                                    "OceaniaGames": "Oceania Games",
                                    "OceaniaChampionships": "Oceania Championships",
                                    "OceaniaCup": "Oceania Cup",
                                    "WorldUniversityGames": "World University Games",
                                    "WorldUniversityChampionships": "World University Championships",
                                    "UniversityWorldCup": "University World Cup",
                                    "BeachGames": "Beach Games",
                                    "InternationalMeeting": "International Meeting",
                                    "NationalCompetitions": "National Competitions",
                                    "OtherInternationalEvents": "Other International Events"
                                }
                            }
                        },
                        { title: "Competition Start Date", field: "competition.start_date", editor: "date" },
                        { title: "Competition End Date", field: "competition.end_date", editor: "date" },
                        { title: "Registration Start Date", field: "competition.start_registration", editor: "date" },
                        { title: "Registration End Date", field: "competition.end_registration", editor: "date" },
                        { title: "Rounds Total", field: "competition.rounds_total", editor: "number" },
                        { title: "Category Age", field: "competition.cat_age", editor: "input" },
                        { title: "Competition Category", field: "competition.cat_competition", editor: "input" },
                        { title: "Environment", field: "competition.environment", editor: "input" },
                        {
                            title: "Discipline ClassGroup",
                            field: "competition.disciplineTemplate.name",
                            editor: "input"
                        },
                        { title: "Allow Coach Enrollment", field: "allow_coach_enrollment", editor: "tickCross" },
                        { title: "Allow Referee Enrollment", field: "allow_referee_enrollment", editor: "tickCross" },
                        {
                            title: "Allow Individual Enrollment",
                            field: "allow_individual_enrollment",
                            editor: "tickCross"
                        },
                        { title: "Visible in Listings", field: "is_visible", editor: "tickCross" },
                        {
                            title: "Event State", field: "status_class", editor: "select", editorParams: {
                                values: {
                                    "Domain\\EvtEvents\\States\\ActiveEventState": "Active",
                                    "Domain\\EvtEvents\\States\\ArchiveEventState": "Archive",
                                    "Domain\\EvtEvents\\States\\PreparationEventState": "Preparation",
                                    "Domain\\EvtEvents\\States\\CanceledEventState": "Canceled"
                                }
                            }
                        },
                        { title: "Venue Name", field: "venue", editor: "input" },
                        { title: "Venue Address", field: "venue_address", editor: "input" },
                        { title: "Venue City", field: "venue_city", editor: "input" },
                        { title: "Venue Country", field: "venueCountry.name", editor: "input" },
                        { title: "Country", field: "countries", formatter: "list" },
                        {
                            title: "Planned Nº of Controls",
                            field: "competition.antiDopingRecord.num_controls_planned",
                            editor: "number"
                        },
                        {
                            title: "Number of Controls",
                            field: "competition.antiDopingRecord.number_of_controls",
                            editor: "number"
                        },
                        {
                            title: "Responsible Name (Anti-Doping)",
                            field: "competition.antiDopingRecord.responsible_name",
                            editor: "input"
                        },
                        {
                            title: "Responsible Email (Anti-Doping)",
                            field: "competition.antiDopingRecord.responsible_email",
                            editor: "input"
                        },
                        {
                            title: "Responsible Phone (Anti-Doping)",
                            field: "competition.antiDopingRecord.responsible_phone",
                            editor: "input"
                        },
                        {
                            title: "Expected Athletes",
                            field: "competition.antiDopingRecord.expected_athletes",
                            editor: "number"
                        },
                        { title: "Nº Gold", field: "competition.medals_gold", editor: "number" },
                        { title: "Nº Silver", field: "competition.medals_silver", editor: "number" },
                        { title: "Nº Bronze", field: "competition.medals_bronze", editor: "number" },
                        {
                            title: "Technical Delegate Name",
                            field: "competition.technicalDelegates.0.name",
                            editor: "input"
                        },
                        {
                            title: "{{ __('certifications.member_code') }}",
                            field: "competition.technicalDelegates.0.member_code_delegate_federation",
                            editor: "input"
                        },
                        {
                            title: "Technical Delegate Federation",
                            field: "competition.technicalDelegates.0.federation.name",
                            editor: "input"
                        },
                        {
                            title: "Technical Delegate Appointment by BOD Number",
                            field: "competition.technicalDelegates.0.appointment_by_bod_number",
                            editor: "input"
                        },
                        {
                            title: "Technical Delegate Date of BOD Appointment",
                            field: "competition.technicalDelegates.0.date_of_bod_appointment",
                            editor: "date"
                        }
                    ],
                    columnDefaults: {
                        minWidth: 100,  // Set a minimum width for all columns
                        resizable: true  // Allow all columns to be resized
                    }

                });
                table.on("cellEdited", function(cell) {
                    console.log("cellEdited callback triggered");
                    var data = {
                        id: cell.getRow().getData().id,
                        field: cell.getColumn().getField(),
                        value: cell.getValue()
                    };


                    // Call the Livewire method to update the event
                    @this.
                    call("updateEvent", data)
                        .then(response => {
                            console.log("Livewire response:", response);

                            // Check if the response has a success property
                            if (response && response.original && response.original.success) {
                                // Trigger the Alpine notification for success
                                window.dispatchEvent(new CustomEvent("notify", {
                                    detail: { type: "success", message: "Updated successfully" }
                                }));
                            } else {
                                // Trigger the Alpine notification for failure
                                window.dispatchEvent(new CustomEvent("notify", {
                                    detail: { type: "error", message: "Update failed" }
                                }));
                                // Restore the cell to its old value
                                cell.restoreOldValue();
                            }
                        })
                        .catch(error => {
                            console.error("Error in Livewire call:", error);

                            // Trigger the Alpine notification for error
                            window.dispatchEvent(new CustomEvent("notify", {
                                detail: { type: "error", message: "An error occurred" }
                            }));
                            // Restore the cell to its old value
                            cell.restoreOldValue();
                        });
                });
            });
        </script>
    @endpush
</div>
