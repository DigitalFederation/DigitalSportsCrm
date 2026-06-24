<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">{{ __('permission_management.permission_details') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                
                @if(!$isSystemPermission)
                    <!-- Edit button -->
                    <a href="{{ route('admin.permission-management.edit', $permission) }}" 
                       class="btn btn-secondary">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M11.7.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM4.6 14H2v-2.6l6-6L10.6 8l-6 6zM12 6.6L9.4 4 11 2.4 13.6 5 12 6.6z" />
                        </svg>
                        <span class="ml-2">{{ __('permission_management.edit') }}</span>
                    </a>
                    
                    <!-- Delete button -->
                    <form action="{{ route('admin.permission-management.destroy', $permission) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('{{ __('permission_management.messages.confirm_delete_message') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path d="M5 7h2v6H5zM9 7h2v6H9z" />
                                <path d="M12 1c0-.6-.4-1-1-1H5c-.6 0-1 .4-1 1v2H0v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V5h1V3h-4V1zM6 2h4v1H6V2zm7 3v9H3V5h10z" />
                            </svg>
                            <span class="ml-2">{{ __('permission_management.delete') }}</span>
                        </button>
                    </form>
                @endif
                
            </div>

        </div>

        <div class="grid grid-cols-12 gap-6">

            <!-- Permission Details -->
            <div class="col-span-full xl:col-span-8 card">
                <header class="px-5 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">{{ __('permission_management.permission') }}</h2>
                </header>
                <div class="p-5">
                    
                    <!-- Permission info -->
                    <div class="space-y-4">
                        
                        <!-- Name -->
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.name') }}</div>
                            <div class="flex items-center">
                                <div class="text-lg font-medium text-gray-800">{{ $permission->name }}</div>
                                @if($isSystemPermission)
                                    <div class="ml-3 inline-flex font-medium bg-amber-100 text-amber-600 rounded-full text-center px-2.5 py-0.5 text-xs">
                                        {{ __('permission_management.system_permission') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Display Name -->
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.display_name') }}</div>
                            <div class="text-gray-800">{{ $permission->display_name }}</div>
                        </div>
                        
                        <!-- Category -->
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.category') }}</div>
                            @if($permission->category)
                                <div class="inline-flex font-medium bg-slate-100 text-gray-600 rounded-full text-center px-2.5 py-0.5">
                                    {{ ucfirst($permission->category) }}
                                </div>
                            @else
                                <div class="text-slate-400">{{ __('permission_management.uncategorized') }}</div>
                            @endif
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.description') }}</div>
                            <div class="text-gray-800">{{ $permission->description ?? '-' }}</div>
                        </div>
                        
                        <!-- Guard -->
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.guard_name') }}</div>
                            <div class="text-gray-800">{{ $permission->guard_name }}</div>
                        </div>
                        
                        <!-- Timestamps -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.created_at') }}</div>
                                <div class="text-gray-800">{{ $permission->created_at->format('M d, Y H:i') }}</div>
                                @if($permission->createdBy)
                                    <div class="text-sm text-gray-500">{{ __('role_management.by') }} {{ $permission->createdBy->name }}</div>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-600 mb-1">{{ __('permission_management.updated_at') }}</div>
                                <div class="text-gray-800">{{ $permission->updated_at->format('M d, Y H:i') }}</div>
                                @if($permission->updatedBy)
                                    <div class="text-sm text-gray-500">{{ __('role_management.by') }} {{ $permission->updatedBy->name }}</div>
                                @endif
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
            </div>

            <!-- Deletion Impact -->
            <div class="col-span-full xl:col-span-4 space-y-6">
                
                <!-- Impact Analysis -->
                <div class="card">
                    <header class="px-5 py-4 border-b border-gray-200">
                        <h2 class="font-semibold text-gray-800">{{ __('permission_management.deletion_impact') }}</h2>
                    </header>
                    <div class="p-5">
                        <div class="space-y-3">
                            
                            <!-- Affected Roles -->
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('permission_management.affected_roles') }}:</span>
                                <span class="text-sm font-medium text-gray-800">{{ $impact['affected_roles'] }}</span>
                            </div>
                            
                            <!-- Affected Users -->
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('permission_management.affected_users') }}:</span>
                                <span class="text-sm font-medium text-gray-800">{{ $impact['affected_users'] }}</span>
                            </div>
                            
                            <!-- Affected Routes -->
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">{{ __('permission_management.affected_routes') }}:</span>
                                <span class="text-sm font-medium text-gray-800">{{ $impact['affected_routes'] }}</span>
                            </div>
                            
                        </div>
                        
                        @if($isSystemPermission)
                            <div class="mt-4 rounded bg-amber-50 p-3">
                                <div class="flex">
                                    <svg class="w-4 h-4 shrink-0 fill-current text-amber-400 mt-0.5 mr-2" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                    </svg>
                                    <div class="text-sm text-amber-800">
                                        {{ __('permission_management.errors.cannot_delete_system_permission') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                    </div>
                </div>
                
                <!-- Routes using this permission -->
                @if($permission->routePermissions->count() > 0)
                    <div class="card">
                        <header class="px-5 py-4 border-b border-gray-200">
                            <h2 class="font-semibold text-gray-800">{{ __('permission_management.routes_using') }}</h2>
                        </header>
                        <div class="p-5">
                            <ul class="space-y-2">
                                @foreach($permission->routePermissions as $routePermission)
                                    <li class="text-sm">
                                        <code class="text-xs bg-slate-100 text-gray-600 px-1 py-0.5 rounded">
                                            {{ $routePermission->route_pattern }}
                                        </code>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                
            </div>

        </div>

        <!-- Roles with this permission -->
        @if($rolesWithPermission->count() > 0)
            <div class="mt-6 card">
                <header class="px-5 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-800">{{ __('permission_management.roles_list') }} ({{ $rolesWithPermission->count() }})</h2>
                </header>
                <div class="p-5">
                    
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <!-- Table header -->
                            <thead class="text-xs font-semibold uppercase text-gray-500 bg-slate-50 border-t border-b border-slate-200">
                                <tr>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('role_management.role_name') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('role_management.category') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center">{{ __('role_management.users') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center">{{ __('role_management.protection_level') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <!-- Table body -->
                            <tbody class="text-sm divide-y divide-slate-200">
                                @foreach($rolesWithPermission as $role)
                                    <tr>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <div class="font-medium text-gray-800">{{ $role->name }}</div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($role->category)
                                                <div class="inline-flex font-medium bg-slate-100 text-gray-600 rounded-full text-center px-2.5 py-0.5">
                                                    {{ ucfirst($role->category) }}
                                                </div>
                                            @else
                                                <div class="text-slate-400">-</div>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <div class="text-center">{{ $role->users_count }}</div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <div class="text-center">
                                                @if($role->protection_level === 'system')
                                                    <div class="inline-flex font-medium bg-red-100 text-red-600 rounded-full text-center px-2.5 py-0.5">
                                                        {{ __('role_management.protection_levels.system') }}
                                                    </div>
                                                @elseif($role->protection_level === 'admin')
                                                    <div class="inline-flex font-medium bg-amber-100 text-amber-600 rounded-full text-center px-2.5 py-0.5">
                                                        {{ __('role_management.protection_levels.admin') }}
                                                    </div>
                                                @else
                                                    <div class="inline-flex font-medium bg-slate-100 text-gray-500 rounded-full text-center px-2.5 py-0.5">
                                                        {{ __('role_management.protection_levels.user') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                            <div class="flex items-center justify-end">
                                                <a href="{{ route('admin.role-management.show', $role) }}" 
                                                   class="text-slate-400 hover:text-gray-500 rounded-full">
                                                    <span class="sr-only">{{ __('common.view') }}</span>
                                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                        <path d="M16 20c2.2 0 4-1.8 4-4s-1.8-4-4-4-4 1.8-4 4 1.8 4 4 4zm0-6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2z" />
                                                        <path d="M16 25C9.9 25 5 20.1 5 14s4.9-11 11-11 11 4.9 11 11-4.9 11-11 11zm0-20c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        @endif

        <!-- Back to list -->
        <div class="mt-6">
            <a href="{{ route('admin.permission-management.index') }}" class="text-blue-600 hover:underline">
                &larr; {{ __('permission_management.back_to_list') }}
            </a>
        </div>

    </div>
</x-layout>