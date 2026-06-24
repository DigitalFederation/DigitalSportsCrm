@section('title', $role->display_name)
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center">
                    <h1 class="page-first-title">{{ $role->display_name }}</h1>
                    @if($role->is_protected)
                        <svg class="w-6 h-6 ml-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
                <p class="text-slate-600 text-sm">{{ $role->description ?: __('role_management.no_description') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.role-management.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('role_management.back_to_roles') }}
                </a>

                @if($protectionInfo['can_modify'])
                    <a href="{{ route('admin.role-management.edit', $role) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('role_management.edit') }}
                    </a>

                    <a href="{{ route('admin.role-management.permissions', $role) }}" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        {{ __('role_management.manage_permissions') }}
                    </a>

                    <form method="POST" action="{{ route('admin.role-management.duplicate', $role) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            {{ __('role_management.duplicate') }}
                        </button>
                    </form>
                @endif
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Role Details -->
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-medium text-slate-900">{{ __('role_management.role_details') }}</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.role_name') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $role->name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.display_name') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $role->display_name }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.role_category') }}</dt>
                                <dd class="mt-1">
                                    @if($role->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ ucwords($role->category) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-sm">{{ __('role_management.uncategorized') }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.protection_level') }}</dt>
                                <dd class="mt-1">
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
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.users_count') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ number_format($role->users_count) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.permissions_count') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ number_format($role->permissions_count) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.created_by') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $role->createdBy->name ?? __('role_management.system_user') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500">{{ __('role_management.last_modified') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900">
                                    {{ $role->updated_at?->format('Y-m-d H:i') ?? __('role_management.never') }}
                                    @if($role->updatedBy)
                                        <span class="text-slate-500">{{ __('role_management.by') }} {{ $role->updatedBy->name }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if($role->description)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-slate-500 mb-2">{{ __('role_management.role_description') }}</dt>
                                <dd class="text-sm text-slate-900 bg-slate-50 rounded-md p-3">{{ $role->description }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Permissions -->
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-slate-900">{{ __('role_management.permissions') }} ({{ $role->permissions_count }})</h3>
                        @if($protectionInfo['can_modify'])
                            <a href="{{ route('admin.role-management.permissions', $role) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                {{ __('role_management.manage_permissions') }}
                            </a>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($role->permissions->count() > 0)
                            @php $groupedPermissions = $role->permissions->groupBy('category') @endphp
                            <div class="space-y-6">
                                @foreach($groupedPermissions as $category => $categoryPermissions)
                                    <div>
                                        <h4 class="font-medium text-slate-900 mb-3">
                                            {{ $category ?: __('role_management.uncategorized') }}
                                            <span class="text-slate-500 text-sm font-normal">({{ $categoryPermissions->count() }})</span>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($categoryPermissions as $permission)
                                                <div class="flex items-center p-2 bg-slate-50 rounded">
                                                    <svg class="w-4 h-4 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="text-sm text-slate-700">{{ $permission->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <p class="text-slate-500">{{ __('role_management.no_permissions_assigned') }}</p>
                                @if($protectionInfo['can_modify'])
                                    <a href="{{ route('admin.role-management.permissions', $role) }}" class="btn btn-primary mt-4">
                                        {{ __('role_management.assign_permissions') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Security Information -->
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-medium text-slate-900">{{ __('role_management.security_info') }}</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">{{ __('role_management.is_protected') }}</span>
                            @if($role->is_protected)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ __('role_management.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('role_management.no') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">{{ __('role_management.can_modify') }}</span>
                            @if($protectionInfo['can_modify'])
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('role_management.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('role_management.no') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">{{ __('role_management.can_delete') }}</span>
                            @if($protectionInfo['can_delete'])
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('role_management.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('role_management.no') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Deletion Impact -->
                @if($impact['has_users'] || $impact['affected_permissions'] > 0)
                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-medium text-slate-900">{{ __('role_management.deletion_impact') }}</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @if($impact['has_users'])
                                <div class="flex items-center p-3 bg-amber-50 rounded-lg">
                                    <svg class="w-5 h-5 text-amber-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-amber-800">{{ __('role_management.users_will_lose_access') }}</p>
                                        <p class="text-sm text-amber-700">{{ __('role_management.affected_users') }}: {{ $impact['user_count'] }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($impact['affected_permissions'] > 0)
                                <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-blue-800">{{ __('role_management.permissions_will_be_removed') }}</p>
                                        <p class="text-sm text-blue-700">{{ __('role_management.affected_permissions') }}: {{ $impact['affected_permissions'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Activity Summary -->
                @if(!empty($auditSummary['total_activities']))
                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-medium text-slate-900">{{ __('role_management.recent_activity') }}</h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-slate-800">{{ $auditSummary['total_activities'] ?? 0 }}</div>
                                <div class="text-sm text-slate-500">{{ __('role_management.total_activities') }}</div>
                            </div>
                            
                            @if(!empty($auditSummary['last_activity']))
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <div class="text-sm text-slate-600">{{ __('role_management.last_activity') }}</div>
                                    <div class="text-sm text-slate-900">{{ $auditSummary['last_activity'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                @if($protectionInfo['can_modify'] || $protectionInfo['can_delete'])
                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-medium text-slate-900">{{ __('role_management.quick_actions') }}</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($protectionInfo['can_modify'])
                                <a href="{{ route('admin.role-management.permissions', $role) }}" class="block w-full text-center btn btn-secondary">
                                    {{ __('role_management.manage_permissions') }}
                                </a>
                                
                                <form method="POST" action="{{ route('admin.role-management.duplicate', $role) }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full btn btn-secondary">
                                        {{ __('role_management.duplicate_role') }}
                                    </button>
                                </form>
                            @endif

                            @if($protectionInfo['can_delete'])
                                <form method="POST" action="{{ route('admin.role-management.destroy', $role) }}" 
                                      onsubmit="return confirm('{{ __('role_management.confirm_delete_role') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full btn btn-danger">
                                        {{ __('role_management.delete_role') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layout>
