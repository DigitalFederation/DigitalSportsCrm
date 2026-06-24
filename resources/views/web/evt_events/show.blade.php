@section('title', __('events.event_details'))
<x-layout-full>
    <div class="previous-layout-classes relative">

        <!-- Page header -->
        <div
            class="h-80 items-center flex flex-col justify-center bg-cover bg-center relative bg-zoom-in zoom-in"
            style="background-image: url('{{ $event->heroImage }}');">
            <!-- Overlay: Color Blue with Opacity -->
            <div class="absolute top-0 left-0 right-0 bottom-0 bg-blue-500/50 h-80"></div>
            <!-- Left: Title -->
            <div class="relative mb-4 sm:mb-0 flex flex-col justify-center text-center mx-auto">
                <h1 class="text-4xl md:text-6xl text-white font-bold subpixel-antialiased drop-shadow-lg">
                    @if(!$event->name)
                        {{ __('events.event_details') }}
                    @else
                        {{$event->name}}
                    @endif
                </h1>
            </div>


        </div>

        <div class="page-wrapper -mt-20 md:-mt-24">

            <div class="flex flex-col md:flex-row gap-x-4 mb-4 z-20">

                <x-evt_event.block-event-details :event="$event" :is-organizer="$isOrganizer" />

            </div>

            <div class="flex flex-col md:flex-row gap-x-4 mb-4">

                <!-- Event Location -->
                <x-evt_event.block-event-location :event="$event" />


                <!-- Technical Delegate -->
                <x-evt_event.block-technical-delegate :event="$event" />


                <!-- Event LOC -->
                <x-evt_event.block-event-loc :event="$event" />

                <!-- For organization only -->
                @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::organization->value)
                    <x-evt_event.block-event-pricing-details :event="$event" />
                @endif

            </div>

            <!-- Event Attachments -->
            <x-evt_event.block-event-attachments :event="$event" :attachments="$attachments" />


            <!-- Referees -->
            @if(!empty($referees) && $referees->count() > 0)
                <div class="card w-full mb-4">
                    <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">
                        <span class="font-bold">{{ __('events.referees') }}</span>
                    </div>

                    <x-dynamic-table
                        :displayable-headers="false"
                        :headers="['Name', __('certifications.member_code')]">

                        @foreach($referees as $referee)
                            <tr class="hover:bg-gray-100">
                                <td class="py-1 text-sm">{{ $referee->individual->full_name }}</td>
                                <td class="py-1 text-sm text-right"><span
                                        class="font-bold">Code: </span>{{ $referee->individual->member_code }}</td>
                            </tr>
                        @endforeach

                    </x-dynamic-table>
                </div>
            @endif


            <x-evt_event.block-event-individual-enrollments :event="$event"
                                                            :federationIndividualEnrollments="$federationIndividualEnrollments" />

            @if ($event->allowsEnrollments())
                <div class="flex flex-col md:flex-row gap-x-4 mb-4 z-20">
                    <x-evt_event.block-event-registration :event="$event" :is-entity="$isEntity" :has-own-athlete-enrollments="$hasOwnAthleteEnrollments" />
                </div>
            @endif

            <div class="flex flex-col md:flex-row gap-x-4 mb-4 z-20">
                <x-evt_event.block-event-registration-organizer :event="$event" :is-organizer="$isOrganizer" />
            </div>

        </div>

    </div>

    <style>
        /* CSS animation for zooming effect */
        @keyframes zoomIn {
            0% {
                transform: scale(1.1); /* Final scale */
            }
            100% {
                transform: scale(1); /* Initial scale */
            }
        }

        /* Apply animation to the background image */
        .bg-cover.zoom-in {
            transform-origin: center;
            animation: zoomIn 1s ease forwards;
        }
    </style>
</x-layout-full>
