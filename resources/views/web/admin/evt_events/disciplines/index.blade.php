<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div>
                <h1 class="page-first-title">{{ __('events.disciplines') }}</h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.evt-events.events.index') }}" class="btn-info btn-xs">{{ __('events.event_list') }}</a>
                <a href="{{ route('admin.evt-events.attributes.index') }}" class="btn-info btn-xs">{{ __('events.attribute_list') }}</a>
                <a href="{{ route('admin.evt-events.discipline-templates.index') }}" class="btn-info btn-xs">{{ __('events.discipline_templates') }}</a>
                <a href="{{ route('admin.evt-events.disciplines.create') }}" class="btn-primary btn-xs">{{ __('events.create_discipline') }}</a>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.evt-events.disciplines.index')">
            <x-forms.filter-input-text label="common.name" name="filter_name" />
            <x-forms.filter-input-select label="events.sport" name="filter_sport" :options="$sports" />
            <x-forms.filter-input-select label="events.enrollment_type" name="filter_enrollment_type" :options="$enrollmentTypes" />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @if (! empty($disciplines) && $disciplines->count() > 0)
                <x-dynamic-table :headers="[
                    ['text' => '', 'sortable' => false, 'alignment' => 'text-center'],
                    ['text' => __('events.discipline'), 'field' => 'name', 'sortable' => true],
                    ['text' => __('events.enrollment_type'), 'field' => 'enrollment_type', 'sortable' => true],
                    ['text' => __('events.sport'), 'field' => 'sport', 'sortable' => false],
                    ['text' => __('events.age_group'), 'sortable' => false],
                    ['text' => __('events.attributes'), 'sortable' => false],
                    ['text' => __('common.actions'), 'sortable' => false, 'alignment' => 'text-right'],
                ]">
                    @foreach ($disciplines as $discipline)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                <div class="gap-x-2 flex justify-end">
                                    <x-dynamic-table-buttons type="duplicate"
                                                             method="POST"
                                                             :route="route('admin.evt-events.disciplines.duplicate', ['discipline' => $discipline])" />
                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route('admin.evt-events.disciplines.edit', ['discipline' => $discipline->id])" />
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $discipline->name }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $discipline->enrollment_type }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ optional($discipline->sport)->name }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @foreach ($discipline->sportAgeGroups as $ageGroup)
                                    {{ $ageGroup->title }}@if (! $loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 w-px">
                                @foreach ($discipline->attributes as $attribute)
                                    {{ $attribute->name }}@if (! $loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 w-px">
                                <div class="gap-x-2 flex justify-end">

                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('admin.evt-events.disciplines.destroy', ['discipline' => $discipline])"
                                                             method="DELETE" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <div class="text-center text-gray-500 py-4">
                    {{ __('events.no_disciplines_found') }}
                </div>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $disciplines->links() }}
        </div>
    </div>
</x-layout>
