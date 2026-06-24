@section('title', __('events.event_enrollments'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => route('federation.evt-events.events.show', ['event' => $event]),
                    'text' => __('events.back')
                ],
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-success',
                    'url' => route('federation.evt-events.events.individual-enrollment.export', ['event' => $event]),
                    'text' => __('events.export_to_excel'),
                    'svg' => 'document-arrow-down',
                    'svgClass' => 'w-4 h-4 text-slate-400',
                ],
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-primary',
                    'url' => route('federation.evt-events.events.individual-enrollment.create', ['event' => $event]),
                    'text' => __('events.event_registration'),
                    'svg' => 'box-arrow-down',
                    'svgClass' => 'w-4 h-4 text-slate-400',
                ],
            ];
        @endphp

        <x-layout.page-header
            :title="__('events.registered_members')"
            :subtitle="$event->name"
            :actions="$actions">
        </x-layout.page-header>


        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if(!empty($enrollments) && count($enrollments) > 0)

                @php
                    $uniqueAttributes = $enrollments->flatMap(function ($enrollment) {
                        return collect($enrollment->attributes)->map(function ($attribute) {
                            return $attribute->attribute->name;
                        });
                    })->unique()->sort()->values();
                @endphp



                <x-dynamic-table :headers="[]">

                    <thead>
                    <tr class="bg-slate-50">
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('events.name') }}</th>
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('events.birthdate') }}</th>
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('events.gender') }}</th>
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('events.member_number') }}</th>
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('events.email') }}</th>
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('events.phone') }}</th>
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">{{ __('main.status') }}</th>
                        @foreach($uniqueAttributes as $attributeName)
                            <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap last:text-right">
                                {{ $attributeName }}</th>
                        @endforeach
                        <th class="text-sm px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">{{ __('main.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($enrollments as $enrollment)
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

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                <x-tables.badge
                                    :status="\App\Enums\EvtIndividualEnrollmentStatusEnum::toString($enrollment->status_class)"
                                    :color="match($enrollment->status_class) {
                                        'registered' => 'yellow',
                                        'paid' => 'blue',
                                        'completed' => 'green',
                                        default => 'gray',
                                    }" />
                            </td>

                            @foreach($uniqueAttributes as $attributeName)
                                @php
                                    $attributeValue = collect($enrollment->attributes)->firstWhere('attribute.name', $attributeName)?->value ?? __('events.na');
                                @endphp
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">{{ $attributeValue }}</td>
                            @endforeach

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                <form action="{{ route('federation.evt-events.events.individual-enrollment.destroy', ['event' => $event, 'individualEnrollment' => $enrollment]) }}" method="POST" onsubmit="return confirm('{{ __('events.confirm_delete_registration') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
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
