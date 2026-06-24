<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.show', $event),
                    'text' => __('Back to Event'),
                ],
                [
                    'type' => 'form',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.enrollments.officials.export', $event->id),
                    'text' => __('Export Team Officials'),
                ],
            ];

        @endphp

        <x-layout.page-header
            :title="__('Team Officials Enrollment')"
            :subtitle="$event->name"
            :actions="$actions"
        ></x-layout.page-header>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if(!empty($enrollments) && count($enrollments) > 0)
                @php
                    $headers = [
                        ['text' => __('Name'), 'field' => 'name'],
                        ['text' => __('certifications.member_code'), 'field' => 'member_code'],
                        ['text' => __('Enrolled Date'), 'field' => 'created_at'],
                        ['text' => __('Enrolled By'), 'field' => ''],
                    ];

                    foreach ($allAttributes as $attribute) {
                        $headers[] = [
                            'text' => $attribute,
                            'alignment' => 'text-right',
                            'field' => Str::slug($attribute, '_'),
                        ];
                    }
                    $headers[] = ['text' => __('Status'), 'field' => 'status_class', 'alignment' => 'text-right'];
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
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                <div class="inline-flex gap-2 items-center">
                                    @php
                                        $enrolledBy = "";
                                        if($enrollment->enrollment->enrollable_type === 'Domain\Federations\Models\Federation'){
                                            $enrolledBy = $enrollment->enrollment->enrollable->member_code;
                                        }elseif($enrollment->enrollment->enrollable_type === 'Domain\Entities\Models\Entity'){
                                            $enrolledBy = $enrollment->enrollment->enrollable->name;
                                        }
                                    @endphp

                                    {{ $enrolledBy ?: ucwords(\Filament\Support\get_model_label($enrollment->enrollment->enrollable_type)) }}

                                </div>
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
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $enrollment->stateColor() }}">
                                    {{ $enrollment->stateName() }}
                                </span>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left"></td>
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
