@section('title', __('role_management.create_role'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('role_management.create_role') }}</h1>
                <p class="text-slate-600 text-sm">{{ __('role_management.create_role_description') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.role-management.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('role_management.back_to_roles') }}
                </a>
            </div>

        </div>

        <!-- Role Creation Form -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <form method="POST" action="{{ route('admin.role-management.store') }}">
                @csrf

                <div class="p-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Role Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.role_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   placeholder="{{ __('role_management.role_name_placeholder') }}"
                                   class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.role_name_help') }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.role_category') }}
                            </label>
                            <select name="category" id="category"
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category') border-red-300 @enderror">
                                <option value="">{{ __('role_management.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>
                                        {{ ucwords($category) }}
                                    </option>
                                @endforeach
                                <option value="custom">{{ __('role_management.custom_category') }}</option>
                            </select>
                            @error('category')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Scope -->
                        <div>
                            <label for="scope" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.scope') }}
                            </label>
                            <select name="scope" id="scope"
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('scope') border-red-300 @enderror">
                                <option value="">{{ __('role_management.select_scope') }}</option>
                                @foreach($scopes as $key => $label)
                                    <option value="{{ $key }}" {{ old('scope') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scope')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.scope_help') }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-8">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('role_management.role_description') }}
                        </label>
                        <textarea name="description" id="description" rows="3" 
                                  placeholder="{{ __('role_management.role_description_placeholder') }}"
                                  class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Protection Settings -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Protection Level -->
                        <div>
                            <label for="protection_level" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.protection_level') }}
                            </label>
                            <select name="protection_level" id="protection_level" 
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('protection_level') border-red-300 @enderror">
                                <option value="user" {{ old('protection_level', 'user') === 'user' ? 'selected' : '' }}>
                                    {{ __('role_management.protection_levels.user') }}
                                </option>
                                <option value="admin" {{ old('protection_level') === 'admin' ? 'selected' : '' }}>
                                    {{ __('role_management.protection_levels.admin') }}
                                </option>
                                <option value="system" {{ old('protection_level') === 'system' ? 'selected' : '' }}>
                                    {{ __('role_management.protection_levels.system') }}
                                </option>
                            </select>
                            @error('protection_level')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.protection_level_help') }}</p>
                        </div>

                        <!-- Protected Role Checkbox -->
                        <div class="flex items-center">
                            <div class="flex items-center h-5">
                                <input id="is_protected" name="is_protected" type="checkbox" value="1" 
                                       {{ old('is_protected') ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_protected" class="font-medium text-slate-700">{{ __('role_management.is_protected') }}</label>
                                <p class="text-slate-500">{{ __('role_management.is_protected_help') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Role Templates -->
                    @if($templates->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-slate-900 mb-4">{{ __('role_management.role_templates') }}</h3>
                            <p class="text-slate-600 text-sm mb-4">{{ __('role_management.role_templates_description') }}</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($templates as $template)
                                    <div class="border border-slate-200 rounded-lg p-4 hover:border-indigo-300 cursor-pointer template-card" 
                                         data-template="{{ $template->id }}" data-permissions="{{ json_encode($template->permissions) }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-slate-900">{{ $template->name }}</h4>
                                            @if($template->category)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ ucwords($template->category) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($template->description)
                                            <p class="text-sm text-slate-600 mb-3">{{ $template->description }}</p>
                                        @endif
                                        <div class="text-xs text-slate-500">
                                            {{ count($template->permissions ?? []) }} {{ __('role_management.permissions_included') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Permissions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-slate-900 mb-4">{{ __('role_management.assign_permissions') }}</h3>
                        <p class="text-slate-600 text-sm mb-4">{{ __('role_management.assign_permissions_description') }}</p>
                        
                        <div class="space-y-6">
                            @php $groupedPermissions = $permissions->groupBy('category') @endphp
                            @foreach($groupedPermissions as $category => $categoryPermissions)
                                <div class="border border-slate-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-medium text-slate-900">
                                            {{ $category ?: __('role_management.uncategorized') }}
                                        </h4>
                                        <button type="button" class="text-sm text-indigo-600 hover:text-indigo-800 select-all-category" 
                                                data-category="{{ $category }}">
                                            {{ __('role_management.select_all') }}
                                        </button>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($categoryPermissions as $permission)
                                            <div class="flex items-center">
                                                <input id="permission_{{ $permission->id }}" name="permissions[]" type="checkbox" 
                                                       value="{{ $permission->name }}" 
                                                       {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded permission-checkbox"
                                                       data-category="{{ $category }}">
                                                <label for="permission_{{ $permission->id }}" class="ml-3 text-sm text-slate-700">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">
                    <a href="{{ route('admin.role-management.index') }}" class="btn btn-secondary">
                        {{ __('role_management.cancel') }}
                    </a>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('role_management.create_role') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        // Template selection
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove active state from all cards
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('border-indigo-500', 'bg-indigo-50'));
                
                // Add active state to clicked card
                this.classList.add('border-indigo-500', 'bg-indigo-50');
                
                // Get template permissions
                let permissions = [];
                try {
                    permissions = JSON.parse(this.dataset.permissions || '[]');
                } catch (e) {
                    console.warn('Invalid JSON in template permissions:', e);
                    permissions = [];
                }
                
                // Uncheck all permissions first
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
                
                // Check permissions from template
                permissions.forEach(permission => {
                    const checkbox = document.querySelector(`input[value="${permission}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            });
        });

        // Select all in category
        document.querySelectorAll('.select-all-category').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.dataset.category;
                const checkboxes = document.querySelectorAll(`input[data-category="${category}"]`);
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                
                checkboxes.forEach(cb => cb.checked = !allChecked);
                this.textContent = allChecked ? '{{ __("role_management.select_all") }}' : '{{ __("role_management.deselect_all") }}';
            });
        });
    </script>
    @endpush
</x-layout>