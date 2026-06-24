@section('title', __('role_management.edit_role'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center">
                    <h1 class="page-first-title">{{ __('role_management.edit_role') }}: {{ $role->display_name }}</h1>
                    @if($role->is_protected)
                        <svg class="w-6 h-6 ml-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>
                <p class="text-slate-600 text-sm">{{ __('role_management.edit_role_description') }}</p>
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

        <!-- Protection Notice -->
        @if($role->is_protected)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800">{{ __('role_management.protected_role_notice') }}</h3>
                        <p class="text-sm text-amber-700 mt-1">{{ __('role_management.protected_role_edit_warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Role Edit Form -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <form method="POST" action="{{ route('admin.role-management.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="p-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Role Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.role_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                                   placeholder="{{ __('role_management.role_name_placeholder') }}"
                                   class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.role_name_help') }}</p>
                        </div>

                        <!-- Display Name -->
                        <div>
                            <label for="display_name" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.display_name') }}
                            </label>
                            <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $role->display_name) }}"
                                   placeholder="{{ __('role_management.display_name_placeholder') }}"
                                   class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('display_name') border-red-300 @enderror">
                            @error('display_name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.display_name_help') }}</p>
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
                                    <option value="{{ $category }}" {{ old('category', $role->category) === $category ? 'selected' : '' }}>
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
                                    <option value="{{ $key }}" {{ old('scope', $role->scope) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scope')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.scope_help') }}</p>
                        </div>

                        <!-- Protection Level -->
                        <div>
                            <label for="protection_level" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('role_management.protection_level') }}
                            </label>
                            <select name="protection_level" id="protection_level" 
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('protection_level') border-red-300 @enderror">
                                <option value="user" {{ old('protection_level', $role->protection_level) === 'user' ? 'selected' : '' }}>
                                    {{ __('role_management.protection_levels.user') }}
                                </option>
                                <option value="admin" {{ old('protection_level', $role->protection_level) === 'admin' ? 'selected' : '' }}>
                                    {{ __('role_management.protection_levels.admin') }}
                                </option>
                                <option value="system" {{ old('protection_level', $role->protection_level) === 'system' ? 'selected' : '' }}>
                                    {{ __('role_management.protection_levels.system') }}
                                </option>
                            </select>
                            @error('protection_level')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-slate-500 text-xs mt-1">{{ __('role_management.protection_level_help') }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-8">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('role_management.role_description') }}
                        </label>
                        <textarea name="description" id="description" rows="3" 
                                  placeholder="{{ __('role_management.role_description_placeholder') }}"
                                  class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Protected Role Checkbox -->
                    <div class="mb-8">
                        <div class="flex items-center">
                            <div class="flex items-center h-5">
                                <input id="is_protected" name="is_protected" type="checkbox" value="1" 
                                       {{ old('is_protected', $role->is_protected) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-slate-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_protected" class="font-medium text-slate-700">{{ __('role_management.is_protected') }}</label>
                                <p class="text-slate-500">{{ __('role_management.is_protected_help') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Current Permissions Summary -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-slate-900 mb-3">{{ __('role_management.current_permissions') }}</h3>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="text-sm text-slate-600">
                                    <span class="font-medium">{{ $role->permissions->count() }}</span> {{ __('role_management.permissions_assigned') }}
                                </div>
                                @if($role->permissions->count() > 0)
                                    <div class="text-sm text-slate-600">
                                        {{ __('role_management.across_categories') }}: {{ $role->permissions->groupBy('category')->count() }}
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('admin.role-management.permissions', $role) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                {{ __('role_management.manage_permissions') }}
                            </a>
                        </div>
                    </div>

                    <!-- Role Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-white border border-slate-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-slate-800">{{ number_format($role->users_count) }}</div>
                            <div class="text-sm text-slate-500">{{ __('role_management.users_with_role') }}</div>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-slate-800">{{ number_format($role->permissions_count) }}</div>
                            <div class="text-sm text-slate-500">{{ __('role_management.permissions_count') }}</div>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-lg p-4">
                            <div class="text-2xl font-bold text-slate-800">{{ $role->updated_at?->diffForHumans() ?? __('role_management.never') }}</div>
                            <div class="text-sm text-slate-500">{{ __('role_management.last_updated') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between">
                    <a href="{{ route('admin.role-management.show', $role) }}" class="btn btn-secondary">
                        {{ __('role_management.cancel') }}
                    </a>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.role-management.permissions', $role) }}" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            {{ __('role_management.manage_permissions') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('role_management.save_changes') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</x-layout>