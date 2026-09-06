<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->

        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('federation.evt-events.events.show', $event),
                    'text' => __('events.back_to_event')
                ]
            ];
        @endphp
        <x-layout.page-header subtitle="{{$event->name}}" title="{{ __('Waiting List') }}" :actions="$actions" />

        <!-- Welcome Message & Instructions Card -->
        <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-information-circle class="w-8 h-8 text-blue-500" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Registration Review') }}</h2>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('You have selected participants for enrollment in this event. Please review the details below:') }}
                        </p>
                        <ul class="mt-2 text-sm text-gray-600 space-y-1 list-disc list-inside">
                            <li>{{ __('Verify all participant information is correct') }}</li>
                            <li>{{ __('Check the cost breakdown for accuracy') }}</li>
                            <li>{{ __('Use the "Register more" buttons to add additional participants') }}</li>
                            <li>{{ __('Click "Close waiting list and finalize registrations" when ready to proceed') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <x-heroicon-o-clipboard-document-list class="w-12 h-12 text-gray-400" />
                    <h3 class="text-lg font-medium text-gray-900">{{ __('No Pending Enrollments') }}</h3>
                    <p class="text-gray-500 max-w-sm">
                        {{ __('There are no pending enrollments for this event. You can register new participants or check back later.') }}
                    </p>
                    <a href="{{ route('federation.evt-events.events.show', $event) }}"
                    class="btn btn-primary mt-4">
                        {{ __('Return to Event Page') }}
                    </a>
                </div>
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
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-xl font-semibold mb-2">
                                {{ ucfirst(str_replace('Enrollments', ' List', $enrollmentType)) }}
                            </h2>

                            <a
                                href="{{ route('federation.evt-events.events.enrollments.create', [
                                        'event' => $event,
                                        'type' => \App\Enums\EvtEventEnrollmentRoleEnum::toSlug($enumValue->value)
                                   ]) }}"
                                class="btn btn-warning btn-sm">

                                @if($enrollmentType == 'athleteEnrollments')
                                    {{ __('events.register_more_athletes') }}
                                @elseif($enrollmentType == 'teamOfficialEnrollments')
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
                                                                             :route="route('federation.evt-events.events.waiting-list.destroy', [
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
            <!-- Enhanced Cost Display -->
            @if($totalCost > 0)
                <div class="mb-8 bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Cost Breakdown') }}</h2>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            @foreach ($costBreakdown as $breakdown)
                                <li class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700">{{ ucfirst($breakdown['type']) }}</span>
                                    <span class="text-gray-900">{{ money($breakdown['cost']) }}</span>
                                </li>
                            @endforeach
                            <li class="pt-3 mt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="font-semibold text-gray-900">{{ __('Total Cost') }}</span>
                                <span class="text-lg font-bold text-gray-900">{{ money($totalCost) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif

                <!-- Pagination -->
            <div class="mt-8">
                {{ $pendingEnrollments->links() }}
            </div>



            <form action="{{ route('federation.evt-events.events.waiting-list.store', $event->id) }}" method="POST">
                @csrf
                <button type="submit"
                        class="btn btn-lg btn-info text-xl w-full flex items-center gap-x-2">
                    <x-svg.ticket class="w-6 h-6" />
                    <span>{{ __('Close waiting list and finalize registrations') }}</span>

                </button>
                <p class="text-sm text-gray-500 text-center mt-2">
                    {{ __('This action will submit all pending enrollments for final processing') }}
                </p>
            </form>

            <a href="{{ route('federation.evt-events.events.show', $event) }}"
                class="btn btn-lg btn-primary text-xl w-full flex items-center justify-center gap-x-2 py-2 mt-4">
                <x-svg.arrow-back-up class="w-6 h-6" />
                <span>{{ __('Back to the event') }}</span>
            </a>

        @endif


    </div>
</x-layout>
