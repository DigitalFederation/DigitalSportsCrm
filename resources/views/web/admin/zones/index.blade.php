<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('zones.title') }}</h1>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('admin.districts.index') }}">
                    {{ __('zones.manage_districts') }}
                </a>
                <a class="btn btn-primary" href="{{ route('admin.zones.create') }}">
                    {{ __('zones.create_zone') }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-form :post="route('admin.zones.index')">
            <x-forms.filter-input-text 
                label="{{ __('zones.search') }}" 
                name="search" />
            
            <x-forms.filter-input-select 
                label="{{ __('zones.country') }}" 
                name="country" 
                :options="$countries" />
            
            <x-forms.filter-input-select 
                label="{{ __('zones.status') }}" 
                name="is_active" 
                :options="[
                    '1' => __('zones.active'),
                    '0' => __('zones.inactive')
                ]" />
        </x-filter-form>

        <!-- Data table -->
        @if(!empty($zones) && $zones->isNotEmpty())
            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-5">
                <x-dynamic-table :headers="[
                    __('zones.name'),
                    __('zones.code'),
                    __('zones.districts'),
                    __('zones.status'),
                    __('zones.associations'),
                    __('zones.creator'),
                    __('zones.actions')
                ]">
                    @foreach($zones as $zone)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $zone->name }}</div>
                                @if($zone->description)
                                    <div class="text-sm text-slate-500">{{ Str::limit($zone->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($zone->code)
                                    <span class="inline-flex font-medium text-slate-800 rounded-full text-center px-2.5 py-1 bg-slate-100 text-slate-500 text-xs">
                                        {{ $zone->code }}
                                    </span>
                                @else
                                    <span class="text-slate-400">{{ __('zones.no_code') }}</span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-sm">
                                    <span class="font-medium">{{ $zone->districts_count }}</span> 
                                    {{ __('zones.districts_lowercase') }}
                                    @if($zone->districts->isNotEmpty())
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $zone->districts->pluck('country.name')->unique()->implode(', ') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($zone->is_active)
                                    <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-1 text-xs">
                                        {{ __('zones.active') }}
                                    </div>
                                @else
                                    <div class="inline-flex font-medium bg-slate-100 text-slate-500 rounded-full text-center px-2.5 py-1 text-xs">
                                        {{ __('zones.inactive') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-sm text-slate-500">
                                    {{ $zone->entities_count ?? 0 }} {{ __('common.entities') }},
                                    {{ $zone->federations_count ?? 0 }} {{ __('common.federations') }},
                                    {{ $zone->individuals_count ?? 0 }} {{ __('common.individuals') }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($zone->creator)
                                    <div class="text-sm text-slate-500">{{ $zone->creator->name }}</div>
                                @else
                                    <div class="text-sm text-slate-400">{{ __('zones.system') }}</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 w-px">
                                <div class="gap-x-2 flex justify-end">
                                    <x-dynamic-table-buttons 
                                        type="show" 
                                        :route="route('admin.zones.show', $zone->id)" />
                                    <x-dynamic-table-buttons 
                                        type="edit" 
                                        :route="route('admin.zones.edit', $zone->id)" />
                                    
                                    @if($zone->is_active)
                                        <form action="{{ route('admin.zones.toggle-status', $zone->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white" 
                                                    onclick="return confirm('{{ __('zones.confirm_deactivate') }}')">
                                                {{ __('zones.deactivate') }}
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.zones.toggle-status', $zone->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">
                                                {{ __('zones.activate') }}
                                            </button>
                                        </form>
                                    @endif

                                    <x-dynamic-table-buttons 
                                        type="delete" 
                                        :route="route('admin.zones.destroy', $zone->id)" 
                                        method="DELETE" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $zones->links() }}
                </div>
            </div>
        @else
            <x-utility.no-data />
        @endif
    </div>
</x-layout>