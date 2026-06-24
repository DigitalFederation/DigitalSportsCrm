@section('title', __('events.enrollments'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0 flex flex-col">
                <h1 class="page-first-title">
                    {{ __('events.registered_members') }}
                </h1>
                <div class="text-slate-700 text-lg">
                    @if ($event->name)
                        {{ $event->name }}
                    @endif
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 items-start">
                <a href="{{ route('entity.evt-events.events.show', $event) }}" class="btn btn-info">
                    {{ __('events.back') }}
                </a>
                <form
                    action="{{ route('entity.evt-events.events.organizer-enrollments.export', ['event' => $event->id, 'enrollmentType' => $enrollmentType]) }}"
                    method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info flex flex-row gap-x-2 items-center">
                        <x-svg.box-arrow-down class="w-4 h-4 text-slate-400" />
                        <span>{{ __('events.export_to_excel') }}</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @php
                $allAttributes = collect($enrollments->pluck('attributes')->flatten()->pluck('attribute.name')->all());
                $uniqueAttr = $allAttributes->unique()->toArray();
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

                    foreach ($uniqueAttr as $attribute) {
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

                            @foreach ($uniqueAttr as $attribute)
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

                            <td class="px-2 py-3 whitespace-normal flex justify-end">
                                <div class="flex gap-2 items-end justify-end text-nowrap w-auto">
                                    <x-tables.badge
                                        :status="\App\Enums\EvtIndividualEnrollmentStatusEnum::toString($enrollment->status_class)"
                                        :color="match($enrollment->status_class) {
                                            'registered' => 'yellow',
                                            'paid' => 'blue',
                                            'completed' => 'green',
                                            default => 'gray',
                                        }" />
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
        @if ($enrollments instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-8">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
</x-layout>
