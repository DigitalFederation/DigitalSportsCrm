<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('diving.edit_certification_requirements') }}</h1>

            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.license-certification-requirements.show', $license) }}" 
                   class="btn btn-info">
                    {{ __('diving.back_to_license') }}
                </a>
            </div>
        </div>

        <!-- License Information -->
        <div class="bg-white shadow-lg rounded-sm mb-8">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('License') }}</h2>
                <p class="text-sm text-slate-600">{{ $license->name }}</p>
                @if($license->license_code)
                    <p class="text-xs text-slate-500 mt-1">{{ __('Code') }}: {{ $license->license_code }}</p>
                @endif
            </div>
        </div>

        <!-- Edit Form -->
        <form action="{{ route('admin.license-certification-requirements.update', $license) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-lg rounded-sm">
                <div class="p-6">
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-4">
                        {{ __('diving.certification_requirements') }}
                    </h3>

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-3">
                            {{ __('diving.required_certifications') }}
                        </label>
                        
                        <div class="space-y-3">
                            @foreach($availableCertificationLevels as $level => $displayName)
                                <label class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50">
                                    <input type="checkbox" 
                                           name="certification_levels[]" 
                                           value="{{ $level }}"
                                           class="form-checkbox h-4 w-4 text-blue-600"
                                           {{ in_array($level, $requirements) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">
                                            {{ $displayName }}
                                        </span>
                                        <p class="text-xs text-slate-600">
                                            {{ __('diving.certification_level_code') }}: {{ $level }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 text-sm text-blue-700">
                                    <p class="font-medium mb-1">{{ __('diving.note') }}</p>
                                    <p>{{ __('diving.select_certification_levels_help') }}</p>
                                </div>
                            </div>
                        </div>

                        @error('certification_levels')
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col px-0 py-5 border-t border-slate-200">
                        <div class="flex gap-4 self-start">
                            <button type="submit" class="btn btn-primary">
                                {{ __('diving.update_requirements') }}
                            </button>
                            
                            <a href="{{ route('admin.license-certification-requirements.show', $license) }}" 
                               class="btn btn-info">
                                {{ __('main.cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>