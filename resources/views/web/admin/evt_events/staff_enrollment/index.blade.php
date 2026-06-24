@section('title', __('events.staff_members'))

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
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.show', $event->id),
                    'text' => __('events.back_to_event'),
                ],
                [
                    'type' => 'link',
                    'class' => 'btn btn-primary',
                    'url' => route('admin.evt-events.events.staff-enrollment.create', $event->id),
                    'text' => __('events.register_staff'),
                ],
            ];

            if (!empty($enrollments) && count($enrollments) > 0) {
                $actions[] = [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.staff-enrollment.export', $event->id),
                    'text' => __('events.export_staff_list'),
                ];
            }
        @endphp

        <x-layout.page-header
            :title="__('events.staff_members')"
            :subtitle="$event->name"
            :actions="$actions"
        />

        <x-filter-form :post="route('admin.evt-events.events.staff-enrollment.index', $event->id)">
            <x-forms.filter-input-text label="{{ __('events.name') }}" name="name" />
            <x-forms.filter-input-text label="{{ __('events.member_number') }}" name="member_number" />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @if (!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('events.name'), 'field' => 'name'],
                        ['text' => __('events.birth_date'), 'field' => 'birth_date'],
                        ['text' => __('events.gender'), 'field' => 'gender'],
                        ['text' => __('events.member_number'), 'field' => 'member_number'],
                    ];

                    if (isset($uniqueAttributes) && $uniqueAttributes->count() > 0) {
                        foreach ($uniqueAttributes as $attributeId => $attributeName) {
                            $headers[] = ['text' => $attributeName, 'field' => Str::slug($attributeName, '_')];
                        }
                    }

                    $headers[] = ['text' => '', 'field' => 'actions'];
                @endphp

                <x-dynamic-table :headers="$headers">
                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->birthdate?->format('d/m/Y') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ strtoupper(substr($enrollment->individual?->gender ?? '', 0, 1)) }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $enrollment->individual?->member_number }}
                            </td>

                            @if (isset($uniqueAttributes))
                                @foreach ($uniqueAttributes as $attributeId => $attributeName)
                                    @php
                                        $attributeValue =
                                            $enrollment->attributes->where('attribute_id', $attributeId)->first()
                                                ?->value ?? '';
                                    @endphp
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        {{ $attributeValue }}
                                    </td>
                                @endforeach
                            @endif

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px !bg-slate-50">
                                <div class="flex flex-row items-center gap-2">
                                    <form
                                        action="{{ route('admin.evt-events.events.staff-enrollment.destroy', ['event' => $event->id, 'staff_enrollment' => $enrollment->id]) }}"
                                        method="POST" x-data="{}"
                                        @submit.prevent="if (confirm('{{ __('events.confirm_remove_staff') }}')) $el.submit();">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:text-red-700 transition-colors duration-150 p-1 rounded-full hover:bg-red-50"
                                            title="{{ __('events.remove_staff') }}">
                                            <x-svg.trash class="w-5 h-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data />
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $enrollments->links() }}
        </div>
    </div>
</x-layout>
