@php
    //Define the competition variable
   $competition = !empty($event->competitions->first())? $event->competitions->first() : null;
@endphp


<div class="flex md:flex-row flex-col w-full">
    <div
        class="md:w-1/3 w-full z-10 md:rounded-l rounded-t md:rounded-t-none md:rounded-tl"
        style="background-color: #192f45">
        <!-- Placeholder image -->
        <a href="{{ route('entity.evt-events.events.show', ['event' =>$event->id]) }}">
            @if($event->featured_image)
                <img class="h-full object-none object-center  rounded-t md:rounded-l"
                     src="{{ asset('storage/' . $event->featured_image) }}"
                     alt="">
            @else
                @if(!empty($competition))
                    <img class="h-full object-none object-center rounded-t md:rounded-l"
                         src="{{ asset('img/placeholder_event_competition.png') }}"
                         alt="CMAS Event">
                @else
                    <img class="h-full object-none object-center rounded-t md:rounded-l"
                         src="{{ asset('img/placeholder_event_organization.png') }}"
                         alt="CMAS Event">
                @endif

            @endif
        </a>


    </div>

    <div class="card w-full rounded-t-none md:rounded-l-none md:rounded-tr-md">

        <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">

            <div class="flex gap-x-2 items-center">
                <x-svg.passport class="w-6 h-6 text-slate-600" />
                <span class="font-bold">{{ __('events.event_information') }}</span>
            </div>


            <div class="flex flex-col md:flex-row gap-x-2">

                <div>
                    @if(!Auth::user()->isAdmin())
                        <div x-data="{ openEnrollmentMenu: false }" @click.away="openEnrollmentMenu = false"
                             class="relative">
                            <button @click="openEnrollmentMenu = !openEnrollmentMenu" id="dropdownEnrollmentsButton"
                                    class="btn btn-info" type="button">
                                <span>{{ __('events.my_enrollments') }}</span>
                                <svg class="w-2.5 h-2.5 ml-3" aria-hidden="true" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="m1 1 4 4 4-4" />
                                </svg>
                            </button>

                            <!-- Dropdown menu -->
                            <div x-cloak x-show="openEnrollmentMenu" id="dropdownEnrollmentsMenu"
                                 class="absolute z-50 bg-white divide-y divide-gray-100 rounded-md shadow w-44 dark:bg-gray-700 mt-1"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95">
                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                    aria-labelledby="dropdownEnrollmentsButton">
                                    @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value)
                                        <li>
                                            <a
                                                href="{{ route('entity.evt-events.events.athlete-enrollment.index', ['event'=> $event->id]) }}"
                                                target="_blank"
                                                class="px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2 items-center">
                                                <span>{{ __('events.athletes') }}</span>
                                                <x-svg.box-arrow-up-right class="w-3 h-3 text-slate-400" />
                                            </a>
                                        </li>
                                        <li>
                                            <a
                                                href="{{ route('entity.evt-events.events.coach-enrollment.index', $event->id) }}"
                                                target="_blank"
                                                class="px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                                <span>{{ __('events.coaches') }}</span>
                                                <x-svg.box-arrow-up-right class="w-3 h-3 text-slate-400" />
                                            </a>
                                        </li>
                                    @endif
                                    @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::organization->value)
                                        <li>
                                            <a
                                                href="{{ route('entity.evt-events.events.individual-enrollment.index', ['event'=> $event->id]) }}"
                                                target="_blank"
                                                class="px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2 items-center">
                                                <span>{{ __('events.members') }}</span>
                                                <x-svg.box-arrow-up-right class="w-3 h-3 text-slate-400" />
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-2">


            <!-- Event Status -->
            <div class="flex flex-col gap-x-2 my-3">
                <span class="text-slate-400 text-sm">{{ __('events.event_status') }}</span>
                <span class="text-base text-slate-600">
                    <x-tables.badge :status="ucfirst($event->stateName())"
                                    :color="$event->stateColor()" /></span>
            </div>

            <!-- Website -->
            @if(isset($event->external_url))
                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.website') }}</span>
                    <div class="text-base text-slate-600">
                        <a class="hover:text-slate-400 flex items-center gap-x-2"
                           href="{{ $event->external_url }}"
                           target="_blank">

                            <span> <x-svg.box-arrow-up-right class="w-3 h-3"></x-svg.box-arrow-up-right> </span>
                            <span>{{ __('events.open_link') }}</span>
                        </a>
                    </div>
                </div>
            @endif


            <!-- Event Sport -->
            @if(!empty($competition))
                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.sport') }}</span>
                    <span class="text-base text-slate-600">{{ optional($competition->sport)->translated_name }}</span>
                </div>
            @endif

            <!-- Event Description -->
            @if(isset($event->description))
                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.description') }}</span>
                    <span class="text-base text-slate-600">{{ $event->description }}</span>
                </div>
            @endif


            <!-- Event Start - End Date -->
            @if(!empty($competition))

                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-sm text-slate-400">{{ __('events.start_date') }}</span>
                    <span class="text-base text-slate-600 ">
                    {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d/m/Y') : '--' }}
                </span>
                </div>

                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-sm text-slate-400">{{ __('events.end_date') }}</span>
                    <span class="text-base text-slate-600 ">
                    {{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('d/m/Y') : '--' }}
                </span>
                </div>

            @else

                @if(isset($event->start_date))
                    <div class="flex flex-col gap-x-2 my-3">
                        <span class="text-slate-400 text-sm">{{ __('events.event_start_date') }}</span>
                        <span
                            class="text-base text-slate-600">{{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y') }}</span>
                    </div>
                @endif

                @if(isset($event->end_date))
                    <div class="flex flex-col gap-x-2 my-3">
                        <span class="text-slate-400 text-sm">{{ __('events.event_end_date') }}</span>
                        <span
                            class="text-base text-slate-600">{{ \Carbon\Carbon::parse($event->end_date)->format('d/m/Y') }}</span>
                    </div>
                @endif

            @endif



            @if(!empty($event->organization_type))
                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.organization_type') }}</span>
                    <span
                        class="text-base text-slate-600"> {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type) }} </span>
                </div>
            @endif

            <!-- Competition Information -->
            @if(!empty($competition))
                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.competition_category') }}</span>
                    <span class="text-base text-slate-600">
                    {{ $competition->cat_competition }}
                    </span>
                </div>

                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.age_category') }}</span>
                    <span class="text-base text-slate-600">
                    {{ $competition->cat_age }}
                    </span>
                </div>
            @endif

            <!-- Registration Deadlines -->
            @if($event->start_registration || $event->end_registration)
                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.registration_start_date') }}</span>
                    <span class="text-base text-slate-600">
                        {{ $event->start_registration ? \Carbon\Carbon::parse($event->start_registration)->format('d/m/Y') : '--' }}
                    </span>
                </div>

                <div class="flex flex-col gap-x-2 my-3">
                    <span class="text-slate-400 text-sm">{{ __('events.registration_end_date') }}</span>
                    <span class="text-base text-slate-600">
                        {{ $event->end_registration ? \Carbon\Carbon::parse($event->end_registration)->format('d/m/Y') : '--' }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Event Notes -->
        @if(isset($event->notes))
            <div class="flex flex-col gap-x-2 my-3 w-full ">
                <span class="text-slate-400 text-sm">{{ __('events.event_notes') }}</span>
                <span class="prose">{!! $event->notes !!} </span>
            </div>
        @endif


        <!-- Member Enrollments -->
        @if($event->allowsEnrollments())
            <div class="mt-6 border-t border-gray-200 pt-6">
                <!-- Registration Header -->
                <div class="flex items-center gap-x-2 mb-4">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-primary-600" />
                    <span class="font-bold text-slate-700">{{ __('events.registration_wizard') }}</span>
                </div>

                <!-- 3-Step Flow Indicator -->
                <div class="bg-gradient-to-r from-blue-50 to-slate-50 rounded-lg p-4 mb-5 border border-blue-100">
                    <div class="flex items-center justify-center gap-x-2 md:gap-x-4">
                        <!-- Step 1 -->
                        <div class="flex items-center gap-x-2">
                            <div class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-primary-600 text-white text-xs md:text-sm font-semibold shadow-sm">
                                1
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-slate-700">{{ __('events.step1_title') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.enrollment') }}</p>
                            </div>
                        </div>

                        <!-- Arrow 1 -->
                        <div class="flex items-center">
                            <div class="w-4 md:w-10 h-0.5 bg-gradient-to-r from-primary-400 to-primary-300"></div>
                            <x-heroicon-s-chevron-right class="w-3 h-3 md:w-4 md:h-4 text-primary-400 -ml-1" />
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center gap-x-2">
                            <div class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-slate-300 text-slate-600 text-xs md:text-sm font-semibold">
                                2
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-slate-700">{{ __('events.step2_title') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.payment') }}</p>
                            </div>
                        </div>

                        <!-- Arrow 2 -->
                        <div class="flex items-center">
                            <div class="w-4 md:w-10 h-0.5 bg-gradient-to-r from-slate-300 to-slate-200"></div>
                            <x-heroicon-s-chevron-right class="w-3 h-3 md:w-4 md:h-4 text-slate-300 -ml-1" />
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center gap-x-2">
                            <div class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-slate-300 text-slate-600 text-xs md:text-sm font-semibold">
                                3
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-slate-700">{{ __('events.step3_title') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.confirmed_list') }}</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-center text-xs text-slate-500 mt-3">
                        {{ __('events.registration_flow_hint') }}
                    </p>
                </div>

                @if($event->isSportEvent())
                    <!-- Registration Buttons Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Athlete Registration -->
                        <a href="{{ route('entity.evt-events.events.enrollments.create', [
                               'event' => $event,
                               'type' => \App\Enums\EvtEventEnrollmentRoleEnum::toSlug(\App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
                           ]) }}"
                           class="group relative flex items-center gap-x-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-md transition-all duration-200">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                                <x-heroicon-o-user-group class="w-5 h-5 text-primary-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-700 group-hover:text-primary-700">{{ __('events.athlete_registration') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.register_athletes_for_disciplines') }}</p>
                            </div>
                            <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-primary-500 group-hover:translate-x-1 transition-all" />
                        </a>

                        @if ($event->allow_coach_enrollment)
                            <!-- Coach Registration -->
                            <a href="{{ route('entity.evt-events.events.enrollments.create', [
                                   'event' => $event,
                                   'type' => \App\Enums\EvtEventEnrollmentRoleEnum::toSlug(\App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                               ]) }}"
                               class="group relative flex items-center gap-x-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-md transition-all duration-200">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                    <x-heroicon-o-academic-cap class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-700 group-hover:text-primary-700">{{ __('events.coach_registration') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('events.register_team_coaches') }}</p>
                                </div>
                                <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-primary-500 group-hover:translate-x-1 transition-all" />
                            </a>
                        @endif

                        <!-- Team Official Registration -->
                        <a href="{{ route('entity.evt-events.events.enrollments.create', [
                               'event' => $event,
                               'type' => \App\Enums\EvtEventEnrollmentRoleEnum::toSlug(\App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value)
                           ]) }}"
                           class="group relative flex items-center gap-x-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-md transition-all duration-200">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                                <x-heroicon-o-identification class="w-5 h-5 text-violet-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-700 group-hover:text-primary-700">{{ __('events.team_official_registration') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.register_team_officials') }}</p>
                            </div>
                            <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-primary-500 group-hover:translate-x-1 transition-all" />
                        </a>
                    </div>

                    <!-- Review & Pay Link -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('entity.evt-events.events.review', ['event' => $event]) }}"
                           class="inline-flex items-center gap-x-2 text-sm text-slate-600 hover:text-primary-600 transition-colors">
                            <x-heroicon-o-document-text class="w-4 h-4" />
                            <span>{{ __('events.view_registration_summary') }}</span>
                            <x-heroicon-m-arrow-right class="w-3 h-3" />
                        </a>
                    </div>
                @else
                    <a class="group relative flex items-center gap-x-3 p-4 bg-white border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-md transition-all duration-200"
                       href="{{ route('entity.evt-events.events.individual-enrollment.index', $event->id) }}">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                            <x-heroicon-o-user-group class="w-5 h-5 text-primary-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 group-hover:text-primary-700">{{ __('events.event_registration') }}</p>
                            <p class="text-xs text-slate-500">{{ __('events.register_members_for_event') }}</p>
                        </div>
                        <x-heroicon-m-arrow-right class="w-4 h-4 text-slate-400 group-hover:text-primary-500 group-hover:translate-x-1 transition-all" />
                    </a>
                @endif
            </div>
        @endif



        @if(!auth()->user()->isAdmin())
            <div class="flex justify-center mt-4">
                <a href="{{ route('entity.evt-events.events.waiting-list.index', ['event'=>$event]) }}"
                   class="btn btn-info w-full">
                    {{ __('events.manage_waiting_list') }}
                </a>
            </div>
        @endif

    </div>
</div>
