<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">{{ __('Create Zone') }}</h1>
        </div>

        <form action="{{ route('admin.zones.store') }}" method="POST">
            @csrf
            
            <div class="card">
                <div class="flex flex-col md:flex-row md:-mr-px">
                    <section class="mb-4 w-full">
                        <!-- Information Box -->
                        <x-information-box 
                            title="{{ __('Zone Information') }}" 
                            body="{{ __('Zones are custom geographic groupings that can contain multiple districts from different countries. They are used for organizational and reporting purposes.') }}">
                        </x-information-box>

                        <!-- Input Fields -->
                        <div class="flex flex-wrap -mx-4 space-y-4 md:space-y-0">
                            <!-- Zone Name -->
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="name">
                                    {{ __('Zone Name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input id="name" class="form-input w-full" type="text" 
                                       name="name" value="{{ old('name') }}" required />
                                @error('name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Zone Code -->
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="code">
                                    {{ __('Zone Code') }}
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
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="description">
                                    {{ __('Description') }}
                                </label>
                                <textarea id="description" class="form-input w-full" name="description" rows="3" 
                                          placeholder="{{ __('Optional description of the zone') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Districts Selection -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium mb-3">
                                {{ __('Select Districts') }} <span class="text-rose-500">*</span>
                            </label>
                            
                            @if($districts->isNotEmpty())
                                <div class="space-y-4">
                                    @foreach($districts as $countryName => $countryDistricts)
                                        <div class="border border-slate-200 rounded-lg p-4">
                                            <h4 class="font-medium text-slate-900 mb-3">{{ $countryName }}</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                @foreach($countryDistricts as $district)
                                                    <label class="flex items-center space-x-3 p-2 bg-slate-50 rounded hover:bg-slate-100 cursor-pointer">
                                                        <input type="checkbox" name="district_ids[]" value="{{ $district->id }}" 
                                                               class="form-checkbox" 
                                                               {{ in_array($district->id, old('district_ids', [])) ? 'checked' : '' }}>
                                                        <div>
                                                            <div class="font-medium text-sm">{{ $district->name }}</div>
                                                            @if($district->code)
                                                                <div class="text-xs text-slate-500">{{ $district->code }}</div>
                                                            @endif
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-slate-500">
                                    <p>{{ __('No districts available. Please create districts first.') }}</p>
                                    <a href="{{ route('admin.districts.create') }}" class="btn btn-primary mt-4">
                                        {{ __('Create District') }}
                                    </a>
                                </div>
                            @endif

                            @error('district_ids')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </section>
                </div>

                <x-forms.card-form-submit 
                    backRoute="admin.zones.index"
                    buttonText="{{ __('Create Zone') }}">
                </x-forms.card-form-submit>
            </div>
        </form>
    </div>

    <script>
        // Select All functionality for each country
        document.addEventListener('DOMContentLoaded', function() {
            const countryGroups = document.querySelectorAll('.border.border-slate-200');
            
            countryGroups.forEach(group => {
                const checkboxes = group.querySelectorAll('input[type="checkbox"]');
                
                // Add "Select All" button for each country
                const header = group.querySelector('h4');
                if (header && checkboxes.length > 1) {
                    const selectAllBtn = document.createElement('button');
                    selectAllBtn.type = 'button';
                    selectAllBtn.className = 'text-xs text-indigo-600 hover:text-indigo-800 ml-2';
                    selectAllBtn.textContent = 'Select All';
                    
                    selectAllBtn.addEventListener('click', function() {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        checkboxes.forEach(cb => cb.checked = !allChecked);
                        selectAllBtn.textContent = allChecked ? 'Select All' : 'Deselect All';
                    });
                    
                    header.appendChild(selectAllBtn);
                }
            });
        });
    </script>
</x-layout>