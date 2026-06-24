@section('title', __('events.pending_athletes'))
@props([
    'event',
    'enrollments',
    'disciplines',
    'statuses',
    'genders',
    'enrolledByOptions',
    'navigationLinks',
])

<x-layout>
    <x-evt_event.sticky-table-styles />
    <div class="previous-layout-classes">
        <x-layout.page-header
            :title="__('events.pending_athletes')"
            :subtitle="$event->name"
            :actions="[
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('federation.evt-events.events.athlete-enrollment.index', $event->id),
                    'text' => __('events.back_to_enrollments'),
                ],
            ]"
        />

        <x-evt_event.enrollment-navigation :event="$event" active="athlete.registered" :links="$navigationLinks" />

        <x-filter-form :post="route('federation.evt-events.events.athlete-enrollment.registered', $event->id)">
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
                                        $enrolledBy = "";
                                        if($enrollment->enrollment->enrollable_type === 'Domain\Federations\Models\Federation'){
                                            $enrolledBy = $enrollment->enrollment->enrollable->name ?? __('events.deleted_federation');
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Entities\Models\Entity'){
                                            $enrolledBy = $enrollment->enrollment->enrollable->name ?? __('events.unknown');
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Individuals\Models\Individual'){
                                            if(!empty($enrollment->enrollment->enrollable)){
                                                $enrolledBy = $enrollment->enrollment->enrollable->name . ' ' . $enrollment->enrollment->enrollable->surname;
                                            }
                                        }
                                    @endphp

                                    <span>{{ $enrolledBy ?: ucwords(\Filament\Support\get_model_label($enrollment->enrollment->enrollable_type)) }}</span>
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
                                    $badgeColor = match($currentStatus) {
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::REGISTERED => 'yellow',
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT => 'orange',
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::PAID => 'green',
                                        App\Enums\EvtAthleteEnrollmentStatusEnum::CANCELED => 'red',
                                        default => 'gray',
                                    };
                                @endphp
                                <x-tables.badge :color="$badgeColor" :status="App\Enums\EvtAthleteEnrollmentStatusEnum::toString($currentStatus->value)" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px !bg-slate-50">
                                <div class="flex flex-row items-center gap-2">
                                    @if(!$enrollment->status_class->isCanceled())
                                        <form
                                            action="{{ route('federation.evt-events.events.athlete-enrollment.destroy', ['event' => $event->id, 'athleteEnrollment' => $enrollment->id]) }}"
                                            method="POST"
                                            class="flex"
                                            onsubmit="return confirm('{{ __('events.confirm_cancel_enrollment') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="{{ __('events.cancel_enrollment') }}">
                                                <x-svg.trash class="w-5 h-5" />
                                            </button>
                                        </form>
                                    @endif
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
