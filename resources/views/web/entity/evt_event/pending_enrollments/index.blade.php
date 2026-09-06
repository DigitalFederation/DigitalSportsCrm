<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <x-layout.page-header subtitle="{{$event->name}}" title="{{ __('Waiting List') }}" />


        @php
            $enrollmentTypeMapping = [
                'individualEnrollments' => \App\Enums\EvtEventEnrollmentRoleEnum::INDIVIDUAL,
                'athleteEnrollments' => \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE,
                'coachEnrollments' => \App\Enums\EvtEventEnrollmentRoleEnum::COACH,
                'teamOfficialEnrollments' => \App\Enums\EvtEventEnrollmentRoleEnum::OFFICIAL,
                'refereeEnrollments' => \App\Enums\EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL,
            ];
        @endphp

            <!-- Enrollments Tables for Different Roles -->
        @if($pendingEnrollments->isEmpty())
            <div class="card text-center">
                {{ __('There are no pending enrollments for this event. You can register new participants or check back later.') }}
            </div>
        @else
            @foreach($enrollmentTypeMapping as $enrollmentType => $enumValue)
                @if($pendingEnrollments->pluck($enrollmentType)->flatten()->isNotEmpty())

                    @php
                        $allAttributes = $pendingEnrollments->pluck($enrollmentType)
                            ->flatten()
                            ->pluck('attributes')
                            ->flatten()
                            ->pluck('attribute.name')
                            ->unique();
                    @endphp

                    <div class="mb-8">
                        <div class="flex justify-between mb-2">
                            <h2 class="text-xl font-semibold mb-2">
                                {{ ucfirst(str_replace('Enrollments', ' List', $enrollmentType)) }}
                            </h2>

                            <a
                                href="{{ route('entity.evt-events.events.enrollments.create', [
                                    'event' => $event,
                                    'type' => \App\Enums\EvtEventEnrollmentRoleEnum::toSlug($enumValue->value)
                               ]) }}"
                                class="btn btn-warning btn-sm">
                                @if ($enrollmentType == 'athleteEnrollments')
                                    {{ __('events.register_more_athletes') }}
                                @elseif ($enrollmentType == 'teamOfficialEnrollments')
                                    {{ __('events.register_more_team_officials') }}
                                @else
                                    {{ __('events.register_more_type', ['type' => ucfirst(str_replace('Enrollments', '', $enrollmentType))]) }}
                                @endif
                            </a>
                        </div>

                        <div class="sm:flex sm:justify-center sm:items-center mb-5">
                            @php
                                $headers = [
                                    ['text' => __('events.name'), 'field' => 'name'],
                                    ['text' => __('events.surname'), 'field' => 'surname'],
                                ];

                                if ($enrollmentType === 'athleteEnrollments') {
                                    $headers[] = ['text' => __('events.discipline'), 'field' => 'discipline'];
                                }

                                foreach ($allAttributes as $attribute) {
                                    $headers[] = [
                                    'text' => $attribute,
                                    'alignment' => 'text-right',
                                    'field' => Str::slug($attribute, '_')
                                    ];
                                }

                                $headers[] = ['text' => '', 'field' => '', 'alignment' => 'text-right'];
                            @endphp

                            <x-dynamic-table
                                :headers="$headers">
                                @foreach($pendingEnrollments as $enrollment)
                                    @foreach($enrollment->$enrollmentType as $subEnrollment)
                                        <tr>
                                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $subEnrollment->individual->name }}</td>
                                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $subEnrollment->individual->surname }} </td>

                                            @if($enrollmentType === 'athleteEnrollments')
                                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $subEnrollment->discipline->name ?? '' }}</td>
                                            @endif

                                            <!-- Ensure All Attributes are Displayed -->
                                            @foreach($allAttributes as $attribute)
                                                @php
                                                    $attributeValue = $subEnrollment->attributes ? $subEnrollment->attributes->firstWhere('attribute.name', $attribute)->value ?? '' : '';
                                                @endphp
                                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                                    {{ $attributeValue }}
                                                </td>
                                            @endforeach


                                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right items-center">
                                                <div class="space-x-1 flex justify-end items-end">
                                                    <x-dynamic-table-buttons type="delete"
                                                                             method="DELETE"
                                                                             :route="route('entity.evt-events.events.waiting-list.destroy', [
                                                                            'event' => $event->id,
                                                                            'enrollmentType' => strtolower(str_replace('Enrollments', '', $enrollmentType)),
                                                                            'id' => $subEnrollment->id])" />
                                                </div>
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

                    <h2 class="text-lg font-bold mb-4 border-b border-slate-300 pb-2">{{ __('Cost Breakdown') }}</h2>

                    <ul class="space-y-2">
                        @foreach ($costBreakdown as $breakdown)
                            <li class="flex justify-between items-center">
                                <span class="font-semibold text-sm">{{ ucfirst($breakdown['type']) }}:</span>
                                <span>{{ money($breakdown['cost']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t border-slate-300 pt-2 mt-2 flex justify-between items-center">
                        <span class="font-bold text-sm">{{ __('Total Cost') }}:</span>
                        <span class="text-lg">{{ money($totalCost) }}</span>
                    </div>
                </div>
            @endif


            <form action="{{ route('entity.evt-events.events.waiting-list.store', $event->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="btn btn-lg btn-warning text-xl w-full flex items-center gap-x-2">
                    <x-svg.ticket class="w-6 h-6" />
                    <span>{{ __('Finalize all Registrations') }}</span>

                </button>
            </form>

            <a href="{{ route('entity.evt-events.events.show', $event) }}"
               class="btn btn-lg btn-primary text-xl w-full mt-4 flex items-center gap-x-2">
                <x-svg.arrow-back-up class="w-6 h-6" />
                <span>{{ __('Back to Event') }}</span>
            </a>
            <!-- Pagination -->
            <div class="mt-8">
                {{ $pendingEnrollments->links() }}
            </div>

        @endif


    </div>
</x-layout>
