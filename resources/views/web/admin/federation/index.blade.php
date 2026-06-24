@php
    $isLocal = $title === 'Associations';
@endphp

@section('title', $isLocal ? __('Associations') : __('National Organizations'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $isLocal ? __('Associations') : __('National Organizations') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @unless($isLocal)
                    <a href="{{ route('admin.federation-voting-right.index', request()->query()) }}" class="btn btn-info">
                        <x-heroicon-o-list-bullet class="w-4 h-4 mr-1" />
                        {{ __('Voting Rights') }}
                    </a>
                    <a href="{{ route(Request::segment(1) . '.federation.export', request()->query()) }}"
                        class="btn btn-info">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
                        {{ __('Export') }}
                    </a>
                @endunless
                <a class="btn btn-primary" href="{{ route(Request::segment(1) . '.federation.create') }}">
                    @if ($isLocal)
                        <span>{{ __('Create Association') }}</span>
                    @else
                        <span>{{ __('Create Organization') }}</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- FILTER RESULTS COUNT -->
        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total :title="$isLocal ? __('Associations') : __('National Organizations')" :count="$federations->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.federation.index')">
                <input type="hidden" name="filter[filter_is_local]" value="{{ $isLocal ? '1' : '0' }}">
                <x-forms.filter-input-text label="{{ __('ID Number') }}" name="filter_code" />
                <x-forms.filter-input-text label="{{ __('Designation') }}" name="filter_name" />
            </x-filter-form>

        </div>

        <!-- More actions -->
        @if (Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('All records') }} </h2>
            </div>
        @else
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Results') }} </h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if($isLocal)
                {{-- Simplified table for Associations (local federations) --}}
                <x-dynamic-table :headers="[
                    __('Designation'),
                    ['text' => __('ID Number'), 'field' => 'member_code'],
                    '',
                ]" :sortable="['member_code']">
                    @foreach ($federations as $federation)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap max-w-xs">
                                <div class="truncate" title="{{ $federation->legal_name }}">
                                    {{ $federation->legal_name }}
                                </div>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $federation->member_code }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end content-end">
                                <div class="space-x-1 flex items-center justify-end">
                                    <x-dynamic-table-buttons type="show" :route="route('admin.federation.show', $federation->id)" />
                                    <x-dynamic-table-buttons type="edit" :route="route('admin.federation.edit', $federation->id)" />
                                    <x-dynamic-table-buttons type="delete" :route="route('admin.federation.delete', $federation->id)" method="DELETE" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                {{-- Simplified table for National Organizations (federations) --}}
                <x-dynamic-table :headers="[
                    __('Designation'),
                    ['text' => __('ID Number'), 'field' => 'member_code'],
                    '',
                ]" :sortable="['member_code']">
                    @foreach ($federations as $federation)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap max-w-xs">
                                <div class="truncate" title="{{ $federation->legal_name }}">
                                    {{ $federation->legal_name }}
                                </div>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $federation->member_code }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end content-end">
                                <div class="space-x-1 flex items-center justify-end">
                                    <x-dynamic-table-buttons type="show" :route="route('admin.federation.show', $federation->id)" />
                                    <x-dynamic-table-buttons type="edit" :route="route('admin.federation.edit', $federation->id)" />
                                    <x-dynamic-table-buttons type="delete" :route="route('admin.federation.delete', $federation->id)" method="DELETE" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @endif

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $federations->links() }}
        </div>

    </div>
</x-layout>
