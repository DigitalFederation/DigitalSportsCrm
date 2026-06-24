<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Districts') }}</h1>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('admin.districts.create') }}">
                    {{ __('Create District') }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-form :post="route('admin.districts.index')">
            <x-forms.filter-input-text 
                label="{{ __('Search') }}" 
                name="search" />
            
            <x-forms.filter-input-select 
                label="{{ __('Status') }}" 
                name="is_active" 
                :options="[
                    '1' => __('Active'),
                    '0' => __('Inactive')
                ]" />
        </x-filter-form>

        <!-- Data table -->
        @if(!empty($districts) && $districts->isNotEmpty())
            <div class="mb-5 mt-5">
                <x-dynamic-table :headers="[
                    __('Name'),
                    __('Code'),
                    __('Status'),
                    __('Associations'),
                    __('Actions')
                ]">
                    @foreach($districts as $district)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $district->name }}</div>
                                @if($district->description)
                                    <div class="text-sm text-slate-500">{{ Str::limit($district->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($district->code)
                                    <span class="inline-flex font-medium text-slate-800 rounded-full text-center px-2.5 py-1 bg-slate-100 text-slate-500 text-xs">
                                        {{ $district->code }}
                                    </span>
                                @else
                                    <span class="text-slate-400">{{ __('No code') }}</span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($district->is_active)
                                    <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-1 text-xs">
                                        {{ __('Active') }}
                                    </div>
                                @else
                                    <div class="inline-flex font-medium bg-slate-100 text-slate-500 rounded-full text-center px-2.5 py-1 text-xs">
                                        {{ __('Inactive') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-sm text-slate-500">
                                    {{ $district->zones_count ?? 0 }} {{ __('common.zones') }},
                                    {{ $district->entities_count ?? 0 }} {{ __('common.entities') }},
                                    {{ $district->federations_count ?? 0 }} {{ __('common.federations') }},
                                    {{ $district->individuals_count ?? 0 }} {{ __('common.individuals') }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 w-px">
                                <div class="gap-x-2 flex justify-end">
                                    <x-dynamic-table-buttons 
                                        type="show" 
                                        :route="route('admin.districts.show', $district->id)" />
                                    <x-dynamic-table-buttons 
                                        type="edit" 
                                        :route="route('admin.districts.edit', $district->id)" />
                                    
                                    @if($district->is_active)
                                        <form action="{{ route('admin.districts.toggle-status', $district->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white" 
                                                    onclick="return confirm('{{ __('Are you sure you want to deactivate this district?') }}')">
                                                {{ __('Deactivate') }}
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.districts.toggle-status', $district->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">
                                                {{ __('Activate') }}
                                            </button>
                                        </form>
                                    @endif

                                    <x-dynamic-table-buttons 
                                        type="delete" 
                                        :route="route('admin.districts.destroy', $district->id)" 
                                        method="DELETE" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $districts->links() }}
            </div>
        @else
            <x-utility.no-data />
        @endif
    </div>
</x-layout>