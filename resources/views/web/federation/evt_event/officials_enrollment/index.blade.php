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
        <x-layout.page-header
            :title="__('events.team_official_enrollment')"
            :subtitle="$event->name"
            :actions="$actions"
        ></x-layout.page-header>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if(!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('events.name'), 'field' => 'name'],
                        ['text' => __('certifications.member_code'), 'field' => 'member_code'],
                        ['text' => __('events.enrolled_date'), 'field' => 'created_at']
                    ];

                    foreach ($allAttributes as $attribute) {
                        $headers[] = [
                            'text' => $attribute,
                            'alignment' => 'text-right',
                            'field' => Str::slug($attribute, '_')
                        ];
                    }

                    $headers[] = ['text' => '', 'field' => '', 'alignment' => 'text-right'];
                @endphp
                <x-dynamic-table :headers="$headers">
                    @foreach($enrollments as $enrollment)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $enrollment->individual->full_name }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                {{ $enrollment->individual->member_code }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                {{ $enrollment->created_at->format('d/m/Y') }}
                            </td>
                            @foreach($allAttributes as $attribute)
                                @php
                                    $attributeValue = $enrollment->attributes->firstWhere('attribute.name', $attribute)->value ?? '';
                                @endphp
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                    {{ $attributeValue }}
                                </td>
                            @endforeach
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
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
