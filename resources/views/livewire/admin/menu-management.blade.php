@php
use Illuminate\Support\Str;
@endphp

<div>
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Menu Selection Section -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm mb-6">
    
        <div class="p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <label for="menu-select" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('menu.dynamic.admin.select_menu_type') }}
                    </label>
                    <select wire:model.live="selectedMenuType" id="menu-select" class="form-select w-full md:w-1/2">
                        <option value="">{{ __('menu.dynamic.admin.select_menu') }}</option>
                        @foreach($availableMenus as $menu)
                            <option value="{{ $menu->machine_name }}">{{ $menu->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                @if($selectedMenuType)
                    <div class="flex items-center space-x-2">
                        <button class="btn btn-primary" wire:click="addItem">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('menu.dynamic.admin.add_item') }}
                        </button>
                        
                        <!-- Cache Management Button -->
                        <button class="btn btn-info" wire:click="toggleCacheInfo">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            {{ __('menu.dynamic.admin.cache_management') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cache Management Panel -->
    @if($showCacheInfo)
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm mb-6">
            <div class="px-5 py-3 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('menu.dynamic.admin.cache_management') }}</h2>
            </div>
            <div class="p-5">
                <!-- Cache Status Info -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-gray-800 mb-1">{{ __('menu.dynamic.admin.cache_info_title') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('menu.dynamic.admin.cache_info_description') }}</p>
                            
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                                <p class="text-sm text-blue-800">{{ __('menu.dynamic.admin.cache_clear_info') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cache Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Clear Cache -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">{{ __('menu.dynamic.admin.clear_cache') }}</h4>
                        <p class="text-sm text-gray-600 mb-4">{{ __('menu.dynamic.admin.clear_cache_description') }}</p>
                        <button wire:click="clearMenuCache" 
                                wire:loading.attr="disabled"
                                class="btn btn-primary w-full">
                            <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <svg wire:loading class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            {{ $selectedMenuType ? __('menu.dynamic.admin.clear_selected_menu_cache') : __('menu.dynamic.admin.clear_all_menu_cache') }}
                        </button>
                    </div>
                    
                    <!-- Rebuild Cache -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-800 mb-2">{{ __('menu.dynamic.admin.rebuild_cache') }}</h4>
                        <p class="text-sm text-gray-600 mb-4">{{ __('menu.dynamic.admin.rebuild_cache_description') }}</p>
                        <button wire:click="rebuildMenuCache" 
                                wire:loading.attr="disabled"
                                class="btn btn-info w-full">
                            <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg wire:loading class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            {{ $selectedMenuType ? __('menu.dynamic.admin.rebuild_selected_menu_cache') : __('menu.dynamic.admin.rebuild_all_menu_cache') }}
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
    @endif

    @if($selectedMenuType)
        <!-- Tabs for Items and Groups -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <!-- Tab Navigation -->
            <div class="border-b border-slate-200">
                <nav class="flex" aria-label="Tabs">
                    <button
                        wire:click="$set('activeTab', 'items')"
                        @class([
                            'px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200',
                            'border-primary text-primary' => $activeTab === 'items',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== 'items'
                        ])
                    >
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <span>{{ __('menu.dynamic.admin.menu_items') }}</span>
                        </div>
                    </button>
                    <button
                        wire:click="$set('activeTab', 'groups')"
                        @class([
                            'px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200',
                            'border-primary text-primary' => $activeTab === 'groups',
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== 'groups'
                        ])
                    >
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>{{ __('menu.dynamic.admin.menu_groups') }}</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            @if($activeTab === 'items')
                <!-- Menu Items Tab -->
                <div>
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h2 class="text-lg font-semibold text-slate-800">
                                {{ __('menu.dynamic.admin.menu_items_for', ['menu' => $selectedMenuType]) }}
                            </h2>
                            <div class="flex-1 md:max-w-md">
                                <input type="text" 
                                       class="form-input w-full" 
                                       placeholder="{{ __('menu.dynamic.admin.search_items') }}"
                                       wire:model.live.debounce.300ms="search">
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                @if($menuItems->count() > 0)
                    <!-- Reorder Mode Toggle -->
                    <div class="mb-4 flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <button 
                                wire:click="$toggle('reorderMode')"
                                class="btn {{ $reorderMode ? 'btn-primary' : 'btn-secondary' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                                {{ $reorderMode ? __('menu.dynamic.admin.exit_reorder_mode') : __('menu.dynamic.admin.enter_reorder_mode') }}
                            </button>
                            @if($reorderMode)
                                <span class="text-sm text-gray-600">{{ __('menu.dynamic.admin.use_arrows_to_reorder') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($reorderMode)
                        <!-- Sortable List View for Reordering -->
                        <div class="space-y-2">
                            @foreach($menuItems as $index => $item)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center">
                                        <!-- Order Number -->
                                        <div class="mr-4 text-gray-400">
                                            <span class="text-lg font-semibold">{{ $item->order + 1 }}</span>
                                        </div>
                                        
                                        <!-- Item Info -->
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                @if($item->parent)
                                                    <span class="text-gray-500 mr-2">↳</span>
                                                @endif
                                                <span class="font-medium text-gray-800">{{ $item->name }}</span>
                                                @if($item->parent)
                                                    <span class="ml-2 text-sm text-gray-500">({{ __('menu.dynamic.admin.under') }} {{ $item->parent->name }})</span>
                                                @endif
                                            </div>
                                            @if($item->route_name)
                                                <span class="text-xs text-gray-600">{{ $item->route_name }}</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Order Actions -->
                                        <div class="flex items-center space-x-1">
                                            <button 
                                                wire:click="moveUp({{ $item->id }})"
                                                class="p-2 text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors {{ $loop->first ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                title="{{ __('menu.dynamic.admin.move_up') }}"
                                                {{ $loop->first ? 'disabled' : '' }}>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            </button>
                                            <button 
                                                wire:click="moveDown({{ $item->id }})"
                                                class="p-2 text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors {{ $loop->last ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                title="{{ __('menu.dynamic.admin.move_down') }}"
                                                {{ $loop->last ? 'disabled' : '' }}>
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Regular Table View -->
                        <x-dynamic-table
                            :headers="[
                                __('menu.dynamic.admin.fields.display_name'), 
                                __('menu.dynamic.admin.fields.icon'), 
                                __('menu.dynamic.admin.fields.route'), 
                                __('menu.dynamic.admin.fields.parent'),
                                __('menu.dynamic.admin.fields.visible'),
                                __('menu.dynamic.admin.fields.order'),
                                __('menu.dynamic.admin.actions')
                            ]">
                            @foreach($menuItems as $item)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3">
                                        <span class="font-medium text-slate-800">{{ $item->name }}</span>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($item->icon)
                                            <div class="flex items-center space-x-2">
                                                <x-icon name="heroicon-o-{{ $item->icon }}" class="w-5 h-5 text-slate-600" />
                                                <span class="text-xs text-slate-500">{{ $item->icon }}</span>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-sm">{{ __('menu.dynamic.admin.no_icon') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3">
                                        @if($item->route_name)
                                            <div class="flex flex-col">
                                                <span class="text-sm text-slate-700 font-mono bg-slate-100 px-2 py-1 rounded">{{ $item->route_name }}</span>
                                                @php
                                                    try {
                                                        $routeUrl = route($item->route_name, $item->route_parameters ?: []);
                                                        // Remove the domain to show just the path
                                                        $routePath = parse_url($routeUrl, PHP_URL_PATH);
                                                    } catch (\Exception $e) {
                                                        $routePath = null;
                                                    }
                                                @endphp
                                                @if($routePath)
                                                    <span class="text-xs text-blue-600 mt-1">{{ $routePath }}</span>
                                                @else
                                                    <span class="text-xs text-red-500 mt-1">{{ __('menu.dynamic.admin.invalid_route') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-sm">{{ __('menu.dynamic.admin.no_route') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($item->parent)
                                            <span class="text-sm text-slate-600">{{ $item->parent->name }}</span>
                                        @else
                                            <span class="text-slate-400 text-sm">{{ __('menu.dynamic.admin.top_level') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($item->visible)
                                            <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ __('menu.dynamic.admin.visible') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path>
                                                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path>
                                            </svg>
                                            {{ __('menu.dynamic.admin.hidden') }}
                                        </span>
                                    @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            <button 
                                                wire:click="moveUp({{ $item->id }})"
                                                class="p-1 text-gray-400 hover:text-primary rounded hover:bg-gray-100"
                                                title="{{ __('menu.dynamic.admin.move_up') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            </button>
                                            <span class="text-sm text-slate-600 w-8 text-center">{{ $item->order }}</span>
                                            <button 
                                                wire:click="moveDown({{ $item->id }})"
                                                class="p-1 text-gray-400 hover:text-primary rounded hover:bg-gray-100"
                                                title="{{ __('menu.dynamic.admin.move_down') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="flex items-center space-x-2">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    wire:click="editItem({{ $item->id }})">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                {{ __('menu.dynamic.admin.edit') }}
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    wire:click="confirmDelete({{ $item->id }})">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                {{ __('menu.dynamic.admin.delete') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </x-dynamic-table>
                    @endif

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $menuItems->links() }}
                    </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('menu.dynamic.no_items_available') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('menu.dynamic.admin.get_started_by_adding') }}</p>
                            <div class="mt-6">
                                <button class="btn btn-primary" wire:click="addItem">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('menu.dynamic.admin.add_item') }}
                                </button>
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            @elseif($activeTab === 'groups')
                <!-- Menu Groups Tab -->
                <div>
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <h2 class="text-lg font-semibold text-slate-800">
                                {{ __('menu.dynamic.admin.menu_groups_for', ['menu' => $selectedMenuType]) }}
                            </h2>
                            <div class="flex items-center gap-4">
                                <div class="flex-1 md:max-w-md">
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.search_groups') }}"
                                           wire:model.live.debounce.300ms="search">
                                </div>
                                <button class="btn btn-primary" wire:click="addGroup">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('menu.dynamic.admin.add_group') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        @if($menuGroups->count() > 0)
                            <!-- Groups Table -->
                            <x-dynamic-table
                                :headers="[
                                    __('menu.dynamic.admin.fields.group_name'), 
                                    __('menu.dynamic.admin.fields.machine_name'), 
                                    __('menu.dynamic.admin.fields.description'), 
                                    __('menu.dynamic.admin.fields.visibility'),
                                    __('menu.dynamic.admin.fields.items_count'),
                                    __('menu.dynamic.admin.fields.default'),
                                    __('menu.dynamic.admin.fields.active'),
                                    __('menu.dynamic.admin.fields.order'),
                                    __('menu.dynamic.admin.actions')
                                ]">
                                @foreach($menuGroups as $group)
                                    <tr>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                                            <div class="flex items-center">
                                                @if($group->icon)
                                                    <svg class="w-5 h-5 text-slate-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                                    </svg>
                                                @endif
                                                <span class="font-medium text-slate-800">{{ $group->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                                            <span class="text-sm text-slate-600 font-mono">{{ $group->machine_name }}</span>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                                            <span class="text-sm text-slate-600">{{ Str::limit($group->description, 50) }}</span>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($group->visibility_type === 'all')
                                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ __('menu.dynamic.admin.all_users') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ count($group->required_roles ?? []) }} {{ __('menu.dynamic.admin.roles') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                {{ $group->item_count }} {{ __('menu.dynamic.admin.items') }}
                                            </span>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($group->is_default)
                                                <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ __('menu.dynamic.admin.default') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($group->active)
                                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                    {{ __('menu.dynamic.admin.active') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                                    {{ __('menu.dynamic.admin.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <div class="flex items-center space-x-1">
                                                <button 
                                                    wire:click="moveGroupUp({{ $group->id }})"
                                                    class="p-1 text-gray-400 hover:text-primary rounded hover:bg-gray-100"
                                                    title="{{ __('menu.dynamic.admin.move_up') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                </button>
                                                <span class="text-sm text-slate-600 w-8 text-center">{{ $group->order }}</span>
                                                <button 
                                                    wire:click="moveGroupDown({{ $group->id }})"
                                                    class="p-1 text-gray-400 hover:text-primary rounded hover:bg-gray-100"
                                                    title="{{ __('menu.dynamic.admin.move_down') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                            <div class="flex items-center space-x-2">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        wire:click="editGroup({{ $group->id }})">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    {{ __('menu.dynamic.admin.edit') }}
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        wire:click="confirmDeleteGroup({{ $group->id }})">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    {{ __('menu.dynamic.admin.delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-dynamic-table>

                            <!-- Pagination -->
                            <div class="mt-6">
                                {{ $menuGroups->links() }}
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('menu.dynamic.admin.no_groups_available') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ __('menu.dynamic.admin.groups_help_organize') }}</p>
                                <div class="mt-6">
                                    <button class="btn btn-primary" wire:click="addGroup">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        {{ __('menu.dynamic.admin.add_group') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Add/Edit Item Modal -->
    @if($showItemModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[1100]" wire:click="closeModal">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-5/6 lg:w-4/5 xl:w-3/4 2xl:w-2/3 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingItemId ? __('menu.dynamic.admin.edit_item') : __('menu.dynamic.admin.add_item') }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4 max-h-[70vh] overflow-y-auto">
                        <form wire:submit.prevent="saveItem">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.display_name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.placeholders.display_name') }}"
                                           wire:model="name">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('menu.dynamic.admin.display_name_help') }}</p>
                                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Icon -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.icon') }}
                                    </label>
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.placeholders.icon') }}"
                                           wire:model="icon">
                                    @error('icon') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>


                                <!-- Route Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.route') }}
                                    </label>
                                    <select class="form-select w-full" wire:model="route_name">
                                        <option value="">{{ __('menu.dynamic.admin.placeholders.route') }}</option>
                                        @foreach($availableRoutes as $route)
                                            @php
                                                try {
                                                    $routeUrl = route($route);
                                                    $routePath = parse_url($routeUrl, PHP_URL_PATH);
                                                    $displayText = $route . ' → ' . $routePath;
                                                } catch (\Exception $e) {
                                                    $displayText = $route . ' (invalid route)';
                                                }
                                            @endphp
                                            <option value="{{ $route }}">{{ $displayText }}</option>
                                        @endforeach
                                    </select>
                                    @error('route_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    @if($route_name)
                                        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm">
                                            <strong>{{ __('menu.dynamic.admin.preview_url') }}:</strong>
                                            @php
                                                try {
                                                    $previewUrl = route($route_name);
                                                    $previewPath = parse_url($previewUrl, PHP_URL_PATH);
                                                } catch (\Exception $e) {
                                                    $previewPath = null;
                                                }
                                            @endphp
                                            @if($previewPath)
                                                <code class="text-blue-700">{{ $previewPath }}</code>
                                            @else
                                                <span class="text-red-600">{{ __('menu.dynamic.admin.invalid_route') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Parent Item -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.parent') }}
                                    </label>
                                    <select class="form-select w-full" wire:model="parent_id">
                                        <option value="">{{ __('menu.dynamic.admin.top_level') }}</option>
                                        @foreach($parentItems as $parentItem)
                                            <option value="{{ $parentItem->id }}">{{ $parentItem->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Menu Group -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.menu_group') }}
                                    </label>
                                    <select class="form-select w-full" wire:model="menu_group_id">
                                        <option value="">{{ __('menu.dynamic.admin.no_group') }}</option>
                                        @foreach($availableGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('menu.dynamic.admin.group_help') }}</p>
                                    @error('menu_group_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Route Parameters (JSON) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.route_parameters') }}
                                    </label>
                                    <textarea class="form-textarea w-full" 
                                              rows="3"
                                              placeholder='{"id": 1, "slug": "example"}'
                                              wire:model="route_parameters_json"></textarea>
                                    <div class="mt-1 text-xs text-gray-500">
                                        <p>{{ __('menu.dynamic.admin.json_format_help') }}</p>
                                        <p class="mt-1"><strong>{{ __('menu.dynamic.admin.examples') }}:</strong></p>
                                        <ul class="ml-4 list-disc">
                                            <li><code>{"filter[committee]": "diving"}</code> - {{ __('menu.dynamic.admin.example_filter') }}</li>
                                            <li><code>{"id": 1}</code> - {{ __('menu.dynamic.admin.example_id') }}</li>
                                            <li><code>{"type": "entity", "status": "active"}</code> - {{ __('menu.dynamic.admin.example_multiple') }}</li>
                                        </ul>
                                    </div>
                                    @error('route_parameters_json') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Active Patterns -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.active_patterns') }}
                                    </label>
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.placeholders.active_patterns') }}"
                                           wire:model="active_patterns_text">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('menu.dynamic.admin.active_patterns_help') }}</p>
                                    @error('active_patterns_text') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Access Control (Roles/Permissions) -->
                                <div class="md:col-span-2">
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('menu.dynamic.admin.fields.access_control') }}
                                        </label>
                                        
                                        <!-- Toggle Switch between Roles and Permissions -->
                                        <div class="flex items-center space-x-3 mb-3">
                                            <button type="button" 
                                                    wire:click="toggleSelectionMode"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $useRoleSelection ? 'bg-indigo-600' : 'bg-gray-200' }}">
                                                <span class="translate-x-0 pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $useRoleSelection ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                            </button>
                                            <span class="text-sm text-gray-700">
                                                {{ $useRoleSelection ? __('menu.dynamic.admin.select_by_roles') : __('menu.dynamic.admin.select_by_permissions') }}
                                            </span>
                                            <button type="button" 
                                                    wire:click="toggleSelectionMode" 
                                                    class="text-xs text-indigo-600 hover:text-indigo-500 underline">
                                                {{ __('menu.dynamic.admin.switch_to') }} {{ $useRoleSelection ? __('menu.dynamic.admin.permissions') : __('menu.dynamic.admin.roles') }}
                                            </button>
                                        </div>

                                        <!-- Help Text -->
                                        <p class="text-xs text-gray-500 mb-2">
                                            @if($useRoleSelection)
                                                {{ __('menu.dynamic.admin.role_selection_help') }}
                                            @else
                                                {{ __('menu.dynamic.admin.permission_selection_help') }}
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Role Selection -->
                                    @if($useRoleSelection)
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wider">
                                                {{ __('menu.dynamic.admin.select_roles') }}
                                            </div>
                                            <div class="max-h-32 overflow-y-auto space-y-2">
                                                @forelse($allRoles as $role)
                                                    <div class="flex items-center">
                                                        <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                                               type="checkbox" 
                                                               value="{{ $role->name }}" 
                                                               id="role_{{ $role->id }}"
                                                               wire:model.live="selectedRoles">
                                                        <label class="ml-2 text-sm text-gray-700 flex-1" for="role_{{ $role->id }}">
                                                            <span class="font-medium">{{ $role->name }}</span>
                                                            @if($role->description)
                                                                <span class="text-xs text-gray-500 ml-2">{{ $role->description }}</span>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500">{{ __('menu.dynamic.admin.no_roles_available') }}</p>
                                                @endforelse
                                            </div>
                                            
                                            <!-- Show resulting permissions -->
                                            @if(!empty($permissions))
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <div class="text-xs font-semibold text-gray-600 mb-1">
                                                        {{ __('menu.dynamic.admin.resulting_permissions') }}:
                                                    </div>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($permissions as $permission)
                                                            <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                                                                {{ $permission }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Permission Selection (grouped by scope) -->
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wider">
                                                {{ __('menu.dynamic.admin.select_permissions') }}
                                            </div>
                                            <div class="max-h-64 overflow-y-auto space-y-3">
                                                @forelse($groupedPermissions as $scope => $scopePermissions)
                                                    <div>
                                                        <div class="flex items-center mb-1">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                                {{ $scope === 'system' ? 'bg-purple-100 text-purple-700' : '' }}
                                                                {{ $scope === 'federation' ? 'bg-blue-100 text-blue-700' : '' }}
                                                                {{ $scope === 'entity' ? 'bg-green-100 text-green-700' : '' }}
                                                                {{ $scope === 'individual' ? 'bg-orange-100 text-orange-700' : '' }}
                                                                {{ !$scope ? 'bg-gray-100 text-gray-600' : '' }}">
                                                                {{ $scope ? ucfirst($scope) : __('menu.dynamic.admin.uncategorized') }}
                                                            </span>
                                                        </div>
                                                        <div class="ml-4 space-y-1">
                                                            @foreach($scopePermissions as $permission)
                                                                <div class="flex items-center">
                                                                    <input class="form-checkbox h-4 w-4 text-indigo-600"
                                                                           type="checkbox"
                                                                           value="{{ $permission->name }}"
                                                                           id="permission_{{ $permission->id }}"
                                                                           wire:model="permissions">
                                                                    <label class="ml-2 text-sm text-gray-700" for="permission_{{ $permission->id }}">
                                                                        {{ $permission->name }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500">{{ __('menu.dynamic.admin.no_permissions_available') }}</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <p class="text-xs text-gray-500 mt-2">
                                        <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ __('menu.dynamic.admin.access_control_info') }}
                                    </p>
                                </div>

                                <!-- Order and Visibility -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.order') }}
                                    </label>
                                    <input type="number" 
                                           class="form-input w-full" 
                                           min="0"
                                           wire:model="order">
                                    @error('order') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.visible') }}
                                    </label>
                                    <div class="flex items-center">
                                        <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                               type="checkbox" 
                                               id="visible"
                                               wire:model="visible">
                                        <label class="ml-2 text-sm text-gray-700" for="visible">
                                            {{ __('menu.dynamic.admin.item_visible') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            {{ __('menu.dynamic.admin.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveItem">
                            {{ $editingItemId ? __('menu.dynamic.admin.save_changes') : __('menu.dynamic.admin.add_item') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[1100]" wire:click="closeDeleteModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('menu.dynamic.admin.confirm_delete_title') }}</h3>
                        <button wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5C2.962 18.333 3.924 20 5.464 20z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('menu.dynamic.admin.confirm_delete') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ __('menu.dynamic.admin.delete_warning') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            {{ __('menu.dynamic.admin.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteItem">
                            {{ __('menu.dynamic.admin.delete_item') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add/Edit Group Modal -->
    @if($showGroupModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[1100]" wire:click="closeGroupModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingGroupId ? __('menu.dynamic.admin.edit_group') : __('menu.dynamic.admin.add_group') }}
                        </h3>
                        <button wire:click="closeGroupModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <form wire:submit.prevent="saveGroup">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.group_name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.placeholders.group_name') }}"
                                           wire:model="groupName">
                                    @error('groupName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Machine Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.machine_name') }}
                                    </label>
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.placeholders.machine_name') }}"
                                           wire:model="groupMachineName">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('menu.dynamic.admin.machine_name_help') }}</p>
                                    @error('groupMachineName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Icon -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.icon') }}
                                    </label>
                                    <input type="text" 
                                           class="form-input w-full" 
                                           placeholder="{{ __('menu.dynamic.admin.placeholders.icon') }}"
                                           wire:model="groupIcon">
                                    @error('groupIcon') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.description') }}
                                    </label>
                                    <textarea class="form-textarea w-full" 
                                              rows="3"
                                              placeholder="{{ __('menu.dynamic.admin.placeholders.group_description') }}"
                                              wire:model="groupDescription"></textarea>
                                    @error('groupDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Order -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.order') }}
                                    </label>
                                    <input type="number" 
                                           class="form-input w-full" 
                                           min="0"
                                           wire:model="groupOrder">
                                    @error('groupOrder') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Status Options -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.status') }}
                                    </label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                                   type="checkbox" 
                                                   id="groupActive"
                                                   wire:model="groupActive">
                                            <label class="ml-2 text-sm text-gray-700" for="groupActive">
                                                {{ __('menu.dynamic.admin.group_active') }}
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                                   type="checkbox" 
                                                   id="groupIsDefault"
                                                   wire:model="groupIsDefault">
                                            <label class="ml-2 text-sm text-gray-700" for="groupIsDefault">
                                                {{ __('menu.dynamic.admin.group_is_default') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Role Visibility -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('menu.dynamic.admin.fields.role_visibility') }}
                                    </label>
                                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded p-3">
                                        @forelse($allRoles as $role)
                                            <div class="flex items-center mb-2">
                                                <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                                       type="checkbox" 
                                                       value="{{ $role->name }}" 
                                                       id="group_role_{{ $role->id }}"
                                                       wire:model="groupRequiredRoles">
                                                <label class="ml-2 text-sm text-gray-700" for="group_role_{{ $role->id }}">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500">{{ __('menu.dynamic.admin.no_roles_available') }}</p>
                                        @endforelse
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('menu.dynamic.admin.role_visibility_help') }}</p>
                                    @error('groupRequiredRoles') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeGroupModal">
                            {{ __('menu.dynamic.admin.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveGroup">
                            {{ $editingGroupId ? __('menu.dynamic.admin.save_changes') : __('menu.dynamic.admin.add_group') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Group Confirmation Modal -->
    @if($showDeleteGroupModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[1100]" wire:click="closeDeleteGroupModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('menu.dynamic.admin.confirm_delete_group_title') }}</h3>
                        <button wire:click="closeDeleteGroupModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5C2.962 18.333 3.924 20 5.464 20z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('menu.dynamic.admin.confirm_delete_group') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ __('menu.dynamic.admin.delete_group_warning') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteGroupModal">
                            {{ __('menu.dynamic.admin.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteGroup">
                            {{ __('menu.dynamic.admin.delete_group') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

