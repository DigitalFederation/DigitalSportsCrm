<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('License Manager') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary" href="{{ route('admin.license.create') }}">
                    <span>{{ __('Create License') }}</span>
                </a>

            </div>

        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.license.index')">
            <x-forms.filter-input-text label="Name" name="filter_name" />
            <x-forms.filter-input-select label="Committee" name="filter_committee" :options="$committees" />
            <x-forms.filter-input-select label="Sport" name="filter_sport" :options="$sports" />
            <x-forms.filter-input-select label="Type" name="filter_type" :options="$types" />
        </x-filter-form>

        <!-- More actions -->
        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('All Records')}}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <!-- Table -->
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Name') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Committee') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Type') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('International') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Requester') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Federations') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Price (€)') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('Actions') }}</div>
                            </th>
                        </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @foreach($licenses as $license)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->name }}</td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->committee->name }}</td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->type->name }}</td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @if($license->isInternationalLicense())
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">{{ config("branding.international.short_name", "IF") }}</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">{{ __('National') }}</span>
                                    @endif
                                </td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    {{ $license->getFormattedRequesterTypes() }}
                                </td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="flex flex-col text-xs">
                                        @if($license->federations->count() > 0)
                                            @if($license->federations->count() <= 3)
                                                @foreach($license->federations as $federation)
                                                    <span class="text-slate-600">{{ Str::limit($federation->name, 30) }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-slate-600">{{ $license->federations->count() }} {{ __('federations') }}</span>
                                                <span class="text-slate-400 text-xs">
                                                    {{ Str::limit($license->federations->pluck('name')->take(2)->implode(', '), 40) }}...
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-orange-600 font-medium">{{ __('No federations') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex flex-col text-xs">
                                        @if(!empty($license->unit_value_individual))
                                            <span>{{ __('Individual') }}: {{ $license->unit_value_individual }}€</span>
                                        @endif
                                        @if(!empty($license->unit_value_entity))
                                            <span>{{ __('Entity') }}: {{ $license->unit_value_entity }}€</span>
                                        @endif
                                        @if(!empty($license->unit_value_federation))
                                            <span>{{ __('Federation') }}: {{ $license->unit_value_federation }}€</span>
                                        @endif
                                        @if(empty($license->unit_value_individual) && empty($license->unit_value_entity) && empty($license->unit_value_federation))
                                            <span class="text-slate-400">{{ __('No price set') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                    <div class="space-x-1 flex items-end justify-end">

                                        <x-dynamic-table-buttons
                                            type="edit"
                                            :route="route(Request::segment(1).'.license.edit', $license->id)"
                                            title="Edit" />
                                        
                                        @if($license->committee && $license->committee->code === 'DIVING')
                                            <x-dynamic-table-buttons
                                                type="show"
                                                :route="route(Request::segment(1).'.license-certification-requirements.show', $license->id)"
                                                title="{{ __('diving.certification_requirements') }}" />
                                        @endif

                                        <x-dynamic-table-buttons
                                            type="delete"
                                            method="DELETE"
                                            :route="route(Request::segment(1).'.license.destroy', $license->id)"
                                            title="Delete" />

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$licenses->links()}}
        </div>

    </div>
</x-layout>
