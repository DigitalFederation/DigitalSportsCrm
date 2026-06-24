@section('title', __('events.attribute_groups'))
<x-layout>

    <div class="previous-layout-classes">

        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => route('admin.evt-events.attributes.index'),
                    'text' => __('events.attribute_list'),
                ],
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-primary',
                    'url' => route('admin.evt-events.attribute-group.create'),
                    'text' => __('events.create_attribute_group'),
                ],
            ];
        @endphp


        <x-layout.page-header
            title="{{ __('events.attribute_groups') }}"
            subtitle="{{ __('events.attribute_group_list_subtitle') }}"
            :actions="$actions"
        >
        </x-layout.page-header>


        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <!-- Table -->
            @if (! empty($attributes) && $attributes->count() > 0)
                <x-dynamic-table :headers="[__('events.group_name_column'), __('events.attributes'), '']">
                    @foreach ($attributes as $attribute)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-wrap w-px">
                                {{ $attribute->name }}
                            </td>
                            <td class="px-2 last:pr-5 py-3 whitespace-nowrap w-px">
                                @foreach ($attribute->attributes as $attr)
                                    <span class="badge badge-info bg-indigo-50">{{ $attr->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class="space-x-1 flex justify-end items-center">
                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route('admin.evt-events.attribute-group.edit', $attribute->id)" />

                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('admin.evt-events.attribute-group.destroy', $attribute->id)"
                                                             method="DELETE" />
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
            {{ $attributes->links() }}
        </div>

    </div>
</x-layout>
