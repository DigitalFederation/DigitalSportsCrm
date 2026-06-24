<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">{{ __('Sport Age Groups') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.evt-events.events.index') }}"
                   class="btn btn-info btn-xs">{{ __('events.event_list') }}</a>

                <a href="{{ route('admin.evt-events.sport-age-groups.create') }}"
                   class="btn btn-primary btn-xs">{{ __('Create Age Group') }}</a>
            </div>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @if (! empty($ageGroups) && $ageGroups->count() > 0)
                <x-dynamic-table :headers="[__('Title'), __('events.sport'), __('Birthday Start'), __('Birthday End'), '']">
                    @foreach ($ageGroups as $ageGroup)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">{{ $ageGroup->title }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">{{ optional($ageGroup->sport)->name }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">{{ $ageGroup->birthday_start }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">{{ $ageGroup->birthday_end }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="gap-x-2 flex justify-end">
                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route('admin.evt-events.sport-age-groups.edit', ['sport_age_group' => $ageGroup->id])" />
                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('admin.evt-events.sport-age-groups.destroy', ['sport_age_group' => $ageGroup->id])"
                                                             method="DELETE" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>

            @endif
        </div>
        <div class="mt-4">
            {{ $ageGroups->links() }}
        </div>
    </div>
</x-layout>
