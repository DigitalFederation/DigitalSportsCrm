<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">{{ __('permission_management.edit_permission') }}</h1>
        </div>

        <div class="card p-6">
            
            <!-- Form -->
            <form action="{{ route('admin.permission-management.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">

                    <!-- Permission Name -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="name">
                            {{ __('permission_management.name') }} <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            id="name" 
                            name="name" 
                            class="form-input w-full @error('name') border-rose-300 @enderror" 
                            type="text" 
                            value="{{ old('name', $permission->name) }}"
                            placeholder="manage-users"
                            required
                        />
                        <div class="text-xs text-gray-500 mt-1">{{ __('permission_management.name_help') }}</div>
                        @error('name')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category (Scope) -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="category">
                            {{ __('permission_management.category') }}
                        </label>
                        <div class="flex">
                            <select
                                id="category-select"
                                class="form-select flex-1"
                                onchange="toggleCategoryInput()"
                            >
                                <option value="">{{ __('permission_management.select_category') }}</option>
                                @foreach($scopes as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $permission->category) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                                <option value="new">{{ __('permission_management.new_category') }}</option>
                            </select>
                            <input
                                id="category-input"
                                name="category"
                                class="form-input flex-1 ml-2 hidden @error('category') border-rose-300 @enderror"
                                type="text"
                                value="{{ old('category', $permission->category) }}"
                                placeholder="{{ __('permission_management.category') }}"
                            />
                        </div>
                        @error('category')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="description">
                            {{ __('permission_management.description') }}
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-textarea w-full @error('description') border-rose-300 @enderror" 
                            rows="3"
                            placeholder="{{ __('permission_management.description') }}"
                        >{{ old('description', $permission->description) }}</textarea>
                        @error('description')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Guard (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="guard_name_display">
                            {{ __('permission_management.guard_name') }}
                        </label>
                        <input 
                            id="guard_name_display" 
                            class="form-input w-full bg-gray-100" 
                            type="text" 
                            value="{{ $permission->guard_name }}"
                            disabled
                        />
                        <div class="text-xs text-gray-500 mt-1">{{ __('permission_management.help.guard_cannot_be_changed') }}</div>
                    </div>

                    <!-- Permission Info -->
                    <div class="rounded bg-gray-50 p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-3">{{ __('permission_management.permission_details') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">{{ __('permission_management.created_at') }}:</dt>
                                <dd class="text-sm font-medium text-gray-800">{{ $permission->created_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">{{ __('permission_management.updated_at') }}:</dt>
                                <dd class="text-sm font-medium text-gray-800">{{ $permission->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">{{ __('permission_management.roles_using') }}:</dt>
                                <dd class="text-sm font-medium text-gray-800">{{ $permission->roles->count() }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($permission->roles->count() > 0)
                        <!-- Roles using this permission -->
                        <div class="rounded bg-amber-50 p-4">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current text-amber-400 mt-0.5 mr-2" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                </svg>
                                <div class="text-sm">
                                    <div class="font-medium text-amber-800 mb-1">{{ __('permission_management.roles_using') }}:</div>
                                    <div class="text-amber-700">
                                        {{ $permission->roles->pluck('name')->implode(', ') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Form footer -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mt-6 pt-6 border-t">
                    <div class="mb-4 md:mb-0">
                        <a href="{{ route('admin.permission-management.index') }}" class="text-blue-600 hover:underline">
                            &larr; {{ __('permission_management.back_to_list') }}
                        </a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.permission-management.index') }}" 
                           class="btn btn-secondary">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ __('permission_management.save') }}
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <script>
        function toggleCategoryInput() {
            const select = document.getElementById('category-select');
            const input = document.getElementById('category-input');
            
            if (select.value === 'new') {
                select.classList.add('hidden');
                input.classList.remove('hidden');
                input.focus();
                input.value = '';
            } else {
                input.value = select.value;
            }
        }

        // Set initial value
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('category-select');
            const input = document.getElementById('category-input');
            const currentCategory = '{{ old('category', $permission->category) }}';
            
            if (currentCategory && !Array.from(select.options).some(option => option.value === currentCategory)) {
                select.value = 'new';
                toggleCategoryInput();
                input.value = currentCategory;
            } else if (currentCategory) {
                input.value = currentCategory;
            }
        });
    </script>
</x-layout>