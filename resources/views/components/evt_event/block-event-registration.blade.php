@props([
    'event',
    'isEntity' => false,
    'hasOwnAthleteEnrollments' => false,
    'hasCompletedAssignments' => false,
])

@php
    $namespace = Request::segment(1);
    $currentId = $isEntity
        ? auth()->user()->entities()->first()?->id
        : auth()->user()->federations()->first()?->id;
    $hasCompletedAssignments = $isEntity
        ? $event->hasCompletedDisciplineAssignments(null, $currentId)
        : $event->hasCompletedDisciplineAssignments($currentId);
@endphp

<!-- EVENT REGISTRATION BLOCK CARD - 3-Step Flow -->
<div class="card w-full overflow-hidden border-0 shadow-sm p-0" x-data="{ activeStep: 1 }">
    <!-- Header Section -->
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ __('events.event_registration') }}</h2>
                    <p class="text-sm text-slate-600 mt-0.5">{{ __('events.register_manage_members_for_event') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-full">
                <span class="text-sm text-gray-600">{{ __('events.status_label') }}</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $event->allowsEnrollments() ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                    <span class="text-sm font-medium text-gray-700">{{ $event->allowsEnrollments() ? __('events.open') : __('events.closed') }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- 3-Step Navigation -->
    @if ($event->isSportEvent() && $event->allowsEnrollments())
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex flex-wrap items-center justify-between">
                <nav class="flex items-center space-x-1">
                    <!-- Step 1: Enrollment -->
                    <button @click="activeStep = 1"
                        class="flex items-center justify-center px-3 py-2 border rounded-md text-sm font-medium transition-colors duration-200"
                        :class="activeStep === 1 ? 'bg-blue-100 text-blue-700 border-blue-400' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full {{ $hasOwnAthleteEnrollments ? 'bg-green-100 text-green-700' : 'bg-blue-50 text-blue-700' }} mr-2 font-semibold text-xs">
                            @if ($hasOwnAthleteEnrollments)
                                <x-heroicon-o-check class="w-3 h-3" />
                            @else
                                1
                            @endif
                        </span>
                        {{ __('events.step1_title') }}
                    </button>
                    <div class="w-3 h-0.5 {{ $hasOwnAthleteEnrollments ? 'bg-green-300' : 'bg-gray-300' }}"></div>

                    <!-- Step 2: Review & Pay -->
                    <button @click="activeStep = 2"
                        class="flex items-center justify-center px-3 py-2 border rounded-md text-sm font-medium transition-colors duration-200"
                        :class="activeStep === 2 ? 'bg-blue-100 text-blue-700 border-blue-400' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-gray-700 mr-2 font-semibold text-xs">2</span>
                        {{ __('events.step2_title') }}
                    </button>
                    <div class="w-3 h-0.5 bg-gray-300"></div>

                    <!-- Step 3: Confirmed -->
                    <button @click="activeStep = 3"
                        class="flex items-center justify-center px-3 py-2 border rounded-md text-sm font-medium transition-colors duration-200"
                        :class="activeStep === 3 ? 'bg-blue-100 text-blue-700 border-blue-400' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'">
                        <span class="flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-gray-700 mr-2 font-semibold text-xs">3</span>
                        {{ __('events.step3_title') }}
                    </button>
                </nav>
                <div class="mt-3 sm:mt-0">
                    <p class="text-sm text-gray-500">{{ __('events.registration_flow_hint') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Content Section -->
    <div class="p-6 space-y-4">
        @if (!$event->allowsEnrollments())
            {{-- Card to view confirmed enrollments after registration closes --}}
            @if ($event->isSportEvent())
                <div class="bg-green-50 rounded-xl border border-green-200 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 p-2.5 bg-green-100 rounded-lg">
                                <x-heroicon-o-check-badge class="w-6 h-6 text-green-600" />
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ __('events.view_confirmed_enrollments') }}</h3>
                                <p class="text-sm text-gray-600 mt-0.5">{{ __('events.confirmed_enrollments_closed_description') }}</p>
                            </div>
                        </div>
                        <a href="{{ route($namespace . '.evt-events.events.confirmed-enrollments', $event) }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                            {{ __('events.view_enrollments') }}
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            @endif
        @else
            @if ($event->isSportEvent())
                <!-- Step 1: Enrollment -->
                <div class="registration-step bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button @click="activeStep = 1"
                        class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $hasOwnAthleteEnrollments ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }} font-semibold">
                                @if ($hasOwnAthleteEnrollments)
                                    <x-heroicon-o-check class="w-5 h-5" />
                                @else
                                    1
                                @endif
                            </span>
                            <div class="text-left">
                                <h3 class="text-lg font-bold text-blue-900">{{ __('events.step1_title') }}</h3>
                                <p class="text-sm text-slate-600 mt-0.5">
                                    {{ __('events.step1_info') }}
                                </p>
                                @if ($hasOwnAthleteEnrollments)
                                    <p class="text-sm text-green-600 mt-1 flex items-center">
                                        <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                                        {{ __('events.step1_complete_title') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <x-heroicon-o-chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            x-bind:class="activeStep === 1 ? 'transform rotate-180' : ''" />
                    </button>
                    <div x-show="activeStep === 1" x-transition class="border-t border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Athlete Registration -->
                            <a href="{{ route($namespace . '.evt-events.events.enrollments.create', ['event' => $event, 'type' => 'athlete']) }}"
                                class="group relative bg-white rounded-xl border-2 border-blue-500 p-5 hover:border-blue-600 transition-all duration-200 hover:shadow-lg hover:bg-blue-50">
                                <div class="flex flex-col items-center text-center">
                                    <div class="p-3 bg-blue-100 rounded-lg mb-3 group-hover:bg-blue-200 transition-colors">
                                        <x-heroicon-o-user-group class="w-7 h-7 text-blue-600" />
                                    </div>
                                    <span class="font-semibold text-gray-900 mb-1">{{ __('events.athletes') }}</span>
                                    <span class="text-xs text-slate-600">{{ __('events.Register athletes for disciplines') }}</span>
                                    <div class="mt-4">
                                        <div class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 group">
                                            {{ __('events.enroll') }}
                                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </div>
                                </div>
                            </a>

                            @if ($event->allow_coach_enrollment)
                                <!-- Coach Registration -->
                                <a href="{{ route($namespace . '.evt-events.events.enrollments.create', ['event' => $event, 'type' => 'coach']) }}"
                                    class="group relative bg-white rounded-xl border-2 border-emerald-500 p-5 hover:border-emerald-600 transition-all duration-200 hover:shadow-lg hover:bg-emerald-50">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="p-3 bg-emerald-100 rounded-lg mb-3 group-hover:bg-emerald-200 transition-colors">
                                            <x-heroicon-o-academic-cap class="w-7 h-7 text-emerald-600" />
                                        </div>
                                        <span class="font-semibold text-gray-900 mb-1">{{ __('events.coaches') }}</span>
                                        <span class="text-xs text-slate-600">{{ __('events.Register team coaches') }}</span>
                                        <div class="mt-4">
                                            <div class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-all duration-200 group">
                                                {{ __('events.enroll') }}
                                                <x-heroicon-o-arrow-right class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endif

                            @if ($event->allow_referee_enrollment && !$isEntity)
                                <!-- Referee Registration (Federation only) -->
                                <a href="{{ route('federation.evt-events.events.enrollments.create', ['event' => $event, 'type' => 'referee']) }}"
                                    class="group relative bg-white rounded-xl border-2 border-amber-500 p-5 hover:border-amber-600 transition-all duration-200 hover:shadow-lg hover:bg-amber-50">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="p-3 bg-amber-100 rounded-lg mb-3 group-hover:bg-amber-200 transition-colors">
                                            <x-heroicon-o-flag class="w-7 h-7 text-amber-600" />
                                        </div>
                                        <span class="font-semibold text-gray-900 mb-1">{{ __('events.referees') }}</span>
                                        <span class="text-xs text-slate-600">{{ __('Register event referees') }}</span>
                                        <div class="mt-4">
                                            <div class="inline-flex items-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-all duration-200 group">
                                                {{ __('events.enroll') }}
                                                <x-heroicon-o-arrow-right class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endif

                            <!-- Team Officials Registration -->
                            <a href="{{ route($namespace . '.evt-events.events.enrollments.create', ['event' => $event, 'type' => 'official']) }}"
                                class="group relative bg-white rounded-xl border-2 border-violet-500 p-5 hover:border-violet-600 transition-all duration-200 hover:shadow-lg hover:bg-violet-50">
                                <div class="flex flex-col items-center text-center">
                                    <div class="p-3 bg-violet-100 rounded-lg mb-3 group-hover:bg-violet-200 transition-colors">
                                        <x-heroicon-o-identification class="w-7 h-7 text-violet-600" />
                                    </div>
                                    <span class="font-semibold text-gray-900 mb-1">{{ __('events.team_officials') }}</span>
                                    <span class="text-xs text-slate-600">{{ __('events.Register team officials') }}</span>
                                    <div class="mt-4">
                                        <div class="inline-flex items-center px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-all duration-200 group">
                                            {{ __('events.enroll') }}
                                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Review & Pay -->
                <div class="registration-step bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button @click="activeStep = 2"
                        class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold">2</span>
                            <div class="text-left">
                                <h3 class="text-lg font-bold text-blue-900">{{ __('events.step2_title') }}</h3>
                                <p class="text-sm text-slate-600 mt-0.5">
                                    {{ __('events.step2_info') }}
                                </p>
                            </div>
                        </div>
                        <x-heroicon-o-chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            x-bind:class="activeStep === 2 ? 'transform rotate-180' : ''" />
                    </button>
                    <div x-show="activeStep === 2" x-transition class="border-t border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="hidden md:block"></div>
                            <a href="{{ route($namespace . '.evt-events.events.review', $event) }}"
                                class="group relative bg-white rounded-xl border-2 border-blue-500 p-6 hover:border-blue-600 transition-all duration-200 hover:shadow-lg hover:bg-blue-50">
                                <div class="flex flex-col items-center text-center">
                                    <div class="p-4 bg-blue-100 rounded-lg mb-4 group-hover:bg-blue-200 transition-colors">
                                        <x-heroicon-o-document-text class="w-8 h-8 text-blue-600" />
                                    </div>
                                    <span class="font-semibold text-lg text-gray-900 mb-2">{{ __('events.view_registration_summary') }}</span>
                                    <span class="text-sm text-slate-600">{{ __('events.step2_info') }}</span>
                                    <div class="mt-6">
                                        <div class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 group">
                                            {{ __('events.proceed_to_step2') }}
                                            <x-heroicon-o-arrow-right class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <div class="hidden md:block"></div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Confirmed Enrollments -->
                <div class="registration-step bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <button @click="activeStep = 3"
                        class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold">3</span>
                            <div class="text-left">
                                <h3 class="text-lg font-bold text-blue-900">{{ __('events.confirmed_enrollments') }}</h3>
                                <p class="text-sm text-slate-600 mt-0.5">
                                    {{ __('events.step3_info') }}
                                </p>
                            </div>
                        </div>
                        <x-heroicon-o-chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200"
                            x-bind:class="activeStep === 3 ? 'transform rotate-180' : ''" />
                    </button>
                    <div x-show="activeStep === 3" x-transition class="border-t border-gray-200 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="hidden md:block"></div>
                            <a href="{{ route($namespace . '.evt-events.events.confirmed-enrollments', $event) }}"
                                class="group relative bg-white rounded-xl border-2 border-green-500 p-6 hover:border-green-600 transition-all duration-200 hover:shadow-lg hover:bg-green-50">
                                <div class="flex flex-col items-center text-center">
                                    <div class="p-4 bg-green-100 rounded-lg mb-4 group-hover:bg-green-200 transition-colors">
                                        <x-heroicon-o-check-badge class="w-8 h-8 text-green-600" />
                                    </div>
                                    <span class="font-semibold text-lg text-gray-900 mb-2">{{ __('events.view_confirmed_enrollments') }}</span>
                                    <span class="text-sm text-slate-600">{{ __('events.step3_info') }}</span>
                                    <div class="mt-6">
                                        <div class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 group">
                                            {{ __('events.view_confirmed') }}
                                            <x-heroicon-o-arrow-right class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <div class="hidden md:block"></div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Non-Sport Events: Member Registration -->
            @if ($event->allowsEnrollments() && !auth()->user()->isAdmin() && !$event->isSportEvent())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route($namespace . '.evt-events.events.individual-enrollment.create', $event->id) }}"
                        class="group relative bg-white rounded-xl border-2 border-blue-500 p-6 hover:border-blue-600 transition-all duration-200 hover:shadow-lg hover:bg-blue-50">
                        <div class="flex flex-col items-center text-center">
                            <div class="p-4 bg-blue-100 rounded-lg mb-4 group-hover:bg-blue-200 transition-colors">
                                <x-heroicon-o-user-plus class="w-8 h-8 text-blue-600" />
                            </div>
                            <span class="font-semibold text-lg text-gray-900 mb-2">{{ __('events.register_members') }}</span>
                            <span class="text-sm text-slate-600">{{ __('events.start_member_registration') }}</span>
                            <div class="mt-6">
                                <div class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 group">
                                    {{ __('events.click_to_register') }}
                                    <x-heroicon-o-arrow-right class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route($namespace . '.evt-events.events.individual-enrollment.index', ['event' => $event->id, 'view' => 'list']) }}"
                        class="group relative bg-white rounded-xl border-2 border-blue-500 p-6 hover:border-blue-600 transition-all duration-200 hover:shadow-lg hover:bg-blue-50">
                        <div class="flex flex-col items-center text-center">
                            <div class="p-4 bg-blue-100 rounded-lg mb-4 group-hover:bg-blue-200 transition-colors">
                                <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-blue-600" />
                            </div>
                            <span class="font-semibold text-lg text-gray-900 mb-2">{{ __('events.registration_list') }}</span>
                            <span class="text-sm text-slate-600">{{ __('events.view_manage_registered_members') }}</span>
                            <div class="mt-6">
                                <div class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 group">
                                    {{ __('events.view_list') }}
                                    <x-heroicon-o-arrow-right class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    [x-transition] { transition: all 0.3s ease-in-out; }
</style>
