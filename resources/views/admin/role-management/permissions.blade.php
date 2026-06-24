@section('title', __('role_management.manage_permissions'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center">
                    <h1 class="page-first-title">{{ __('role_management.manage_permissions') }}: {{ $role->display_name }}</h1>
                    @if($role->is_protected)
                        <svg class="w-6 h-6 ml-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
                <p class="text-slate-600 text-sm">{{ __('role_management.manage_permissions_description') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.role-management.show', $role) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('role_management.back_to_role') }}
                </a>
            </div>

        </div>

        <!-- Role Information -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-slate-900">{{ $role->display_name }}</h3>
                    <p class="text-sm text-slate-600">{{ $role->description ?: __('role_management.no_description') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-slate-800">{{ number_format($role->permissions_count) }}</div>
                        <div class="text-xs text-slate-500">{{ __('role_management.current_permissions') }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-slate-800">{{ number_format($role->users_count) }}</div>
                        <div class="text-xs text-slate-500">{{ __('role_management.affected_users') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Form -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <form method="POST" action="{{ route('admin.role-management.permissions.update', $role) }}">
                @csrf
                @method('PUT')

                <!-- Search and Filter Controls -->
                <div class="p-6 border-b border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="permission_search" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.search_permissions') }}
                            </label>
                            <input type="text" id="permission_search" placeholder="{{ __('role_management.search_permissions_placeholder') }}"
                                   class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Category Filter -->
                        <div>
                            <label for="category_filter" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.filter_by_category') }}
                            </label>
                            <select id="category_filter" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('role_management.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ ucwords($category ?: __('role_management.uncategorized')) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex items-end space-x-2">
                            <button type="button" id="select_all" class="btn btn-secondary">
                                {{ __('role_management.select_all') }}
                            </button>
                            <button type="button" id="clear_all" class="btn btn-secondary">
                                {{ __('role_management.clear_all') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Permissions List -->
                <div class="p-6">
                    <div class="space-y-8">
                        @php $groupedPermissions = $permissions->groupBy('category') @endphp
                        @foreach($groupedPermissions as $category => $categoryPermissions)
                            <div class="permission-category" data-category="{{ $category }}">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-slate-900">
                                        {{ $category ?: __('role_management.uncategorized') }}
                                        <span class="text-sm font-normal text-slate-500">({{ $categoryPermissions->count() }})</span>
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800 select-category" 
                                                data-category="{{ $category }}">
                                            {{ __('role_management.select_all') }}
                                        </button>
                                        <button type="button" class="text-sm text-slate-600 hover:text-slate-800 clear-category" 
                                                data-category="{{ $category }}">
                                            {{ __('role_management.clear_all') }}
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($categoryPermissions as $permission)
                                            <div class="permission-item flex items-center p-2 bg-white rounded border border-slate-200 hover:border-indigo-300" 
                                                 data-permission="{{ $permission->name }}" data-category="{{ $category }}">
                                                <input id="permission_{{ $permission->id }}" name="permissions[]" type="checkbox" 
                                                       value="{{ $permission->name }}" 
                                                       {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}
                                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded permission-checkbox">
                                                <label for="permission_{{ $permission->id }}" class="ml-3 flex-1 cursor-pointer">
                                                    <div class="text-sm font-medium text-slate-700">{{ $permission->name }}</div>
                                                    @if($permission->description)
                                                        <div class="text-xs text-slate-500">{{ $permission->description }}</div>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('permissions')
                        <p class="text-red-600 text-sm mt-4">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Permission Summary -->
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-slate-600">
                                <span class="font-medium" id="selected_count">{{ $role->permissions_count }}</span> 
                                {{ __('role_management.permissions_selected') }}
                            </div>
                            <div class="text-sm text-slate-600">
                                {{ __('role_management.total_available') }}: {{ $permissions->count() }}
                            </div>
                        </div>
                        <div class="text-sm text-slate-500">
                            {{ __('role_management.changes_will_affect') }} {{ number_format($role->users_count) }} {{ __('role_management.users') }}
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">
                    <a href="{{ route('admin.role-management.show', $role) }}" class="btn btn-secondary">
                        {{ __('role_management.cancel') }}
                    </a>
                    
                    <div class="flex space-x-3">
                        <button type="button" id="preview_changes" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            {{ __('role_management.preview_changes') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('role_management.save_permissions') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        // Permission management functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('permission_search');
            const categoryFilter = document.getElementById('category_filter');
            const selectAllBtn = document.getElementById('select_all');
            const clearAllBtn = document.getElementById('clear_all');
            const selectedCountSpan = document.getElementById('selected_count');
            const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

            // Update selected count
            function updateSelectedCount() {
                const checkedCount = document.querySelectorAll('.permission-checkbox:checked').length;
                selectedCountSpan.textContent = checkedCount;
            }

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('.permission-item').forEach(item => {
                    const permissionName = item.querySelector('label').textContent.toLowerCase();
                    const matches = permissionName.includes(searchTerm);
                    item.style.display = matches ? 'flex' : 'none';
                });
            });

            // Category filter
            categoryFilter.addEventListener('change', function() {
                const selectedCategory = this.value;
                document.querySelectorAll('.permission-category').forEach(category => {
                    const categoryName = category.dataset.category;
                    const matches = selectedCategory === '' || selectedCategory === categoryName;
                    category.style.display = matches ? 'block' : 'none';
                });
            });

            // Select all permissions
            selectAllBtn.addEventListener('click', function() {
                const visibleCheckboxes = document.querySelectorAll('.permission-item:not([style*="display: none"]) .permission-checkbox');
                visibleCheckboxes.forEach(checkbox => checkbox.checked = true);
                updateSelectedCount();
            });

            // Clear all permissions
            clearAllBtn.addEventListener('click', function() {
                const visibleCheckboxes = document.querySelectorAll('.permission-item:not([style*="display: none"]) .permission-checkbox');
                visibleCheckboxes.forEach(checkbox => checkbox.checked = false);
                updateSelectedCount();
            });

            // Category select/clear
            document.querySelectorAll('.select-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    const category = this.dataset.category;
                    const categoryCheckboxes = document.querySelectorAll(`.permission-item[data-category="${category}"] .permission-checkbox`);
                    categoryCheckboxes.forEach(checkbox => checkbox.checked = true);
                    updateSelectedCount();
                });
            });

            document.querySelectorAll('.clear-category').forEach(btn => {
                btn.addEventListener('click', function() {
                    const category = this.dataset.category;
                    const categoryCheckboxes = document.querySelectorAll(`.permission-item[data-category="${category}"] .permission-checkbox`);
                    categoryCheckboxes.forEach(checkbox => checkbox.checked = false);
                    updateSelectedCount();
                });
            });

            // Update count when checkboxes change
            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Preview changes
            document.getElementById('preview_changes').addEventListener('click', function() {
                const checkedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
                    .map(cb => cb.value);
                const originalPermissions = @json($role->permissions->pluck('name'));
                
                const added = checkedPermissions.filter(p => !originalPermissions.includes(p));
                const removed = originalPermissions.filter(p => !checkedPermissions.includes(p));
                
                let message = '{{ __("role_management.changes_preview") }}:\n\n';
                
                if (added.length > 0) {
                    message += '{{ __("role_management.permissions_to_add") }} (' + added.length + '):\n';
                    message += added.map(p => '+ ' + p).join('\n') + '\n\n';
                }
                
                if (removed.length > 0) {
                    message += '{{ __("role_management.permissions_to_remove") }} (' + removed.length + '):\n';
                    message += removed.map(p => '- ' + p).join('\n') + '\n\n';
                }
                
                if (added.length === 0 && removed.length === 0) {
                    message += '{{ __("role_management.no_changes_detected") }}';
                }
                
                alert(message);
            });
        });
    </script>
    @endpush
</x-layout>