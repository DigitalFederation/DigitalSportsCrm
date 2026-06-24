<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('entity.evt-events.events.show', $event),
                    'text' => __('Back to Event'),
                ],
            ];

        @endphp
        <x-layout.page-header :title="__('Athletes Enrollment')" :subtitle="$event->name" :actions="$actions"></x-layout.page-header>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @php
                $allAttributes = collect($enrollments->pluck('attributes')->flatten()->pluck('attribute.name')->all());
                $uniqueAttributes = $allAttributes->unique()->toArray();
            @endphp

            @if (!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('events.name'), 'field' => 'name'],
                        ['text' => __('certifications.member_number'), 'field' => 'member_number'],
                        ['text' => __('events.gender'), 'field' => 'gender'],
                        ['text' => __('Date of Birth'), 'field' => 'birthdate'],
                        ['text' => __('events.discipline'), 'field' => 'discipline'],
                    ];

                    foreach ($uniqueAttributes as $attribute) {
                        $headers[] = ['text' => $attribute, 'field' => Str::slug($attribute, '_')];
                    }
                    $headers[] = ['text' => __('main.Status'), 'field' => 'status', 'alignment' => 'text-right'];
                @endphp

                <x-dynamic-table :headers="$headers">
                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->member_number }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->gender }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ date('d/m/Y', strtotime($enrollment->individual?->birthdate)) }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->discipline?->name ?? __('main.N/A') }}
                            </td>

                            @foreach ($uniqueAttributes as $attribute)
                                @php
                                    $attributeValue =
                                        collect($enrollment->attributes)->firstWhere('attribute.name', $attribute)
                                            ?->value ?? __('main.N/A');
                                @endphp
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $attributeValue }}
                                </td>
                            @endforeach


                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                <div class="inline-flex gap-2 items-center">
                                    @php
                                        $statusColor = match ($enrollment->status_class) {
                                            \App\Enums\EvtAthleteEnrollmentStatusEnum::COMPLETED->value => 'green-500',
                                            \App\Enums\EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value => $enrollment->discipline_id ? 'blue-500' : 'red-500',
                                            \App\Enums\EvtAthleteEnrollmentStatusEnum::PAID->value => 'amber-500',
                                            default => 'yellow-500',
                                        };

                                    @endphp
                                    <x-tables.badge :status="\App\Enums\EvtAthleteEnrollmentStatusEnum::toString(
                                        $enrollment->status_class,
                                    )" :color="$statusColor" />

                                    @if($enrollment->team_identifier)
                                        <form
                                            action="{{ route('entity.evt-events.events.athlete-enrollment.destroy', ['event' => $event->id, 'athleteEnrollment' => $enrollment->id]) }}"
                                            method="POST"
                                            class="inline"
                                            onsubmit="return confirm('{{ __('This will remove all athletes in the same relay team from their discipline. The athletes will remain registered for the event but will need to be reassigned. Are you sure you want to proceed?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="{{ __('Remove relay team') }}">
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
            {{ $enrollments->links() }}
        </div>
    </div>
</x-layout>
