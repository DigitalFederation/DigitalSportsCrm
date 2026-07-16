@php
    $wizardSteps = [
        ['number' => 1, 'title' => __('events.step1_title'), 'description' => __('events.enrollment')],
        ['number' => 2, 'title' => __('events.step2_title'), 'description' => __('events.payment')],
        ['number' => 3, 'title' => __('events.step3_title'), 'description' => __('events.confirmed_list')],
    ];
@endphp

<div class="space-y-5" x-data="{ removing: null }">

    {{-- Wizard Step Indicator --}}
    <x-evt-events.wizard-step-indicator
        :currentStep="2"
        :steps="$wizardSteps"
        :event="$event"
        :model="$this->model"
    />

    {{-- Page Header Card --}}
    <div class="relative overflow-hidden bg-white rounded-xl shadow-sm ring-1 ring-slate-200/60">
        <div class="px-6 py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:flex items-center justify-center w-12 h-12 rounded-xl bg-primary-50 ring-1 ring-primary-200/50">
                        <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-primary-600" />
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-slate-900 tracking-tight">{{ $event->name }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ __('events.step2_info') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-lg ring-1 ring-slate-200/80">
                        <x-heroicon-m-calendar-days class="w-4 h-4 text-slate-400" />
                        <span>{{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}</span>
                    </div>
                    <a href="{{ $this->step1Route }}"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        <span class="hidden sm:inline">{{ __('events.back_to_step1') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (!$hasEnrollments)
        {{-- Empty State --}}
        <div class="bg-amber-50 rounded-xl ring-1 ring-amber-200/60">
            <div class="px-6 py-8">
                <div class="flex flex-col items-center text-center max-w-md mx-auto">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 ring-4 ring-amber-50 mb-4">
                        <x-heroicon-o-exclamation-triangle class="w-7 h-7 text-amber-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-amber-900">{{ __('events.no_enrollments_found') }}</h3>
                    <p class="mt-2 text-sm text-amber-700/80">{{ __('events.please_complete_steps') }}</p>
                    <a href="{{ $this->step1Route }}"
                        class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-amber-900 bg-white rounded-lg shadow-sm ring-1 ring-amber-300 hover:bg-amber-50 transition-all duration-150">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        {{ __('events.go_to_step1') }}
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- Athletes by Discipline --}}
        @if (!empty($enrollmentsByDiscipline))
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
                {{-- Section Header --}}
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary-100 ring-1 ring-primary-200/50">
                            <x-heroicon-s-users class="w-5 h-5 text-primary-600" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">{{ __('events.athletes') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('events.organized_by_discipline') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Disciplines --}}
                <div class="divide-y divide-slate-100">
                    @foreach ($enrollmentsByDiscipline as $disciplineId => $disciplineData)
                        <div class="group" x-data="{ expanded: true }">
                            {{-- Discipline Header --}}
                            <button
                                @click="expanded = !expanded"
                                class="w-full px-6 py-4 flex items-center justify-between bg-slate-50/50 hover:bg-slate-50 transition-colors duration-150"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-500 shadow-sm">
                                        <x-heroicon-s-trophy class="w-4 h-4 text-white" />
                                    </div>
                                    <div class="text-left">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-semibold text-slate-800">{{ $disciplineData['discipline_name'] }}</h3>
                                            @if ($disciplineData['is_team_or_relay'] ?? false)
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-primary-700 bg-primary-100 rounded">
                                                    {{ ucfirst($disciplineData['enrollment_type'] ?? 'team') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500">{{ count($disciplineData['enrollments']) }} {{ Str::plural(__('events.participant'), count($disciplineData['enrollments'])) }}</p>
                                    </div>
                                </div>
                                <x-heroicon-m-chevron-down class="w-5 h-5 text-slate-400 transition-transform duration-200" x-bind:class="expanded ? 'rotate-180' : ''" />
                            </button>

                            {{-- Discipline Table --}}
                            <div x-show="expanded" x-collapse>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="bg-slate-50/80">
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                    {{ __('events.athlete') }}
                                                </th>
                                                @if (isset($disciplineAttributes[$disciplineId]))
                                                    @foreach ($disciplineAttributes[$disciplineId] as $attribute)
                                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                            {{ $attribute['name'] }}
                                                        </th>
                                                    @endforeach
                                                @endif
                                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">
                                                    {{ __('events.actions') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach ($disciplineData['enrollments'] as $enrollment)
                                                <tr
                                                    wire:key="athlete-{{ $enrollment['id'] }}"
                                                    class="group/row hover:bg-primary-50/30 transition-colors duration-100"
                                                    x-bind:class="removing === 'athlete-{{ $enrollment['id'] }}' ? 'opacity-50 pointer-events-none' : ''"
                                                >
                                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-slate-100 ring-2 ring-white shadow-sm flex items-center justify-center">
                                                                <span class="text-xs font-bold text-slate-600">
                                                                    {{ strtoupper(substr($enrollment['name'], 0, 2)) }}
                                                                </span>
                                                            </div>
                                                            <span class="text-sm font-medium text-slate-900">{{ $enrollment['name'] }}</span>
                                                        </div>
                                                    </td>
                                                    @if (isset($disciplineAttributes[$disciplineId]))
                                                        @foreach ($disciplineAttributes[$disciplineId] as $attribute)
                                                            <td class="px-4 py-3.5 whitespace-nowrap">
                                                                @if (isset($enrollment['attributes'][$attribute['id']]) && $enrollment['attributes'][$attribute['id']] !== '')
                                                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 rounded-md ring-1 ring-slate-200/80">
                                                                        {{ $enrollment['attributes'][$attribute['id']] }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-slate-300">&mdash;</span>
                                                                @endif
                                                            </td>
                                                        @endforeach
                                                    @endif
                                                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                                        <button
                                                            wire:click="removeEnrollment({{ $enrollment['id'] }}, 'athlete')"
                                                            wire:confirm="{{ __('events.confirm_remove_enrollment') }}"
                                                            wire:loading.attr="disabled"
                                                            x-on:click="removing = 'athlete-{{ $enrollment['id'] }}'"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-all duration-150"
                                                            title="{{ __('events.remove') }}"
                                                        >
                                                            <x-heroicon-m-trash class="w-4 h-4" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Other Enrollments (Coaches, Referees, Officials) --}}
        @php
            $otherRoles = [
                'coaches' => [
                    'title' => __('events.coaches'),
                    'icon' => 'academic-cap',
                    'color' => 'blue',
                    'type' => 'coach'
                ],
                'referees' => [
                    'title' => __('events.referees'),
                    'icon' => 'flag',
                    'color' => 'violet',
                    'type' => 'referee'
                ],
                'officials' => [
                    'title' => __('events.team_officials'),
                    'icon' => 'identification',
                    'color' => 'emerald',
                    'type' => 'official'
                ],
            ];
        @endphp

        @foreach ($otherRoles as $roleKey => $roleConfig)
            @if (!empty($otherEnrollments[$roleKey]))
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
                    {{-- Section Header --}}
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-{{ $roleConfig['color'] }}-100 ring-1 ring-{{ $roleConfig['color'] }}-200/50">
                                    @if ($roleConfig['icon'] === 'academic-cap')
                                        <x-heroicon-s-academic-cap class="w-5 h-5 text-{{ $roleConfig['color'] }}-600" />
                                    @elseif ($roleConfig['icon'] === 'flag')
                                        <x-heroicon-s-flag class="w-5 h-5 text-{{ $roleConfig['color'] }}-600" />
                                    @else
                                        <x-heroicon-s-identification class="w-5 h-5 text-{{ $roleConfig['color'] }}-600" />
                                    @endif
                                </div>
                                <h2 class="text-base font-semibold text-slate-900">{{ $roleConfig['title'] }}</h2>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-{{ $roleConfig['color'] }}-700 bg-{{ $roleConfig['color'] }}-50 rounded-full ring-1 ring-{{ $roleConfig['color'] }}-200/60">
                                {{ count($otherEnrollments[$roleKey]) }}
                            </span>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-slate-50/60">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        {{ __('events.name') }}
                                    </th>
                                    @if (!empty($otherAttributes[$roleKey]))
                                        @foreach ($otherAttributes[$roleKey] as $attribute)
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                                {{ $attribute['name'] }}
                                            </th>
                                        @endforeach
                                    @endif
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">
                                        {{ __('events.actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($otherEnrollments[$roleKey] as $enrollment)
                                    <tr
                                        wire:key="{{ $roleConfig['type'] }}-{{ $enrollment['id'] }}"
                                        class="group/row hover:bg-{{ $roleConfig['color'] }}-50/30 transition-colors duration-100"
                                        x-bind:class="removing === '{{ $roleConfig['type'] }}-{{ $enrollment['id'] }}' ? 'opacity-50 pointer-events-none' : ''"
                                    >
                                        <td class="px-6 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-{{ $roleConfig['color'] }}-100 ring-2 ring-white shadow-sm flex items-center justify-center">
                                                    <span class="text-xs font-bold text-{{ $roleConfig['color'] }}-700">
                                                        {{ strtoupper(substr($enrollment['name'], 0, 2)) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-medium text-slate-900">{{ $enrollment['name'] }}</span>
                                            </div>
                                        </td>
                                        @if (!empty($otherAttributes[$roleKey]))
                                            @foreach ($otherAttributes[$roleKey] as $attribute)
                                                <td class="px-4 py-3.5 whitespace-nowrap">
                                                    @if (isset($enrollment['attributes'][$attribute['id']]) && $enrollment['attributes'][$attribute['id']] !== '')
                                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 rounded-md ring-1 ring-slate-200/80">
                                                            {{ $enrollment['attributes'][$attribute['id']] }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-300">&mdash;</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endif
                                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                            <button
                                                wire:click="removeEnrollment({{ $enrollment['id'] }}, '{{ $roleConfig['type'] }}')"
                                                wire:confirm="{{ __('events.confirm_remove_enrollment') }}"
                                                wire:loading.attr="disabled"
                                                x-on:click="removing = '{{ $roleConfig['type'] }}-{{ $enrollment['id'] }}'"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-all duration-150"
                                                title="{{ __('events.remove') }}"
                                            >
                                                <x-heroicon-m-trash class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Cost Summary Card (Invoice Style) --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 ring-1 ring-slate-200/50">
                        <x-heroicon-s-calculator class="w-5 h-5 text-slate-600" />
                    </div>
                    <h2 class="text-base font-semibold text-slate-900">{{ __('events.cost_breakdown') }}</h2>
                </div>
            </div>

            <div class="px-6 py-5">
                {{-- Invoice Table --}}
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="pb-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('events.description') }}</th>
                            <th class="pb-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">{{ __('events.qty') }}</th>
                            <th class="pb-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">{{ __('events.unit_price') }}</th>
                            <th class="pb-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-28">{{ __('events.total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {{-- Section: Per-Person Registrations --}}
                        @if (!empty($costBreakdown['registrations']))
                            <tr>
                                <td colspan="4" class="pt-4 pb-2">
                                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('events.per_person_registrations') }}</span>
                                </td>
                            </tr>
                            @foreach ($costBreakdown['registrations'] as $key => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 text-sm text-slate-700">{{ $item['label'] }}</td>
                                    <td class="py-2.5 text-sm text-slate-600 text-center">{{ $item['count'] }}</td>
                                    <td class="py-2.5 text-sm text-slate-600 text-right">{{ money($item['unit_price']) }}</td>
                                    <td class="py-2.5 text-sm font-semibold text-slate-900 text-right">{{ money($item['total']) }}</td>
                                </tr>
                            @endforeach
                        @endif

                        {{-- Section: Discipline Fees --}}
                        @if (!empty($costBreakdown['disciplines']))
                            <tr>
                                <td colspan="4" class="pt-5 pb-2">
                                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('events.discipline_fees') }}</span>
                                </td>
                            </tr>
                            @foreach ($costBreakdown['disciplines'] as $disciplineId => $discipline)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 text-sm text-slate-700">
                                        {{ $discipline['name'] }}
                                        @if ($discipline['is_team_or_relay'])
                                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 ring-1 ring-blue-200/50">
                                                {{ __('events.team_relay') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 text-sm text-slate-600 text-center">
                                        {{ $discipline['entries'] }}
                                        <span class="text-xs text-slate-400">{{ $discipline['entries'] == 1 ? __('events.entry') : __('events.entries') }}</span>
                                    </td>
                                    <td class="py-2.5 text-sm text-slate-600 text-right">{{ money($discipline['unit_price']) }}</td>
                                    <td class="py-2.5 text-sm font-semibold text-slate-900 text-right">{{ money($discipline['total']) }}</td>
                                </tr>
                            @endforeach
                        @endif

                        {{-- Section: Other Fees --}}
                        @if (!empty($costBreakdown['other']))
                            <tr>
                                <td colspan="4" class="pt-5 pb-2">
                                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('events.other_fees') }}</span>
                                </td>
                            </tr>
                            @foreach ($costBreakdown['other'] as $key => $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 text-sm text-slate-700">{{ $item['label'] }}</td>
                                    <td class="py-2.5 text-sm text-slate-600 text-center">{{ $item['count'] }}</td>
                                    <td class="py-2.5 text-sm text-slate-600 text-right">-</td>
                                    <td class="py-2.5 text-sm font-semibold text-slate-900 text-right">{{ money($item['total']) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                {{-- Grand Total --}}
                <div class="mt-5 pt-5 border-t-2 border-slate-200">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-slate-900">{{ __('events.grand_total') }}</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold text-primary">
                                {{ money($grandTotal) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="sticky bottom-4 z-10">
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-lg ring-1 ring-slate-200/60 p-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Status Message --}}
                    <div class="flex items-center gap-3">
                        @if ($grandTotal > 0)
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 ring-1 ring-amber-200/50">
                                <x-heroicon-s-credit-card class="w-5 h-5 text-amber-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __('events.payment_required_message') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.total') }}: {{ money($grandTotal) }}</p>
                            </div>
                        @else
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 ring-1 ring-emerald-200/50">
                                <x-heroicon-s-check-badge class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __('events.no_payment_required') }}</p>
                                <p class="text-xs text-slate-500">{{ __('events.proceed_to_confirmation') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-3 w-full sm:w-auto sm:flex-row sm:items-center">
                        <x-ui.button variant="secondary" :href="$this->step1Route">
                            <x-heroicon-m-arrow-left class="w-4 h-4" />
                            {{ __('events.back_to_step1') }}
                        </x-ui.button>

                        @if ($enrollmentMismatch)
                            <div class="flex items-start gap-3 px-4 py-3 bg-red-50 rounded-lg ring-1 ring-red-200/80">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-semibold text-red-800">{{ __('events.enrollment_mismatch_title') }}</p>
                                    <p class="mt-1 text-xs text-red-700">{{ __('events.enrollment_mismatch_message') }}</p>
                                </div>
                            </div>
                        @elseif ($needsPaymentDocument)
                            <x-ui.button
                                variant="primary"
                                wire:click="confirmAndGeneratePayment"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('events.confirm_generate_payment') }}"
                            >
                                <span wire:loading.remove wire:target="confirmAndGeneratePayment">{{ __('events.confirm_and_pay') }}</span>
                                <span wire:loading wire:target="confirmAndGeneratePayment">{{ __('events.processing') }}</span>
                                <x-heroicon-m-credit-card class="w-4 h-4" wire:loading.remove wire:target="confirmAndGeneratePayment" />
                                <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" wire:loading wire:target="confirmAndGeneratePayment" />
                            </x-ui.button>
                        @elseif ($hasPendingPayment)
                            <x-ui.button
                                variant="primary"
                                wire:click="proceedToPayment"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove wire:target="proceedToPayment">{{ __('events.proceed_to_payment') }}</span>
                                <span wire:loading wire:target="proceedToPayment">{{ __('events.processing') }}</span>
                                <x-heroicon-m-arrow-right class="w-4 h-4" wire:loading.remove wire:target="proceedToPayment" />
                                <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" wire:loading wire:target="proceedToPayment" />
                            </x-ui.button>
                        @elseif ($grandTotal <= 0 && $hasEnrollments)
                            <x-ui.button
                                variant="primary"
                                wire:click="confirmAndGeneratePayment"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('events.confirm_free_registration') }}"
                            >
                                <span wire:loading.remove wire:target="confirmAndGeneratePayment">{{ __('events.confirm_registration') }}</span>
                                <span wire:loading wire:target="confirmAndGeneratePayment">{{ __('events.processing') }}</span>
                                <x-heroicon-m-check-circle class="w-4 h-4" wire:loading.remove wire:target="confirmAndGeneratePayment" />
                                <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" wire:loading wire:target="confirmAndGeneratePayment" />
                            </x-ui.button>
                        @endif

                        <x-ui.button variant="secondary" :href="$this->step3Route">
                            {{ __('events.view_confirmed_enrollments') }}
                            <x-heroicon-m-arrow-right class="w-4 h-4" />
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
