@section('title', $event?->name)
<x-layout-full>

    <div class="relative" x-data="{ historyModalOpen: false }">

        {{-- Page Hero Header --}}
        <div class="bg-gradient-to-r from-slate-800 to-slate-700">
            <div class="page-wrapper py-6">
                <nav class="mb-3">
                    <a href="{{ route('individual.evt-events.events.index') }}"
                       class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        {{ __('events.back_to_events') }}
                    </a>
                </nav>
                <h1 class="text-3xl md:text-4xl text-white font-bold tracking-tight">
                    {{ $event->name ?? __('events.event_details') }}
                </h1>
            </div>
        </div>

        <div class="page-wrapper py-8">

            {{-- Section 1: Event Information --}}
            <section class="mb-8">
                <x-evt_event.block-event-details :event="$event" />
            </section>

            {{-- Section 2: Location, Technical Team & Organizing Entity --}}
            <section class="grid grid-cols-1 lg:grid-cols-{{ $event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value ? '3' : '2' }} gap-4 mb-8">
                {{-- Event Location --}}
                <x-evt_event.block-event-location :event="$event" />

                @if($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value)
                    {{-- Technical Team --}}
                    <x-evt_event.block-technical-team :event="$event" />
                @endif

                {{-- Organizing Entity --}}
                <x-evt_event.block-event-loc :event="$event" />
            </section>

            {{-- Section 3: Event Documents --}}
            @if(!empty($attachments) && $attachments->count() > 0)
                <section class="mb-8">
                    <x-evt_event.block-event-attachments :event="$event" :attachments="$attachments" />
                </section>
            @endif

            {{-- Section 4: Registration Prices --}}
            @if($event->pricing && $event->pricing->count() > 0)
                <section class="mb-8">
                    <div class="card">
                        <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
                            <x-svg.currency-euro class="w-6 h-6 text-slate-600" />
                            <span class="font-bold">{{ __('events.registration_fees') }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('events.price') }}</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('events.price_type') }}</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('events.valid_period') }}</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('common.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($event->pricing as $price)
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 px-4">
                                                <span class="text-lg font-bold text-indigo-600">{{ number_format($price->price, 2) }}&euro;</span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                                                    {{ \App\Enums\EvtEventFeeTypeEnum::toString($price->price_type) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                                                    <span>{{ date('d/m/Y', strtotime($price->start_date)) }} - {{ date('d/m/Y', strtotime($price->end_date)) }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-slate-500">
                                                {{ $price->description ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Section 5: Enrollment --}}
            <section class="mb-8">
                <div class="card">
                    <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">
                        <div class="flex gap-x-2 items-center">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-slate-600" />
                            <span class="font-bold">{{ __('events.event_registration') }}</span>
                        </div>

                        {{-- History Button --}}
                        @if ($activities instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $activities->total() > 0)
                            <button type="button" @click="historyModalOpen = true"
                                class="btn btn-sm border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300"
                                title="{{ __('events.view_enrollment_history') }}">
                                <x-heroicon-o-clock class="w-4 h-4" />
                                <span class="hidden sm:inline ml-1">{{ __('events.enrollment_history') }}</span>
                            </button>
                        @endif
                    </div>

                    @if ($event->event_category == \App\Enums\EvtEventCategoryTypeEnum::organization->name)
                        {{-- Organization Event: Self-registration form --}}
                        <div class="text-center mb-6">
                            <p class="text-gray-600">
                                {{ __('events.complete_form_to_register') }}
                            </p>
                        </div>

                        <form action="{{ route('individual.evt-events.enrollments.store') }}" method="POST"
                            class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $event->id }}">

                            @if (isset($event->pricing) && $event->pricing->count() > 0)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label for="formEventPricing" class="block text-sm font-medium text-gray-700">
                                            {{ __('events.pricing_options') }}
                                        </label>
                                        <span class="text-sm text-gray-500">{{ __('events.select_one') }}</span>
                                    </div>
                                    <div class="grid gap-4">
                                        @foreach ($event->pricing as $pricing)
                                            <label
                                                class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:border-primary-500 transition-colors duration-200">
                                                <input type="radio" name="price_id" value="{{ $pricing->id }}"
                                                    class="form-radio h-5 w-5 text-primary-600" required
                                                    @if ($loop->first) checked @endif>
                                                <div class="ml-3 flex-1">
                                                    <div class="flex justify-between items-center">
                                                        <span class="font-medium text-gray-900">{{ $pricing->description }}</span>
                                                        <span class="text-lg font-semibold text-gray-900">
                                                            {{ $pricing->price }} &euro;
                                                        </span>
                                                    </div>
                                                    @if ($pricing->details)
                                                        <p class="mt-1 text-sm text-gray-500">{{ $pricing->details }}</p>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($event->attributes) && $event->attributes->isNotEmpty())
                                <div class="space-y-6">
                                    <div class="border-t border-gray-200 pt-6">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4">
                                            {{ __('events.additional_information') }}
                                        </h4>
                                        <p class="text-sm text-gray-500 mb-6">
                                            {{ __('events.provide_details_for_registration') }}
                                        </p>

                                        <div class="grid gap-6">
                                            @foreach ($event->attributes as $attribute)
                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <label for="attribute_{{ $attribute->id }}"
                                                            class="block text-sm font-medium text-gray-700">
                                                            {{ $attribute->name }}
                                                            @if ($attribute->is_required)
                                                                <span class="text-red-500">*</span>
                                                            @endif
                                                        </label>
                                                        @if ($attribute->description)
                                                            <span class="text-sm text-gray-500">{{ $attribute->description }}</span>
                                                        @endif
                                                    </div>

                                                    @if ($attribute->attribute_type === 'SELECT')
                                                        <select name="attributes[{{ $attribute->id }}]"
                                                            id="attribute_{{ $attribute->id }}"
                                                            class="w-full form-select"
                                                            @if ($attribute->is_required) required @endif>
                                                            <option value="">{{ __('events.select_an_option') }}</option>
                                                            @foreach ($attribute->attribute_data as $option)
                                                                <option value="{{ $option }}">{{ $option }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($attribute->attribute_type === 'TEXTAREA')
                                                        <textarea name="attributes[{{ $attribute->id }}]" id="attribute_{{ $attribute->id }}" class="w-full form-textarea"
                                                            rows="4" placeholder="{{ $attribute->placeholder ?? __('events.enter_response_here') }}"
                                                            @if ($attribute->is_required) required @endif></textarea>
                                                    @else
                                                        <input type="text" name="attributes[{{ $attribute->id }}]"
                                                            id="attribute_{{ $attribute->id }}"
                                                            class="w-full form-input"
                                                            placeholder="{{ $attribute->placeholder ?? __('events.enter_response_here') }}"
                                                            @if ($attribute->is_required) required @endif>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-8">
                                <button type="submit" class="w-full btn btn-primary relative"
                                    :class="{ 'opacity-50 cursor-not-allowed': submitting }" :disabled="submitting">
                                    <span class="flex items-center justify-center gap-2">
                                        <span x-show="!submitting" class="flex items-center">
                                            {{ __('events.register_for_event') }}
                                        </span>
                                        <span x-show="submitting" class="flex items-center">
                                            {{ __('events.processing_registration') }}
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </form>

                    @elseif($event->event_category == \App\Enums\EvtEventCategoryTypeEnum::competition->name)
                        @php
                            $enrollmentMessage = null;
                            $showIneligibleReasonsList = false;
                            $isEnrollmentOpen = $event->isRegistrationOpen();

                            if (!$isEnrollmentOpen) {
                                if (!$event->allowsEnrollments()) {
                                    $enrollmentMessage = __(
                                        'events.enrollments_not_permitted',
                                        ['state' => $event->stateName()],
                                    );
                                } elseif ($event->isRegistrationNotStarted()) {
                                    $enrollmentMessage = __('events.registration_not_opened', [
                                        'date' => $event->start_registration->format('Y-m-d'),
                                    ]);
                                } elseif ($event->isRegistrationClosed()) {
                                    $enrollmentMessage = __('events.registration_closed', [
                                        'date' => $event->end_registration->format('Y-m-d'),
                                    ]);
                                }
                            } elseif (!$hasEligibleDisciplines) {
                                $enrollmentMessage = __('events.registration_unavailable_requirements');
                                $showIneligibleReasonsList = true;
                            }
                        @endphp

                        @if ($enrollmentMessage)
                            <div class="border-l-4 border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-r-lg"
                                role="alert">
                                <div class="flex items-center mb-2">
                                    <div class="flex-shrink-0">
                                        <x-heroicon-s-exclamation-triangle class="h-6 w-6 text-yellow-500 dark:text-yellow-400" />
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xl font-semibold text-yellow-800 dark:text-yellow-200">
                                            {{ __('events.important_registration_conditions') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="pl-9">
                                    <p class="mb-3 mt-6 text-lg md:text-xl text-yellow-700 dark:text-yellow-300">
                                        {{ $enrollmentMessage }}
                                    </p>

                                    @if ($showIneligibleReasonsList && $ineligibleDisciplines->isNotEmpty())
                                        <div class="mt-2 p-3 bg-yellow-100 dark:bg-yellow-800/30 rounded-md">
                                            <ul class="list-disc list-inside space-y-1.5 text-base md:text-lg text-yellow-700 dark:text-yellow-300">
                                                @foreach ($ineligibleDisciplines as $reason)
                                                    <li>
                                                        @switch($reason)
                                                            @case(Domain\EvtEvents\Actions\GetIneligibleDisciplinesForIndividualAction::REASON_WRONG_ENROLLMENT_TYPE)
                                                                {{ __('events.ineligible_no_individual_enrollment') }}
                                                            @break

                                                            @case(Domain\EvtEvents\Actions\GetIneligibleDisciplinesForIndividualAction::REASON_WRONG_GENDER)
                                                                {{ __('events.ineligible_gender_requirement') }}
                                                            @break

                                                            @case(Domain\EvtEvents\Actions\GetIneligibleDisciplinesForIndividualAction::REASON_AGE_INELIGIBLE)
                                                                {{ __('events.ineligible_age_range') }}
                                                            @break

                                                            @case(Domain\EvtEvents\Actions\GetIneligibleDisciplinesForIndividualAction::REASON_MISSING_LICENSE)
                                                                {{ __('events.ineligible_missing_active_license') }}
                                                            @break

                                                            @default
                                                                {{ __('events.ineligible_unspecified') }}
                                                        @endswitch
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <livewire:evt-events.individual-create-athlete-enrollment :event="$event" :individual="$individual"
                                key="individual-create-athlete-enrollment-{{ $event->id }}" />
                        @endif
                    @endif
                </div>
            </section>

        </div>

        {{-- MODAL for Enrollment History --}}
        @if ($activities instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $activities->total() > 0)
            <div x-cloak x-show="historyModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" aria-hidden="true"></div>

            <div x-cloak x-show="historyModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center transform px-4 sm:px-6"
                role="dialog" aria-modal="true">

                <div class="bg-white dark:bg-slate-800 rounded shadow-lg overflow-auto max-w-3xl w-full max-h-full"
                    @click.outside="historyModalOpen = false">
                    {{-- Modal Header --}}
                    <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800 dark:text-slate-100">
                                {{ __('events.my_enrollment_history') }}
                            </div>
                            <button
                                class="text-slate-400 dark:text-slate-500 hover:text-slate-500 dark:hover:text-slate-400"
                                @click="historyModalOpen = false">
                                <div class="sr-only">{{ __('common.close') }}</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path
                                        d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    {{-- Modal Content --}}
                    <div class="px-5 py-4">
                        <ul class="space-y-3">
                            @foreach ($activities as $activity)
                                <li class="text-sm border-l-2 border-gray-200 pl-3 py-1">
                                    <span class="font-medium text-gray-700">[{{ $activity->created_at->format('Y-m-d H:i:s') }}]</span>
                                    <span class="text-gray-600">{{ $activity->description }}</span>
                                    @if ($activity->properties->isNotEmpty())
                                        <div class="text-xs text-gray-500 ml-2 mt-1 space-y-1">
                                            @if ($activity->properties->has('discipline_name'))
                                                <div>{{ __('events.discipline') }}: {{ $activity->properties->get('discipline_name') }}</div>
                                            @endif

                                            @if ($activity->properties->has('finalizing_discipline_names'))
                                                <div>{{ __('Disciplines Finalized') }}:
                                                    {{ implode(', ', $activity->properties->get('finalizing_discipline_names', [])) }}
                                                </div>
                                            @endif

                                            @if ($activity->properties->has('status_class'))
                                                <div>{{ __('main.Status') }}:
                                                    {{ \App\Enums\EvtAthleteEnrollmentStatusEnum::toString($activity->properties->get('status_class')) }}
                                                </div>
                                            @endif

                                            @if ($activity->properties->has('total_price'))
                                                <div>{{ __('Cost') }}:
                                                    &euro;{{ number_format($activity->properties->get('total_price', 0), 2) }}
                                                </div>
                                            @endif

                                            @if (
                                                $activity->properties->has('attribute_details') &&
                                                    is_array($activity->properties->get('attribute_details')) &&
                                                    ! empty($activity->properties->get('attribute_details')))
                                                <div>
                                                    {{ __('Details') }}:
                                                    <ul class="list-disc list-inside ml-2">
                                                        @foreach ($activity->properties->get('attribute_details') as $name => $value)
                                                            <li>{{ $name }}: {{ $value ?: __('main.N/A') }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6">
                            {{ $activities->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

</x-layout-full>
