<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">{{ __('sports.sports_list') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.evt-events.events.index') }}"
                   class="btn btn-info btn-xs">{{ __('events.event_list') }}</a>

                <a href="{{ route('admin.evt-events.sport.create') }}"
                   class="btn btn-primary btn-xs">{{ __('sports.create_sport') }}</a>
            </div>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @if(!empty($sports) && $sports->count() > 0)
                <x-dynamic-table :headers="[__('main.name'), __('sports.sport_type'), '']">
                    @foreach ($sports as $sport)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">{{ $sport->translated_name }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                @if($sport->sport_type === 'individual')
                                    <span class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-sky-100 text-sky-600">
                                        {{ $sport->sport_type_label }}
                                    </span>
                                @elseif($sport->sport_type === 'team')
                                    <span class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-emerald-100 text-emerald-600">
                                        {{ $sport->sport_type_label }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="gap-x-2 flex justify-end">
                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route('admin.evt-events.sport.edit', ['sport' => $sport->id])" />
                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('admin.evt-events.sport.destroy', ['sport' => $sport->id])"
                                                             method="DELETE"
                                                             :confirmText="__('sports.confirm_delete_sport')" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>

            @endif
        </div>
        <div class="mt-4">
            {{ $sports->links() }}
        </div>
    </div>
</x-layout>
