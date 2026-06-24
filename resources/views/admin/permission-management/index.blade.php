<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">{{ __('permission_management.title') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                
                <!-- Export button -->
                <a href="{{ route('admin.permission-management.export') }}" 
                   class="btn btn-info">
                    {{ __('permission_management.export') }}
                </a>
                
                <!-- Import button -->
                <a href="{{ route('admin.permission-management.import') }}" 
                   class="btn btn-info">
                    {{ __('permission_management.import') }}
                </a>
                
                <!-- Bulk create button -->
                <a href="{{ route('admin.permission-management.bulk-create') }}" 
                   class="btn btn-info">
                    {{ __('permission_management.bulk_create') }}
                </a>
                
                <!-- Create button -->
                <a href="{{ route('admin.permission-management.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2">{{ __('permission_management.create_permission') }}</span>
                </a>
                
            </div>

        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Permissions -->
            <div class="card">
                <div>
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('permission_management.total_permissions') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ __('common.all') }}
                        </span>
                    </header>
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('common.count') }}</div>
                    <div class="flex items-baseline">
                        <div class="text-3xl font-bold text-gray-900">{{ $statistics['total_permissions'] }}</div>
                    </div>
                </div>
            </div>
            
            <!-- System Permissions -->
            <div class="card">
                <div>
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('permission_management.system_permissions') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ __('permission_management.protected') }}
                        </span>
                    </header>
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('common.count') }}</div>
                    <div class="flex items-baseline">
                        <div class="text-3xl font-bold text-gray-900">{{ $statistics['system_permissions'] }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Permissions with Roles -->
            <div class="card">
                <div>
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('permission_management.permissions_with_roles') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ __('permission_management.in_use') }}
                        </span>
                    </header>
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('common.count') }}</div>
                    <div class="flex items-baseline">
                        <div class="text-3xl font-bold text-gray-900">{{ $statistics['permissions_with_roles'] }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Unused Permissions -->
            <div class="card">
                <div>
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800">{{ __('permission_management.unused_permissions') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ __('permission_management.not_used') }}
                        </span>
                    </header>
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('common.count') }}</div>
                    <div class="flex items-baseline">
                        <div class="text-3xl font-bold text-gray-900">{{ $statistics['unused_permissions'] }}</div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Filters -->
        <div class="mb-5">
            <form method="GET" action="{{ route('admin.permission-management.index') }}" class="grid grid-cols-12 gap-4">
                
                <!-- Search -->
                <div class="col-span-full sm:col-span-4">
                    <label class="sr-only">{{ __('common.search') }}</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="{{ __('permission_management.search_permissions') }}"
                        class="form-input w-full"
                    />
                </div>
                
                <!-- Category filter -->
                <div class="col-span-full sm:col-span-3">
                    <label class="sr-only">{{ __('permission_management.category') }}</label>
                    <select name="category" class="form-select w-full">
                        <option value="">{{ __('permission_management.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Usage filter -->
                <div class="col-span-full sm:col-span-3">
                    <label class="sr-only">{{ __('permission_management.filter_by_usage') }}</label>
                    <select name="has_roles" class="form-select w-full">
                        <option value="">{{ __('permission_management.filter_by_usage') }}</option>
                        <option value="yes" {{ request('has_roles') == 'yes' ? 'selected' : '' }}>
                            {{ __('permission_management.has_roles') }}
                        </option>
                        <option value="no" {{ request('has_roles') == 'no' ? 'selected' : '' }}>
                            {{ __('permission_management.no_roles') }}
                        </option>
                    </select>
                </div>
                
                <!-- Filter button -->
                <div class="col-span-full sm:col-span-2">
                    <button type="submit" class="btn btn-primary w-full">
                        {{ __('common.filter') }}
                    </button>
                </div>
                
            </form>
        </div>

        <!-- Table -->
        <div class="card">
            <header class="px-5 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-800">
                    {{ __('permission_management.permissions') }} 
                    <span class="text-gray-400 font-medium">({{ $permissions->total() }})</span>
                </h2>
            </header>
            <div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('permission_management.name') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('permission_management.category') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('permission_management.description') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('permission_management.roles_using') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('common.status') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                            @forelse($permissions as $permission)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="font-medium text-slate-800">{{ $permission->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($permission->category)
                                            <div class="inline-flex font-medium bg-slate-100 text-slate-600 rounded-full text-center px-2.5 py-0.5">
                                                {{ ucfirst($permission->category) }}
                                            </div>
                                        @else
                                            <div class="text-slate-400">{{ __('permission_management.uncategorized') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3">
                                        <div class="text-slate-600">
                                            {{ Str::limit($permission->description ?? '-', 50) }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-center">
                                            @if($permission->roles_count > 0)
                                                <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-0.5">
                                                    {{ $permission->roles_count }}
                                                </div>
                                            @else
                                                <div class="text-slate-400">0</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-center">
                                            @if(in_array($permission->name, ['access users', 'manage roles', 'manage permissions', 'manage role permissions', 'manage protected roles', 'access role management dashboard']))
                                                <div class="inline-flex font-medium bg-amber-100 text-amber-600 rounded-full text-center px-2.5 py-0.5">
                                                    {{ __('permission_management.system_permission') }}
                                                </div>
                                            @elseif($permission->roles_count > 0)
                                                <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-0.5">
                                                    {{ __('permission_management.in_use') }}
                                                </div>
                                            @else
                                                <div class="inline-flex font-medium bg-slate-100 text-slate-500 rounded-full text-center px-2.5 py-0.5">
                                                    {{ __('permission_management.not_used') }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="flex items-center justify-end space-x-1">
                                            
                                            <!-- View button -->
                                            <a href="{{ route('admin.permission-management.show', $permission) }}" 
                                               class="text-slate-400 hover:text-slate-500 rounded-full">
                                                <span class="sr-only">{{ __('permission_management.view') }}</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M16 24c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm0-14c-3.3 0-6 2.7-6 6s2.7 6 6 6 6-2.7 6-6-2.7-6-6-6z" />
                                                    <circle cx="16" cy="16" r="2" />
                                                </svg>
                                            </a>
                                            
                                            @if(!in_array($permission->name, ['access users', 'manage roles', 'manage permissions', 'manage role permissions', 'manage protected roles', 'access role management dashboard']))
                                                <!-- Edit button -->
                                                <a href="{{ route('admin.permission-management.edit', $permission) }}" 
                                                   class="text-slate-400 hover:text-slate-500 rounded-full">
                                                    <span class="sr-only">{{ __('permission_management.edit') }}</span>
                                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                        <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l9-9L21.6 13l-9 9zM29 8s-4.252-4-6-4-5.643 4-6 4c-.1.1 6.689 6.062 6 6 .266.234 6-6 6-6z" />
                                                    </svg>
                                                </a>
                                                
                                                <!-- Delete button -->
                                                <form action="{{ route('admin.permission-management.destroy', $permission) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('{{ __('permission_management.messages.confirm_delete_message') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-600 rounded-full">
                                                        <span class="sr-only">{{ __('permission_management.delete') }}</span>
                                                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                            <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                                            <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center text-slate-400">
                                        {{ __('permission_management.messages.no_permissions_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-5">
                    {{ $permissions->appends(request()->query())->links() }}
                </div>

            </div>
        </div>

    </div>
</x-layout>