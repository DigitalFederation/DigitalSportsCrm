<x-layout>

    <div class="previous-layout-classes">

        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => url()->previous(),
                    'text' => __('common.back'),
                ],
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => route('admin.evt-events.disciplines.index'),
                    'text' => __('events.disciplines'),
                ],
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => route('admin.evt-events.attribute-group.index'),
                    'text' => __('events.attribute_groups'),
                ],
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-primary',
                    'url' => route('admin.evt-events.attributes.create'),
                    'text' => __('events.create_attribute'),
                ],
            ];
        @endphp


        <x-layout.page-header
            title="{{ __('events.attributes') }}"
            subtitle="{{ __('events.attribute_list_subtitle') }}"
            :actions="$actions"
        >
        </x-layout.page-header>


        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <!-- Table -->
            @if (! empty($attributes) && $attributes->count() > 0)

                <x-dynamic-table :headers="[__('common.name'), __('events.type'), __('events.attribute_groups'), __('common.roles'), '']">
                    @foreach ($attributes as $attribute)
                        <tr x-data="{ showModal: false }"
                            x-on:keydown.window.escape="showModal = false">
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $attribute->name }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $attribute->attribute_type }}
                                </div>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    @if ($attribute->attributeGroups->count() > 0)
                                        @foreach ($attribute->attributeGroups as $group)
                                            <span class="badge bg-indigo-50">{{ $group->name }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="inline-flex gap-2 items-center">
                                    {{ $attribute->enrollment_type }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class="space-x-1 flex justify-end items-center">

                                    <div
                                        x-on:click="showModal=!showModal"
                                        class="text-slate-400 hover:text-slate-500 cursor-pointer">
                                        <x-svg.squares-plus
                                            class="w-6 h-6 text-blue-400 hover:text-blue-500" />
                                    </div>
                                    <div x-cloak x-show="showModal" x-transition.opacity
                                         class="fixed inset-0 bg-slate-900/75"></div>

                                    <div
                                        x-cloak
                                        x-show="showModal"
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center">
                                        <div class="w-screen max-w-4xl mx-auto card rounded-lg h-auto">
                                            @livewire('attribute-rules-index-table', ['attribute' => $attribute])
                                            <div class="justify-end text-right">
                                                <button type="button" x-on:click="showModal = false"
                                                        class="btn btn-info">{{ __('common.close') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route('admin.evt-events.attributes.edit', ['attribute' => $attribute->id])" />

                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('admin.evt-events.attributes.destroy', ['attribute' => $attribute->id])"
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
