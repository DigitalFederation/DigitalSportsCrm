@section('title', __('events.enrollments'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0 flex flex-col">
                <h1 class="page-first-title">
                    @if ($enrollmentType === 'official')
                        {{ __('events.team_official_enrollment') }}
                    @else
                        {{ ucfirst($enrollmentType) . ' ' . __('events.enrollment') }}
                    @endif

                </h1>
                <div class="text-slate-700 text-lg">
                    @if ($event->name)
                        {{ $event->name }}
                    @endif
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 items-start">
                <a href="{{ route('federation.evt-events.events.show', $event) }}" class="btn btn-info">
                    {{ __('events.back') }}
                </a>
                <!-- Add this line for the Export button -->
                <form
                    action="{{ route('federation.evt-events.events.organizer-enrollments.export', ['event' => $event->id, 'enrollmentType' => $enrollmentType]) }}"
                    method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info flex flex-row gap-x-2 items-center">
                        <x-svg.box-arrow-down class="w-4 h-4 text-slate-400" />
                        <span>{{ __('Export Enrollment') }}</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @php
                $allAttributes = collect($enrollments->pluck('attributes')->flatten()->pluck('attribute.name')->all());
                $uniqueAttributes = $allAttributes->unique()->toArray();
            @endphp


            @if (!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('events.name'), 'field' => 'name'],
                        ['text' => __('events.birthdate'), 'field' => 'birthdate'],
                        ['text' => __('events.gender'), 'field' => 'gender'],
                        ['text' => __('events.member_number'), 'field' => 'member_number'],
                        ['text' => __('events.email'), 'field' => 'email'],
                        ['text' => __('events.phone'), 'field' => 'phone'],
                    ];

                    // Add discipline column only for athletes
                    if ($enrollmentType === 'athlete') {
                        $headers[] = ['text' => __('events.discipline'), 'field' => 'discipline'];
                    }

                    foreach ($uniqueAttributes as $attribute) {
                        $headers[] = ['text' => $attribute, 'field' => Str::slug($attribute, '_')];
                    }

                    $headers[] = ['text' => __('events.enrolled_by'), 'field' => 'enrolled_by'];
                    $headers[] = ['text' => __('main.status'), 'field' => 'status', 'alignment' => 'text-right'];
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
                                {{ $enrollment->individual?->birthdate ? $enrollment->individual->birthdate->format('d/m/Y') : '' }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->gender }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->member_number }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->email }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->phone }}
                            </td>

                            @if ($enrollmentType === 'athlete')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    {{ $enrollment->discipline?->name ?? __('events.na') }}
                                </td>
                            @endif

                            @foreach ($uniqueAttributes as $attribute)
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @php
                                        $attributeValue =
                                            $enrollment->attributes->firstWhere('attribute.name', $attribute)?->value ??
                                            '';
                                    @endphp
                                    {{ $attributeValue }}
                                </td>
                            @endforeach

                            <td class="px-2 first:pl-5 last:pr-5 py-3 max-w-56 break-words whitespace-normal text-left">
                                <div class="inline-flex gap-2 items-center">
                                    @php
                                        $enrolledBy = '';
                                        if ($enrollment->enrollment?->enrollable) {
                                            if (
                                                $enrollment->enrollment->enrollable_type ===
                                                'Domain\Federations\Models\Federation'
                                            ) {
                                                $enrolledBy =
                                                    $enrollment->enrollment->enrollable->name ?? __('events.deleted_federation');
                                            } elseif (
                                                $enrollment->enrollment->enrollable_type ===
                                                'Domain\Entities\Models\Entity'
                                            ) {
                                                $enrolledBy =
                                                    $enrollment->enrollment->enrollable->name ?? __('events.deleted_entity');
                                            } elseif (
                                                $enrollment->enrollment->enrollable_type ===
                                                'Domain\Individuals\Models\Individual'
                                            ) {
                                                $enrolledBy = $enrollment->enrollment->enrollable
                                                    ? $enrollment->enrollment->enrollable->name .
                                                        ' ' .
                                                        $enrollment->enrollment->enrollable->surname
                                                    : __('events.deleted_individual');
                                            }
                                        }

                                        // Fallback if enrollment or enrollable_type is null
                                        $fallbackLabel = $enrollment->enrollment?->enrollable_type
                                            ? ucwords(
                                                \Filament\Support\get_model_label(
                                                    $enrollment->enrollment->enrollable_type,
                                                ),
                                            )
                                            : __('events.unknown');
                                    @endphp

                                    <p class="underline hover:text-slate-400">
                                        {{ $enrolledBy ?: $fallbackLabel }}
                                    </p>
                                </div>
                            </td>

                            <td
                                class="px-2 py-3 whitespace-normal flex justify-end">
                                @php
                                    try {
                                        $statusName = '';

                                        // Handle enum cases (AthleteEnrollment and IndividualEnrollment)
                                        if ($enrollment->status_class instanceof \UnitEnum) {
                                            $enumClass = get_class($enrollment->status_class);
                                            $statusName = $enumClass::toString($enrollment->status_class->value);
                                        }
                                        // Handle string cases that might be enum values
                                        elseif (is_string($enrollment->status_class) &&
                                            (enum_exists(\App\Enums\EvtAthleteEnrollmentStatusEnum::class) &&
                                             defined("\App\Enums\EvtAthleteEnrollmentStatusEnum::$enrollment->status_class"))) {
                                            $statusName = \App\Enums\EvtAthleteEnrollmentStatusEnum::toString($enrollment->status_class);
                                        }
                                        // Handle state class cases (CoachEnrollment and TeamOfficialEnrollment)
                                        elseif (is_string($enrollment->status_class) && class_exists($enrollment->status_class)) {
                                            $statusInstance = new $enrollment->status_class($enrollment);
                                            $statusName = $statusInstance->name();
                                        }
                                        // Fallback
                                        else {
                                            $statusName = is_string($enrollment->status_class)
                                                ? ucfirst($enrollment->status_class)
                                                : __('events.unknown');
                                        }
                                    } catch (\Exception $e) {
                                        $statusName = is_string($enrollment->status_class)
                                            ? ucfirst($enrollment->status_class)
                                            : __('events.unknown');
                                    }
                                @endphp
                                <div class="flex gap-2 items-end justify-end text-nowrap w-auto">
                                    @php
                                        $statusColor = match ($enrollment->status_class) {
                                            \App\Enums\EvtAthleteEnrollmentStatusEnum::COMPLETED->value => 'green-500',
                                            \App\Enums\EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value
                                                => 'blue-500',
                                            \Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState::class
                                                => 'blue-500',
                                            \Domain\EvtEvents\States\RegisteredCoachEnrollmentState::class
                                                => 'blue-500',
                                            \Domain\EvtEvents\States\AssignedTeamOfficialEnrollmentState::class
                                                => 'green-500',
                                            \Domain\EvtEvents\States\AssignedCoachEnrollmentState::class
                                                => 'green-500',
                                            default => 'yellow-500',
                                        };
                                    @endphp
                                    <x-tables.badge :status="$statusName" :color="$statusColor" />
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
