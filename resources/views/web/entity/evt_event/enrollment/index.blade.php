<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Enrollment list') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('federation.evt-events.events.enrollment.create', $event) }}">
                    <span>{{ __('Enroll athletes') }}</span>
                </a>
            </div>

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if(!empty($enrollments) && count($enrollments) > 0)
                <x-dynamic-table :headers="[__('events.event'), __('events.athlete'), __('main.Status'), __('Activated'), __('main.Actions')]">
                    @foreach($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    <a href="{{ route('admin.evt-events.events.show', ['event' => $event->id, '$enrollment' => $enrollment->id])}}">{{ $enrollment->name }}</a>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $enrollment->payment_status }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $enrollment->activated_at }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class="space-x-1 flex justify-end items-end">

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
