<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">{{ __('permission_management.create_permission') }}</h1>
        </div>

        <div class="card p-6">
            
            <!-- Form -->
            <form action="{{ route('admin.permission-management.store') }}" method="POST">
                @csrf

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
                            value="{{ old('name') }}"
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
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
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
                                value="{{ old('category') }}"
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
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Guard -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="guard_name">
                            {{ __('permission_management.guard_name') }}
                        </label>
                        <select 
                            id="guard_name" 
                            name="guard_name" 
                            class="form-select w-full @error('guard_name') border-rose-300 @enderror"
                        >
                            @foreach($guards as $guard)
                                <option value="{{ $guard }}" {{ old('guard_name', 'web') == $guard ? 'selected' : '' }}>
                                    {{ $guard }}
                                </option>
                            @endforeach
                        </select>
                        @error('guard_name')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Help text -->
                    <div class="rounded bg-gray-50 p-4">
                        <div class="flex">
                            <svg class="w-4 h-4 shrink-0 fill-current text-gray-400 mt-0.5 mr-2" viewBox="0 0 16 16">
                                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                            </svg>
                            <div class="text-sm text-gray-600">
                                <div class="font-medium mb-1">{{ __('permission_management.help.naming_convention') }}</div>
                                <div>{{ __('permission_management.help.categories') }}</div>
                            </div>
                        </div>
                    </div>

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
                            {{ __('permission_management.create') }}
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
            const oldCategory = '{{ old('category') }}';
            
            if (oldCategory && !Array.from(select.options).some(option => option.value === oldCategory)) {
                select.value = 'new';
                toggleCategoryInput();
                input.value = oldCategory;
            }
        });
    </script>
</x-layout>