@section('title', __('role_management.dashboard'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('role_management.dashboard') }}</h1>
                <p class="text-slate-600 text-sm">{{ __('role_management.manage_roles_permissions_description') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.role-management.create') }}" class="btn btn-primary" aria-label="{{ __('role_management.create_role') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('role_management.create_role') }}
                </a>
            </div>

        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Roles -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('role_management.statistics.total_roles') }}</h2>
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($statistics['total_roles']) }}</div>
                </div>
            </div>

            <!-- Protected Roles -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('role_management.statistics.protected_roles') }}</h2>
                    <div class="text-3xl font-bold text-amber-600">{{ number_format($statistics['protected_roles']) }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ __('role_management.statistics.system_roles') }}: {{ $statistics['system_roles'] }}</div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('role_management.statistics.users_with_roles') }}</h2>
                    <div class="text-3xl font-bold text-emerald-600">{{ number_format($statistics['users_with_roles']) }}</div>
                    <div class="text-sm text-slate-500 mt-1">{{ __('role_management.statistics.total_permissions') }}: {{ $statistics['total_permissions'] }}</div>
                </div>
            </div>

            <!-- Categories -->
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-medium text-slate-600 mb-2">{{ __('role_management.statistics.categories') }}</h2>
                    <div class="text-3xl font-bold text-indigo-600">{{ number_format($statistics['categories']) }}</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.role-management.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-slate-700 mb-2">{{ __('role_management.search_roles') }}</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="{{ __('role_management.search_roles') }}"
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Scope Filter -->
                <div>
                    <label for="scope" class="block text-sm font-medium text-slate-700 mb-2">{{ __('role_management.filter_by_scope') }}</label>
                    <select name="scope" id="scope" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('role_management.all_scopes') }}</option>
                        @foreach($scopes as $key => $label)
                            <option value="{{ $key }}" {{ request('scope') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Protection Level Filter -->
                <div>
                    <label for="protection_level" class="block text-sm font-medium text-slate-700 mb-2">{{ __('role_management.filter_by_protection') }}</label>
                    <select name="protection_level" id="protection_level" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('role_management.all_protection_levels') }}</option>
                        @foreach($protectionLevels as $level => $label)
                            <option value="{{ $level }}" {{ request('protection_level') === $level ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="submit" class="btn btn-secondary w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        {{ __('role_management.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Roles Table -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.role_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.scope') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.protection_level') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.users_count') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.permissions_count') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.last_modified') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('role_management.quick_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($roles as $role)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900 flex items-center">
                                                {{ $role->display_name }}
                                                @if($role->is_protected)
                                                    <svg class="w-4 h-4 ml-2 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="text-sm text-slate-500">{{ $role->name }}</div>
                                            @if($role->description)
                                                <div class="text-xs text-slate-400 mt-1">{{ Str::limit($role->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($role->scope === 'system')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ __('role_management.scopes.system') }}
                                        </span>
                                    @elseif($role->scope === 'federation')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('role_management.scopes.federation') }}
                                        </span>
                                    @elseif($role->scope === 'entity')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('role_management.scopes.entity') }}
                                        </span>
                                    @elseif($role->scope === 'individual')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            {{ __('role_management.scopes.individual') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($role->protection_level === 'system')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('role_management.protection_levels.system') }}
                                        </span>
                                    @elseif($role->protection_level === 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            {{ __('role_management.protection_levels.admin') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('role_management.protection_levels.user') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ number_format($role->users_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ number_format($role->permissions_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $role->last_modified ?? __('role_management.never') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route('admin.role-management.show', $role)" />
                                        @php($protectionInfo = $role->protection_info)
                                        @if($protectionInfo['can_modify'])
                                            <x-dynamic-table-buttons type="edit" :route="route('admin.role-management.edit', $role)" />
                                            <x-dynamic-table-buttons type="permissions" :route="route('admin.role-management.permissions', $role)" />
                                        @endif
                                        @if($protectionInfo['can_delete'])
                                            <x-dynamic-table-buttons type="delete" :route="route('admin.role-management.destroy', $role)" method="DELETE" :confirm-text="__('role_management.confirm_delete')" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-slate-900 mb-2">{{ __('role_management.no_roles_found') }}</p>
                                        <p class="text-slate-500">{{ __('role_management.no_roles_found_description') }}</p>
                                        <a href="{{ route('admin.role-management.create') }}" class="btn btn-primary mt-4">
                                            {{ __('role_management.create_first_role') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>

        <!-- Security Warning -->
        <div class="mt-8">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800">{{ __('role_management.security_notice') }}</h3>
                        <p class="text-sm text-amber-700 mt-1">{{ __('role_management.security_notice_description') }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layout>
