@section('title', __('events.athletes_enrollment'))
@props([
    'event',
    'enrollments',
    'disciplines',
    'statuses',
    'genders',
    'enrolledByOptions',
])

<x-layout>
    <style>
        table {
            position: relative;
        }

        table th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        table th:nth-last-child(1) {
            position: sticky;
            right: 0;
            z-index: 10;
            background: #f8fafc
        }

        table tbody tr td:nth-last-child(1) {
            position: sticky;
            right: 0;
            z-index: 1;
            background: white;
            background-clip: padding-box;
        }

        table th:nth-last-child(1),
        table tbody tr td:nth-last-child(1) {
            z-index: 10;
        }
    </style>
    <div class="previous-layout-classes">
        <x-layout.page-header
            :title="__('events.athletes_enrollment')"
            :subtitle="$event->name"
            :actions="[
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.show', $event->id),
                    'text' => __('events.back_to_event'),
                ],
                [
                    'type' => 'form',
                    'class' => 'btn btn-info flex flex-row gap-x-2 items-center',
                    'url' => route('admin.evt-events.events.enrollments.athlete.export', $event->id),
                    'text' => __('events.export_event_data'),
                ],
                [
                    'type' => 'form',
                    'class' => 'btn btn-info flex flex-row gap-x-2 items-center',
                    'url' => route('admin.evt-events.events.enrollments.athlete.export-by-discipline', $event->id),
                    'text' => __('events.export_disciplines_data'),
                ],
            ]"
        />

        <x-evt_event.enrollment-navigation :event="$event" active="athlete.index" />

        <x-filter-form :post="route('admin.evt-events.events.enrollments.athlete.index', $event->id)">
            <x-forms.filter-input-text label="{{ __('events.athlete') }}" name="name" />
            <x-forms.filter-input-select label="{{ __('events.gender') }}" name="gender" :options="$genders" />
            <x-forms.filter-input-select label="{{ __('events.discipline') }}" name="discipline" :options="$disciplines" />
            <x-forms.filter-input-select label="{{ __('events.enrolled_by') }}" name="enrolled_by" :options="$enrolledByOptions" />
            <x-forms.filter-input-select label="{{ __('events.enrollment_status') }}" name="status" :options="$statuses" />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @php
                $allAttributes = collect($enrollments->pluck('attributes')->flatten()->pluck('attribute.name')->all());
                $uniqueAttributes = $allAttributes->unique()->toArray();
            @endphp

            @if(!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('events.athlete'), 'field' => 'name'],
                        ['text' => __('events.gender'), 'field' => 'gender'],
                        ['text' => __('events.date_of_birth'), 'field' => 'birthdate'],
                        ['text' => __('events.nationality'), 'field' => 'country_id'],
                        ['text' => __('events.enrolled_by'), 'field' => 'enrolled_by'],
                        ['text' => __('events.discipline'), 'field' => 'discipline_id'],
                    ];

                    foreach ($uniqueAttributes as $attribute) {
                        $headers[] = ['text' => $attribute, 'field' => Str::slug($attribute, '_')];
                    }

                    $headers[] = ['text' => __('events.enrollment_status'), 'field' => 'status_class'];
                    $headers[] = ['text' => '', 'field' => 'actions'];
                @endphp

                <x-dynamic-table :headers="$headers">
                    @foreach($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ strtoupper(substr($enrollment->individual?->gender ?? '', 0, 1)) }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '' }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->country?->name ?? __('events.na') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                <div class="inline-flex gap-2 items-center">
                                    @php
                                        $url = "#";
                                        $enrolledBy = "";
                                        if($enrollment->enrollment->enrollable_type === 'Domain\Federations\Models\Federation'){
                                            $url = route('admin.federation.show', $enrollment->enrollment->enrollable);
                                            $enrolledBy = $enrollment->enrollment->enrollable->name;
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Entities\Models\Entity'){
                                            $url = route('admin.entity.show', $enrollment->enrollment->enrollable);
                                            $enrolledBy = $enrollment->enrollment->enrollable->name ?? __('events.unknown');
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Individuals\Models\Individual'){
                                            if(!empty($enrollment->enrollment->enrollable)){
                                                $url = route('admin.individual.show', ['individual' => $enrollment->enrollment->enrollable]);
                                                $enrolledBy = $enrollment->enrollment->enrollable->name . ' ' . $enrollment->enrollment->enrollable->surname;
                                            }
                                        }
                                    @endphp

                                    @if($url !== "#" && $enrolledBy)
                                        <a href="{{ $url }}" class="underline hover:text-slate-400">
                                        {{ $enrolledBy ?: ucwords(\Filament\Support\get_model_label($enrollment->enrollment->enrollable_type)) }}
                                        </a>
                                    @else
                                    <span>{{ ucwords(\Filament\Support\get_model_label($enrollment->enrollment->enrollable_type)) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->discipline->name ?? '' }}
                            </td>
                            @foreach ($uniqueAttributes as $attribute)
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @php
                                        $attributeValue = optional($enrollment->attributes->firstWhere('attribute.name', $attribute))->value ?? '';
                                    @endphp
                                    {{ $attributeValue }}
                                </td>
                            @endforeach

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @php
                                    $currentStatus = $enrollment->status_class;
                                    $canConfirm = $currentStatus->canBeConfirmedAsCompleted();
                                    $isCompleted = $currentStatus === App\Enums\EvtAthleteEnrollmentStatusEnum::COMPLETED;
                                    $badgeColor = match($currentStatus) {
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::PAID => 'green',
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED => 'blue',
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::COMPLETED => 'green',
                                        default => 'gray',
                                    };
                                @endphp

                                @if($isCompleted)
                                    <x-tables.badge :color="$badgeColor" :status="App\Enums\EvtAthleteEnrollmentStatusEnum::toString($currentStatus->value)" />
                                @elseif($canConfirm)
                                    <form
                                        action="{{ route('admin.evt-events.events.enrollments.athlete.confirm-completion', ['event' => $event, 'athleteEnrollment' => $enrollment]) }}"
                                        method="POST"
                                        class="flex items-center gap-2"
                                        onsubmit="return confirm('{{ __('events.confirm_completion_question') }}')"
                                    >
                                        @csrf
                                        <x-tables.badge :color="$badgeColor" :status="App\Enums\EvtAthleteEnrollmentStatusEnum::toString($currentStatus->value)" />
                                        <button type="submit" class="btn btn-sm btn-success text-xs px-2 py-1" title="{{ __('events.confirm_completion') }}">
                                            <x-heroicon-s-check class="w-4 h-4" />
                                        </button>
                                    </form>
                                @else
                                    <form
                                        action="{{ route('admin.evt-events.events.enrollments.athlete.update-status', ['event' => $event, 'athleteEnrollment' => $enrollment]) }}"
                                        method="POST"
                                        class="flex"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <select
                                            name="new_status"
                                            onchange="this.form.submit()"
                                            class="{{ $currentStatus->getBadgeClass() }} text-xs font-semibold rounded-md border border-transparent hover:border-slate-300 focus:border-indigo-300 px-2 py-1 cursor-pointer transition-all"
                                        >
                                            <option value="{{ $currentStatus->value }}" selected class="bg-white text-slate-900">
                                                {{ App\Enums\EvtAthleteEnrollmentStatusEnum::toString($currentStatus->value) }}
                                            </option>
                                            @foreach($currentStatus->getAllowedTransitions() as $allowedStatus)
                                                <option value="{{ $allowedStatus->value }}" class="bg-white text-slate-900">
                                                    {{ App\Enums\EvtAthleteEnrollmentStatusEnum::toString($allowedStatus->value) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px !bg-slate-50">
                                <div class="flex flex-row items-center gap-2">
                                    @if($enrollment->individual?->email)
                                        <a href="mailto:{{ $enrollment->individual?->email }}"
                                        class="inline-flex items-center text-slate-600 hover:text-indigo-600 transition-colors duration-150 group"
                                        title="{{ $enrollment->individual?->email }}">
                                            <x-heroicon-o-envelope
                                                class="w-5 h-5 text-slate-400 hover:text-indigo-500 transition-colors duration-150"
                                            />
                                        </a>
                                    @endif

                                    @if($enrollment->document_id)
                                        <a href="{{ route('admin.document.show', $enrollment->document_id) }}"
                                           class="text-slate-600 hover:text-slate-800"
                                           target="_blank">
                                            <x-heroicon-o-document class="w-5 h-5" />
                                        </a>
                                    @endif

                                    <form
                                        action="{{ route('admin.evt-events.events.enrollments.athlete.destroy', ['event' => $event->id, 'athleteEnrollment' => $enrollment->id]) }}"
                                        method="POST"
                                        class="flex"
                                        onsubmit="return confirm('{{ __('events.confirm_cancel_enrollment') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700" title="{{ __('events.cancel_enrollment') }}">
                                            <x-svg.trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>

            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$enrollments->links()}}
        </div>
    </div>
</x-layout>
