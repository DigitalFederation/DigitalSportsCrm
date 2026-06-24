<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">{{ __('Create District') }}</h1>
        </div>

        <form action="{{ route('admin.districts.store') }}" method="POST">
            @csrf
            
            <div class="card">
                <div class="flex flex-col md:flex-row md:-mr-px">
                    <section class="mb-4 w-full">
                        <!-- Information Box -->
                        <x-information-box 
                            title="{{ __('District Information') }}" 
                            body="{{ __('Districts are geographic subdivisions that can be grouped into zones for organizational purposes.') }}">
                        </x-information-box>

                        <!-- Input Fields -->
                        <div class="flex flex-wrap -mx-4 space-y-4 md:space-y-0">
                            <!-- District Name -->
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="name">
                                    {{ __('District Name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input id="name" class="form-input w-full" type="text" 
                                       name="name" value="{{ old('name') }}" required />
                                @error('name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- District Code -->
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="code">
                                    {{ __('District Code') }}
                                </label>
                                <input id="code" class="form-input w-full" type="text" 
                                       name="code" value="{{ old('code') }}" 
                                       placeholder="{{ __('Optional unique code') }}" />
                                @error('code')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="is_active">
                                    {{ __('Status') }}
                                </label>
                                <select id="is_active" class="form-input w-full" name="is_active">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                                        {{ __('Active') }}
                                    </option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                                        {{ __('Inactive') }}
                                    </option>
                                </select>
                                @error('is_active')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="w-full px-4">
                                <label class="block text-sm font-medium mb-1" for="description">
                                    {{ __('Description') }}
                                </label>
                                <textarea id="description" class="form-input w-full" name="description" rows="3" 
                                          placeholder="{{ __('Optional description of the district') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <x-forms.card-form-submit 
                    backRoute="admin.districts.index"
                    buttonText="{{ __('Create District') }}">
                </x-forms.card-form-submit>
            </div>
        </form>
    </div>
</x-layout>