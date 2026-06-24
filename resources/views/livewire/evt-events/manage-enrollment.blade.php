@php
    $namespace = $this->model instanceof \Domain\Federations\Models\Federation ? 'federation' : 'entity';
    $wizardSteps = [
        ['number' => 1, 'title' => __('events.step1_title'), 'description' => __('events.enrollment')],
        ['number' => 2, 'title' => __('events.step2_title'), 'description' => __('events.payment')],
        ['number' => 3, 'title' => __('events.step3_title'), 'description' => __('events.confirmed_list')],
    ];
@endphp

<div x-data="{ currentStep: @entangle('currentStep') }">

    {{-- Wizard Step Indicator --}}
    <x-evt-events.wizard-step-indicator
        :currentStep="1"
        :steps="$wizardSteps"
        :event="$event"
        :model="$this->model"
    />

    <!-- Event Registration Progress -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <!-- Event Header -->
        <div class="border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $event->name }}</h1>
                    <div class="mt-2 flex items-center gap-x-4 text-sm text-gray-600">
                        <span class="flex items-center gap-x-1.5">
                            <x-heroicon-m-calendar class="w-4 h-4" />
                            {{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($event->end_date)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($successMessages)
        <div class="flex gap-4 bg-green-700 p-4 rounded-md mb-4 items-center mt-2">
            <div class="w-max">
                <div class="flex rounded-full text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-sm">
                @foreach ($successMessages as $message)
                    <p class="text-white leading-tight">{{ $message }}</p>
                @endforeach
            </div>
        </div>
    @endif
    @if ($errorMessages)
        <div class="flex gap-4 bg-red-700 p-4 rounded-md mb-4 items-center mt-2">
            <div class="w-max">
                <div class="flex rounded-full text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-sm">
                @foreach ($errorMessages as $message)
                    <p class="text-white leading-tight">{{ $message }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div x-show="currentStep === 1">
        <!-- Enhanced Instructions Card -->
        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
            <!-- Filters Card -->
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <!-- Step 1: Filter & Select Discipline -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-700 font-semibold">1</span>
                            </div>
                            <div>
                                <h3 class="text-blue-900 font-bold mb-2">{{ __('events.filter_select_discipline') }}</h3>
                                <p class="text-blue-700 text-sm mb-4">{{ __('events.filter_select_discipline_description') }}</p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                    @if ($availableFilters['has_individual'] || $availableFilters['has_relay'])
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-sm font-medium text-gray-700">{{ __('events.competition_type') }}</label>
                                            <div class="relative">
                                                <select wire:model.live="disciplineFilters.enrollment_type"
                                                    class="form-select w-full pl-3 pr-10 py-2">
                                                    <option value="">{{ __('events.all_types') }}</option>
                                                    @if ($availableFilters['has_individual'])
                                                        <option value="individual">{{ __('events.individual') }}</option>
                                                    @endif
                                                    @if ($availableFilters['has_relay'])
                                                        <option value="relay">{{ __('events.relay') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($availableFilters['has_male'] || $availableFilters['has_female'] || $availableFilters['has_mixed'])
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-sm font-medium text-gray-700">{{ __('events.gender') }}</label>
                                            <select wire:model.live="disciplineFilters.gender" class="form-select">
                                                <option value="">{{ __('events.select_gender') }}</option>
                                                @if ($availableFilters['has_male'])
                                                    <option value="male">{{ __('events.male') }}</option>
                                                @endif
                                                @if ($availableFilters['has_female'])
                                                    <option value="female">{{ __('events.female') }}</option>
                                                @endif
                                                @if ($availableFilters['has_mixed'])
                                                    <option value="mixed">{{ __('events.mixed') }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    @endif

                                    @if (count($availableFilters['styles']))
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-sm font-medium text-gray-700">{{ __('events.style') }}</label>
                                            <select wire:model.live="disciplineFilters.style" class="form-select">
                                                <option value="">{{ __('events.select_style') }}</option>
                                                @foreach ($availableFilters['styles'] as $style)
                                                    <option value="{{ $style }}">{{ $style }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if (count($availableFilters['distances']))
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-sm font-medium text-gray-700">{{ __('events.distance') }}</label>
                                            <select wire:model.live="disciplineFilters.distance" class="form-select">
                                                <option value="">{{ __('events.select_distance') }}</option>
                                                @foreach ($availableFilters['distances'] as $distance)
                                                    <option value="{{ $distance }}">{{ $distance }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if ($filteredDisciplines->count() > 0)
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-sm font-medium text-gray-700">{{ __('events.discipline') }}</label>
                                            <select wire:model.live="selectedDiscipline" class="form-select">
                                                <option value="">{{ __('events.select_discipline') }}</option>
                                                @foreach ($filteredDisciplines as $discipline)
                                                    <option value="{{ $discipline->id }}">{{ $discipline->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>

                                <!-- Active Filters Summary -->
                                @if (collect($disciplineFilters)->filter()->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($disciplineFilters as $key => $value)
                                            @if ($value)
                                                <span class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-primary-700 bg-primary-50 ring-1 ring-inset ring-primary-600/20">
                                                    <span class="text-primary-900">{{ ucfirst($value) }}</span>
                                                    <button wire:click="$set('disciplineFilters.{{ $key }}', '')"
                                                        class="ml-1 text-primary-400 hover:text-primary-600"
                                                        title="{{ __('events.remove_filter') }}">
                                                        <x-heroicon-m-x-mark class="h-3 w-3" />
                                                    </button>
                                                </span>
                                            @endif
                                        @endforeach

                                        <button wire:click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700">
                                            {{ __('events.clear_all_filters') }}
                                        </button>
                                    </div>
                                @endif

                                {{-- Confirm Selection Button --}}
                                @if ($selectedDiscipline && !$disciplineConfirmed)
                                    <div class="mt-4 flex justify-end">
                                        <button wire:click="confirmDisciplineSelection"
                                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary border border-transparent rounded-lg font-medium text-sm text-white tracking-wide shadow-sm hover:bg-primary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                                            {{ __('events.confirm_selection') }}
                                            <x-heroicon-m-arrow-right class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    </div>
            </div>
        @else
            @if ($multiplePerPersonPricing)
                <select wire:model="selectedPricingIds.perPerson" class="form-select w-full">
                    <option value="">{{ __('events.select_per_person_pricing') }}</option>
                    @foreach ($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::PER_PERSON->value) as $pricing)
                        <option value="{{ $pricing->id }}">{{ $pricing->description }} - {{ $pricing->price }}€
                        </option>
                    @endforeach
                </select>
            @endif

            @if ($multipleEventFeePricing)
                <select wire:model="selectedPricingIds.eventFee" class="form-select w-full">
                    <option value="">{{ __('events.select_event_fee_pricing') }}</option>
                    @foreach ($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::EVENT_FEE->value) as $pricing)
                        <option value="{{ $pricing->id }}">{{ $pricing->description }} - {{ $pricing->price }}€
                        </option>
                    @endforeach
                </select>
            @endif

            <!-- Coach/Official attribute workflow explainer -->
            @if (in_array($enrollmentType, [\App\Enums\EvtEventEnrollmentRoleEnum::COACH->value, \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value]))
                <div class="mb-6 bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-information-circle class="h-5 w-5 text-blue-500" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-blue-800">{{ __('events.complete_your_registration') }}</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>
                                    @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                        {{ __('events.coaches_need_info') }}
                                    @else
                                        {{ __('events.officials_need_info') }}
                                    @endif
                                </p>
                                <ul class="list-disc pl-5 space-y-1 mt-1">
                                    <li>
                                        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                            {{ __('events.select_coaches_from_table') }}
                                        @else
                                            {{ __('events.select_officials_from_table') }}
                                        @endif
                                    </li>
                                    <li>{{ __('events.click_continue_attributes') }}</li>
                                    <li>{{ __('events.fill_required_info') }}</li>
                                </ul>
                                <div class="mt-2 flex items-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">{{ __('events.attributes_required') }}</span>
                                    <span>
                                        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                            {{ __('events.coaches_incomplete_info') }}
                                        @else
                                            {{ __('events.officials_incomplete_info') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
            @if ($disciplineConfirmed)
                <section class="w-full md:flex md:flex-col bg-white rounded-lg shadow-sm border border-gray-200">
                    <!-- Step 2: Select Athletes (Header) -->
                    <div class="p-4 border-b border-gray-200">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-700 font-semibold">2</span>
                                </div>
                                <div>
                                    <h3 class="text-blue-900 font-bold mb-2">{{ __('events.select_athletes') }}</h3>
                                    <p class="text-blue-700 text-sm">{{ __('events.select_athletes_description') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ $this->table }}
                </section>
            @endif
        @else
            {{-- Enrollment Context Banner for non-athlete roles --}}
            @if ($enrollmentContext['alreadyEnrolled'] > 0 || $enrollmentContext['totalEligible'] === 0)
                <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Summary Stats --}}
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-700">{{ __('events.enrollment_summary') }}:</span>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <x-heroicon-m-users class="w-3.5 h-3.5" />
                                    {{ __('events.eligible') }}: {{ $enrollmentContext['totalEligible'] }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <x-heroicon-m-check-circle class="w-3.5 h-3.5" />
                                    {{ __('events.already_enrolled') }}: {{ $enrollmentContext['alreadyEnrolled'] }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $enrollmentContext['availableToEnroll'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                                    <x-heroicon-m-user-plus class="w-3.5 h-3.5" />
                                    {{ __('events.available_to_enroll') }}: {{ $enrollmentContext['availableToEnroll'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Contextual Message --}}
                    @if ($enrollmentContext['availableToEnroll'] === 0)
                        <div class="p-4">
                            @if ($enrollmentContext['alreadyEnrolled'] > 0 && $enrollmentContext['alreadyEnrolled'] === $enrollmentContext['totalEligible'])
                                {{-- All eligible are already enrolled --}}
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-600" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ __('events.all_enrolled_title') }}</h4>
                                        <p class="mt-1 text-sm text-gray-600">
                                            @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value)
                                                {{ __('events.all_referees_enrolled_message') }}
                                            @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                                {{ __('events.all_coaches_enrolled_message') }}
                                            @else
                                                {{ __('events.all_officials_enrolled_message') }}
                                            @endif
                                        </p>

                                        {{-- Already Enrolled List --}}
                                        <div class="mt-3">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">{{ __('events.enrolled_list') }}</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($enrollmentContext['enrolledIndividuals'] as $enrolled)
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                                        <x-heroicon-m-user class="w-3 h-3" />
                                                        {{ $enrolled['name'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Action Link --}}
                                        <div class="mt-4">
                                            @php
                                                $manageRoute = match($enrollmentType) {
                                                    \App\Enums\EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value => $namespace === 'federation'
                                                        ? route('federation.evt-events.events.organizer-enrollments.index', ['event' => $event, 'enrollmentType' => 'referee'])
                                                        : null,
                                                    \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value => $namespace === 'federation'
                                                        ? route('federation.evt-events.events.coach-enrollment.index', $event)
                                                        : route('entity.evt-events.events.coach-enrollment.index', $event),
                                                    \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value => $namespace === 'federation'
                                                        ? route('federation.evt-events.events.officials-enrollment.index', $event)
                                                        : route('entity.evt-events.events.officials-enrollment.index', $event),
                                                    default => null,
                                                };
                                            @endphp
                                            @if ($manageRoute)
                                                <a href="{{ $manageRoute }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-700">
                                                    {{ __('events.manage_enrollments') }}
                                                    <x-heroicon-m-arrow-right class="w-4 h-4" />
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif ($enrollmentContext['totalEligible'] === 0)
                                {{-- No eligible individuals --}}
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                                            <x-heroicon-s-exclamation-triangle class="w-6 h-6 text-amber-600" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ __('events.no_eligible_title') }}</h4>
                                        <p class="mt-1 text-sm text-gray-600">
                                            @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value)
                                                {{ __('events.no_referees_eligible_message') }}
                                            @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                                {{ __('events.no_coaches_eligible_message') }}
                                            @else
                                                {{ __('events.no_officials_eligible_message') }}
                                            @endif
                                        </p>
                                        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value)
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ __('events.referee_role_hint') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif ($enrollmentContext['alreadyEnrolled'] > 0)
                        {{-- Some enrolled, some available --}}
                        <div class="p-4">
                            <details class="group">
                                <summary class="flex items-center gap-2 cursor-pointer list-none">
                                    <x-heroicon-m-chevron-right class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" />
                                    <span class="text-sm font-medium text-gray-700">{{ __('events.view_already_enrolled') }} ({{ $enrollmentContext['alreadyEnrolled'] }})</span>
                                </summary>
                                <div class="mt-3 pl-6">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($enrollmentContext['enrolledIndividuals'] as $enrolled)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                                <x-heroicon-m-check class="w-3 h-3" />
                                                {{ $enrolled['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Coach/Official/Referee - always show table --}}
            <section class="w-full md:flex md:flex-col bg-white rounded-lg shadow-sm border border-gray-200">
                {{ $this->table }}
            </section>
        @endif

        {{-- Action Bar --}}
        <div class="sticky bottom-4 z-10 mt-6">
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg ring-1 ring-slate-200/60 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                    <x-ui.button variant="secondary" :href="$this->eventShowRoute">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        {{ __('events.back_to_event') }}
                    </x-ui.button>

                    <x-ui.button variant="primary" :href="$this->step2Route">
                        {{ __('events.proceed_to_step2') }}
                        <x-heroicon-m-arrow-right class="w-4 h-4" />
                    </x-ui.button>

                    <x-ui.button variant="secondary" :href="$this->step3Route">
                        {{ __('events.view_confirmed_enrollments') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

    </div>

    <div x-show="currentStep === 2">

        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
                                {{ __('events.selected_athletes_info') }}
                            @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                {{ __('events.coach_information') }}
                            @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value)
                                {{ __('events.team_official_info') }}
                            @else
                                {{ __('events.participant_information') }}
                            @endif
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
                                {{ __('events.complete_info_for') }} {{ count($selectedIndividuals) }} {{ __('events.selected_athletes') }}
                            @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                                {{ __('events.complete_info_for') }} {{ count($selectedIndividuals) }} {{ count($selectedIndividuals) > 1 ? __('events.coaches') : __('events.coach') }}
                            @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value)
                                {{ __('events.complete_info_for') }} {{ count($selectedIndividuals) }} {{ count($selectedIndividuals) > 1 ? __('events.team_officials') : __('events.team_official') }}
                            @else
                                {{ __('events.complete_info_for') }} {{ count($selectedIndividuals) }} {{ __('events.selected_participants') }}
                            @endif
                        </p>
                    </div>
                    <button wire:click="$set('currentStep', 1)"
                        class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-x-1">
                        <x-heroicon-m-arrow-left class="h-4 w-4" />
                        {{ __('events.back_to_selection') }}
                    </button>
                </div>

                @if ($selectedDisciplineModel)
                    <div class="border-t border-gray-200 px-4 py-3 bg-gray-50">
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Discipline Name -->
                            <div class="flex items-center gap-x-2">
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                    {{ $selectedDisciplineModel->name }}
                                </span>
                            </div>

                            <!-- Active Filters -->
                            @php
                                $activeFilters = collect($disciplineFilters)
                                    ->filter()
                                    ->map(function ($value, $key) {
                                        return [
                                            'key' => ucfirst($key),
                                            'value' => ucfirst($value),
                                        ];
                                    });
                            @endphp

                            @if ($activeFilters->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach ($activeFilters as $filter)
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                            <span class="font-medium">{{ $filter['key'] }}:</span>
                                            <span class="ml-1">{{ $filter['value'] }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Additional Info -->
                            @if ($selectedDisciplineModel->athlete_limit)
                                <span
                                    class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800">
                                    <x-heroicon-m-user-group class="h-4 w-4 mr-1" />
                                    {{ __('events.max_athletes') }}: {{ $selectedDisciplineModel->athlete_limit }}
                                </span>
                            @endif
                        </div>

                    </div>
                @endif
            </div>

            <!-- Global Attributes Section -->
            @if (!empty($globalAttributes))
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('events.global_attributes') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <x-global-attribute-form-input :globalAttributes="$globalAttributes" :values="$globalAttributeValues" />
                    </div>
                </div>
            @endif

            <!-- Individual Athletes Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">
                        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
                            {{ __('events.athlete_properties') }}
                        @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                            {{ __('events.coach_properties') }}
                        @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value)
                            {{ __('events.team_official_properties') }}
                        @else
                            {{ __('events.participant_properties') }}
                        @endif
                    </h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach ($selectedIndividuals as $selected)
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex flex-wrap items-center gap-4">

                                <!-- Athlete Info -->
                                <div class="flex items-center gap-x-3 min-w-[250px]">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-500">
                                                {{ substr($selected['name'], 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $selected['name'] }}</h4>
                                        <p class="text-xs text-gray-500">CMAS: {{ $selected['member_code'] }}</p>
                                    </div>
                                </div>

                                <!-- Inline Attributes -->
                                <div class="flex-1 flex flex-wrap items-center gap-4">
                                    @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value && !empty($disciplineAttributes))
                                        @foreach ($disciplineAttributes as $attributeKey => $attributeData)
                                            <div class="justify-end content-end">
                                                <x-attribute-form-input :selected="$selected" :attribute="$attributeData"
                                                    :wire="'disciplineAttributeValues.' .
                                                        $selected['id'] .
                                                        '.' .
                                                        $attributeData['attribute_data']['id']" :value="$attributeValues[$selected['id']][
                                                        $attributeData['attribute_data']['id']
                                                    ] ?? $attributeData['attribute_data']['default_value']" :options="$attributeData['attribute_data']['options'] ?? []" />
                                            </div>
                                        @endforeach
                                    @endif

                                    @if (!empty($roleAttributes))
                                        @foreach ($roleAttributes as $attributeKey => $attributeData)
                                            <div class="justify-end">
                                                <x-attribute-form-input :selected="$selected" :attribute="$attributeData"
                                                    :wire="'roleAttributeValues.' .
                                                        $selected['id'] .
                                                        '.' .
                                                        $attributeData['attribute_data']['id']" :value="$attributeValues[$selected['id']][
                                                        $attributeData['attribute_data']['id']
                                                    ] ?? $attributeData['attribute_data']['default_value']" :options="$attributeData['attribute_data']['options'] ?? []" />
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-x-3">
                <x-filament::button wire:click="$set('currentStep', 1)" color="gray" class="">
                    {{ __('events.back_to_selection') }}
                </x-filament::button>

                <x-filament::button wire:click="submitEnrollment" color="primary" :disabled="count($selectedIndividuals) < 1">
                    @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
                        {{ __('events.save_athlete_information') }}
                    @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::COACH->value)
                        {{ __('events.save_coach_attributes') }}
                    @elseif ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL->value)
                        {{ __('events.save_team_official_attributes') }}
                    @else
                        {{ __('events.save_registration_information') }}
                    @endif
                </x-filament::button>
            </div>
        </div>
    </div>

</div>
