<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('events.waiting_list_for') }} {{ $event->name }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2"></div>

        </div>

        @php
            $allAttributes = collect();
            foreach ($pendingEnrollments as $enrollment) {
                $allAttributes = $allAttributes->merge($enrollment->individualEnrollments->pluck('attributes')->flatten())
                                               ->merge($enrollment->athleteEnrollments->pluck('attributes')->flatten());
            }
            $uniqueAttributes = $allAttributes->pluck('attribute.name')->unique()->toArray();
        @endphp

            <!-- Enrollments Tables for Different Roles -->
        @foreach(['individualEnrollments', 'athleteEnrollments'] as $enrollmentType)
            @if($pendingEnrollments->pluck($enrollmentType)->flatten()->isNotEmpty())

                <div class="mb-8">

                    <div class="sm:flex sm:justify-center sm:items-center mb-5">
                        @php
                            $headers = [
                                ['text' => __('main.status'), 'field' => 'payment_status'],
                                ['text' => __('main.name'), 'field' => 'name'],
                                ['text' => __('main.surname'), 'field' => 'surname']
                            ];

                            if ($enrollmentType === 'athleteEnrollments') {
                                $headers[] = ['text' => __('events.discipline'), 'field' => 'discipline'];
                            }

                            foreach ($uniqueAttributes as $attribute) {
                                $headers[] = ['text' => $attribute, 'alignment' => 'text-right','field' => Str::slug($attribute, '_')];
                            }

                            $headers[] = ['text' => '', 'field' => '', 'alignment' => 'text-right'];

                        @endphp

                        <x-dynamic-table
                            :headers="$headers">
                            @foreach($pendingEnrollments as $enrollment)
                                @foreach($enrollment->$enrollmentType as $subEnrollment)

                                    <tr>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                            @if($subEnrollment->enrollment->payment_status == \App\Enums\EvtEventPaymentStatusEnum::PENDING->value)
                                                <span
                                                    class="text-yellow-500 font-bold">{{ \App\Enums\EvtEventPaymentStatusEnum::toString($subEnrollment->enrollment->payment_status) }}</span>
                                            @elseif($subEnrollment->enrollment->payment_status == \App\Enums\EvtEventPaymentStatusEnum::PAID->value)
                                                <span
                                                    class="text-green-500 font-bold">{{ \App\Enums\EvtEventPaymentStatusEnum::toString($subEnrollment->enrollment->payment_status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $subEnrollment->individual->name }}</td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $subEnrollment->individual->surname }}</td>

                                        @if($enrollmentType === 'athleteEnrollments')
                                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $subEnrollment->discipline->name ?? '' }}</td>
                                        @endif

                                        <!-- Display Global Attributes -->
                                        @foreach ($uniqueAttributes as $attribute)
                                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                                @php
                                                    $attributeValue = $subEnrollment->attributes->firstWhere('attribute.name', $attribute)?->value ?? '';
                                                @endphp
                                                {{ $attributeValue }}
                                            </td>
                                        @endforeach

                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">

                                            <form
                                                action="{{ route('individual.evt-events.events.waiting-list.destroy', [
                                                        'event' => $event->id,
                                                        'enrollmentType' => strtolower(str_replace('Enrollments', '', $enrollmentType)),
                                                        'id' => $subEnrollment->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-danger">
                                                    <x-svg.trash class="w-4 h-4" />
                                                </button>
                                            </form>

                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </x-dynamic-table>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Total Cost Display -->
        @if($totalCost > 0)
            <div class="items-center mb-4 p-4 bg-white shadow rounded-lg">

                <h2 class="text-lg font-bold mb-4 border-b border-slate-300 pb-2">{{ __('events.cost_breakdown') }}</h2>

                <ul class="space-y-2">
                    @foreach ($costBreakdown as $breakdown)
                        <li class="flex justify-between items-center">
                            <span class="font-semibold text-sm">{{ ucfirst($breakdown['type']) }}:</span>
                            <span>{{ money($breakdown['cost']) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-slate-300 pt-2 mt-2 flex justify-between items-center">
                    <span class="font-bold text-sm">{{ __('events.total_cost') }}:</span>
                    <span class="text-lg">{{ money($totalCost) }}</span>
                </div>
            </div>
        @endif

        <a
            href="{{ route('individual.evt-events.events.show', $event->id) }}"
            class="btn btn-xl btn-info w-full text-lg mb-4">
            {{ __('events.return_to_event_details') }}
        </a>

        <form action="{{ route('individual.evt-events.events.waiting-list.store', $event->id) }}" method="POST">
            @csrf
            <button type="submit"
                    class="btn btn-xl btn-primary w-full text-lg">{{ __('events.complete_registration_process') }}</button>
        </form>


    </div>
</x-layout>
