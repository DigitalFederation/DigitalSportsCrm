<div>
    <!-- Statistics Cards -->
    <div class="grid grid-cols-12 gap-6 mb-8">
        
        <!-- Total Routes -->
        <div class="card col-span-full sm:col-span-6 xl:col-span-3">
            <div class="card-body">
                <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('route_permissions.total_routes') }}</h2>
                <div class="text-3xl font-bold text-slate-800">{{ number_format($statistics['total_routes']) }}</div>
            </div>
        </div>
        
        <!-- Routes with Permissions -->
        <div class="card col-span-full sm:col-span-6 xl:col-span-3">
            <div class="card-body">
                <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('route_permissions.routes_with_permissions') }}</h2>
                <div class="text-3xl font-bold text-emerald-600">{{ number_format($statistics['with_permissions']) }}</div>
            </div>
        </div>
        
        <!-- Routes without Permissions -->
        <div class="card col-span-full sm:col-span-6 xl:col-span-3">
            <div class="card-body">
                <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('route_permissions.routes_without_permissions') }}</h2>
                <div class="text-3xl font-bold text-amber-600">{{ number_format($statistics['without_permissions']) }}</div>
            </div>
        </div>
        
        <!-- Protection Coverage -->
        <div class="card col-span-full sm:col-span-6 xl:col-span-3">
            <div class="card-body">
                <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('route_permissions.percentage_protected') }}</h2>
                <div class="text-3xl font-bold text-indigo-600">{{ $statistics['percentage_protected'] }}%</div>
                <div class="mt-2">
                    <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="bg-indigo-500 h-full" style="width: {{ $statistics['percentage_protected'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Filters -->
    <div class="card p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-slate-700 mb-2">{{ __('route_permissions.search_routes') }}</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="{{ __('route_permissions.search_routes') }}"
                       class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <!-- Module Filter -->
            <div>
                <label for="module" class="block text-sm font-medium text-slate-700 mb-2">{{ __('route_permissions.filter_by_module') }}</label>
                <select wire:model.live="module" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('route_permissions.all_modules') }}</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}">{{ ucfirst($mod) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Permission Filter -->
            <div>
                <label for="hasPermission" class="block text-sm font-medium text-slate-700 mb-2">{{ __('route_permissions.filter_by_permission') }}</label>
                <select wire:model.live="hasPermission" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('route_permissions.all_permissions') }}</option>
                    <option value="1">{{ __('route_permissions.has_permission') }}</option>
                    <option value="0">{{ __('route_permissions.no_permission') }}</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="flex items-end">
                <button wire:click="$set('search', ''); $set('module', ''); $set('hasPermission', '')" 
                        class="btn btn-secondary w-full">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    {{ __('common.clear') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Grouped Routes -->
    @forelse($groupedRoutes as $module => $moduleData)
        <div class="card mb-6">
            <header class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-slate-800">
                        {{ ucfirst($module ?: __('route_permissions.uncategorized')) }}
                        <span class="text-slate-400 font-medium ml-2">({{ $moduleData['total'] }})</span>
                    </h2>
                    <div class="flex items-center space-x-4 text-sm">
                        <span class="text-emerald-600">
                            <svg class="w-4 h-4 inline-block mr-1" viewBox="0 0 16 16">
                                <path fill="currentColor" d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm4.5 6.5l-5 5c-.3.3-.7.3-1 0l-3-3c-.3-.3-.3-.7 0-1s.7-.3 1 0L7 10l4.5-4.5c.3-.3.7-.3 1 0s.3.7 0 1z"/>
                            </svg>
                            {{ $moduleData['with_permissions'] }} {{ __('route_permissions.has_permission') }}
                        </span>
                        <span class="text-amber-600">
                            <svg class="w-4 h-4 inline-block mr-1" viewBox="0 0 16 16">
                                <path fill="currentColor" d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z"/>
                            </svg>
                            {{ $moduleData['without_permissions'] }} {{ __('route_permissions.no_permission') }}
                        </span>
                    </div>
                </div>
            </header>
            
            <!-- Module Routes Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('route_permissions.route_name') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('route_permissions.methods') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('route_permissions.current_permission') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('route_permissions.status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($moduleData['routes'] as $prefix => $routes)
                            @foreach($routes as $route)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                        {{ $route['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-center">
                                            @foreach($route['methods'] as $method)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($method == 'GET') bg-blue-100 text-blue-800
                                                    @elseif($method == 'POST') bg-green-100 text-green-800
                                                    @elseif($method == 'PUT' || $method == 'PATCH') bg-yellow-100 text-yellow-800
                                                    @elseif($method == 'DELETE') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ $method }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(!empty($route['current_permission']))
                                            @php
                                                $permissions = explode('|', $route['current_permission']);
                                                $displayCount = min(2, count($permissions));
                                                $remainingCount = count($permissions) - $displayCount;
                                            @endphp
                                            <div class="flex flex-wrap gap-1">
                                                @for($i = 0; $i < $displayCount; $i++)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ trim($permissions[$i]) }}
                                                    </span>
                                                @endfor
                                                @if($remainingCount > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600" 
                                                          title="{{ implode(', ', array_slice($permissions, $displayCount)) }}">
                                                        +{{ $remainingCount }} {{ __('common.more') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-slate-400">{{ __('route_permissions.no_permission_assigned') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($route['has_permission'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ __('common.protected') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ __('common.unprotected') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            @if($route['has_permission'])
                                                <!-- Edit Permission Assignment -->
                                                <button wire:click="editPermission('{{ $route['name'] }}', '{{ $route['current_permission'] }}')"
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                        title="{{ __('common.edit') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Remove Permission -->
                                                <button wire:click="removePermission('{{ $route['name'] }}')"
                                                        wire:confirm="{{ __('route_permissions.confirm_remove_permission') }}"
                                                        class="text-red-600 hover:text-red-900"
                                                        title="{{ __('common.delete') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @else
                                                <!-- Assign Permission - Open Modal -->
                                                <button wire:click="assignPermission('{{ $route['name'] }}')"
                                                        class="text-green-600 hover:text-green-900"
                                                        title="{{ __('route_permissions.assign_permission') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('route_permissions.messages.no_routes_found') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('route_permissions.messages.try_adjusting_filters') }}</p>
        </div>
    @endforelse

    <!-- Edit Permission Modal -->
    <div x-data="{ show: @entangle('showEditModal') }"
         x-show="show"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="show = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.stop
                 class="relative bg-white rounded-lg shadow-xl w-full max-w-lg">

                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('route_permissions.edit_permission_assignment') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('route_permissions.route_name') }}</label>
                            <input type="text" value="{{ $editingRoute }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
                        </div>

                        <!-- Current Permissions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('route_permissions.current_permissions') }}</label>
                            @if(empty($editingPermissions))
                                <p class="text-sm text-gray-500">{{ __('route_permissions.no_permissions_assigned') }}</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($editingPermissions as $permission)
                                        <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded">
                                            <span class="text-sm text-gray-700">{{ $permission }}</span>
                                            <button wire:click="removePermissionFromRoute('{{ $permission }}')"
                                                    class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Add New Permission -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('route_permissions.add_permission') }}</label>
                            <div class="flex space-x-2">
                                <select wire:model="editingPermission" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('route_permissions.select_permission') }}</option>
                                    @foreach($permissionsByCategory as $category => $perms)
                                        <optgroup label="{{ $category ?: __('common.uncategorized') }}">
                                            @foreach($perms as $permission)
                                                @if(!in_array($permission->name, $editingPermissions))
                                                    <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <button wire:click="addPermissionToRoute"
                                        class="btn btn-primary whitespace-nowrap">
                                    {{ __('common.add') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeModal" class="btn btn-secondary">{{ __('common.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Permission Modal -->
    <div x-data="{ show: @entangle('showAssignModal') }"
         x-show="show"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="show = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-lg shadow-xl w-full max-w-md">

                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('route_permissions.assign_permission') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('route_permissions.route_name') }}</label>
                            <input type="text" value="{{ $editingRoute }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('route_permissions.permission') }}</label>
                            <select wire:model="editingPermission" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('route_permissions.select_permission') }}</option>
                                @foreach($permissionsByCategory as $category => $perms)
                                    <optgroup label="{{ $category ?: __('common.uncategorized') }}">
                                        @foreach($perms as $permission)
                                            <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('editingPermission') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="editingIsActive" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ __('route_permissions.active') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button wire:click="closeAssignModal" class="btn btn-secondary">{{ __('common.cancel') }}</button>
                        <button wire:click="savePermission" class="btn btn-primary">{{ __('route_permissions.assign') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>