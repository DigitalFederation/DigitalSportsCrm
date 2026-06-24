@section('title', __('diving.edit_certification'))
<x-layout menu="individual">
    @php
        $certificateUrl = $certificateUrl;
    @endphp

    <div class="previous-layout-classes">
        
        <x-information-box 
            :title="__('diving.diving_certifications')"
            :body="__('diving.update_certification_info')">
        </x-information-box>

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <div>
                <h1 class="page-first-title">{{ __('diving.edit_certification') }}</h1>
            </div>
        </div>

        <form action="{{ route('individual.diving_certifications.update', $certification) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="certification_name" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.certification_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="certification_name" 
                                   name="certification_name" 
                                   type="text" 
                                   class="form-input w-full {{ $errors->has('certification_name') ? 'border-rose-300' : '' }}" 
                                   value="{{ old('certification_name', $certification->certification_name) }}"
                                   placeholder="{{ __('diving.certification_name_placeholder') }}"
                                   required>
                            @error('certification_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="certification_system" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.certification_system') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="certification_system" 
                                    name="certification_system" 
                                    class="form-select w-full {{ $errors->has('certification_system') ? 'border-rose-300' : '' }}" 
                                    required>
                                <option value="">{{ __('diving.select_a_system') }}</option>
                                <option value="SSI" {{ old('certification_system', $certification->certification_system) == 'SSI' ? 'selected' : '' }}>SSI</option>
                                <option value="PADI" {{ old('certification_system', $certification->certification_system) == 'PADI' ? 'selected' : '' }}>PADI</option>
                                <option value="SDI_TDI" {{ old('certification_system', $certification->certification_system) == 'SDI_TDI' ? 'selected' : '' }}>SDI/TDI</option>
                                <option value="DDI" {{ old('certification_system', $certification->certification_system) == 'DDI' ? 'selected' : '' }}>DDI</option>
                                <option value="GUE" {{ old('certification_system', $certification->certification_system) == 'GUE' ? 'selected' : '' }}>GUE</option>
                                <option value="CMAS" {{ old('certification_system', $certification->certification_system) == 'CMAS' ? 'selected' : '' }}>CMAS</option>
                            </select>
                            @error('certification_system')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="certification_level" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.certification_level') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="certification_level" 
                                   name="certification_level" 
                                   type="text" 
                                   class="form-input w-full {{ $errors->has('certification_level') ? 'border-rose-300' : '' }}" 
                                   value="{{ old('certification_level', $certification->certification_level) }}"
                                   placeholder="{{ __('diving.certification_level_placeholder') }}"
                                   required>
                            @error('certification_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="certification_number" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.certification_number') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="certification_number" 
                                   name="certification_number" 
                                   type="text" 
                                   class="form-input w-full {{ $errors->has('certification_number') ? 'border-rose-300' : '' }}" 
                                   value="{{ old('certification_number', $certification->certification_number) }}"
                                   placeholder="{{ __('diving.certification_number_placeholder') }}"
                                   required>
                            @error('certification_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.issue_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="issue_date" 
                                   name="issue_date" 
                                   type="date" 
                                   class="form-input w-full {{ $errors->has('issue_date') ? 'border-rose-300' : '' }}" 
                                   value="{{ old('issue_date', $certification->issue_date->format('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   required>
                            @error('issue_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.expiration_date') }}
                            </label>
                            <input id="expiration_date" 
                                   name="expiration_date" 
                                   type="date" 
                                   class="form-input w-full {{ $errors->has('expiration_date') ? 'border-rose-300' : '' }}" 
                                   value="{{ old('expiration_date', $certification->expiration_date?->format('Y-m-d')) }}"
                                   min="{{ date('Y-m-d') }}">
                            @error('expiration_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Document Type -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="document_type" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.document_type') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="document_type" 
                                    name="document_type" 
                                    class="form-select w-full {{ $errors->has('document_type') ? 'border-rose-300' : '' }}"
                                    required>
                                <option value="">{{ __('diving.select_document_type') }}</option>
                                <option value="medical_statement" {{ old('document_type', $certification->document_type) == 'medical_statement' ? 'selected' : '' }}>
                                    {{ __('diving.medical_statement') }}
                                </option>
                                <option value="professional_insurance" {{ old('document_type', $certification->document_type) == 'professional_insurance' ? 'selected' : '' }}>
                                    {{ __('diving.professional_insurance') }}
                                </option>
                                <option value="other" {{ old('document_type', $certification->document_type) == 'other' ? 'selected' : '' }}>
                                    {{ __('diving.other') }}
                                </option>
                            </select>
                            @error('document_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="national_equivalency" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.national_equivalency') }}
                            </label>
                            <input id="national_equivalency" 
                                   name="national_equivalency" 
                                   type="text" 
                                   class="form-input w-full {{ $errors->has('national_equivalency') ? 'border-rose-300' : '' }}" 
                                   value="{{ old('national_equivalency', $certification->national_equivalency) }}"
                                   placeholder="{{ __('diving.national_equivalency_placeholder') }}">
                            @error('national_equivalency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="national_certification_level" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.national_certification_level') }}
                            </label>
                            <select id="national_certification_level" 
                                    name="national_certification_level" 
                                    class="form-select w-full {{ $errors->has('national_certification_level') ? 'border-rose-300' : '' }}">
                                <option value="">{{ __('diving.select_certification_level') }}</option>
                                <option value="diver_level_3" {{ old('national_certification_level', $certification->national_certification_level) == 'diver_level_3' ? 'selected' : '' }}>
                                    {{ __('diving.diver_level_3_dive_leader') }}
                                </option>
                                <option value="instructor_level_1" {{ old('national_certification_level', $certification->national_certification_level) == 'instructor_level_1' ? 'selected' : '' }}>
                                    {{ __('diving.instructor_level_1') }}
                                </option>
                                <option value="instructor_level_2" {{ old('national_certification_level', $certification->national_certification_level) == 'instructor_level_2' ? 'selected' : '' }}>
                                    {{ __('diving.instructor_level_2') }}
                                </option>
                                <option value="instructor_level_3" {{ old('national_certification_level', $certification->national_certification_level) == 'instructor_level_3' ? 'selected' : '' }}>
                                    {{ __('diving.instructor_level_3') }}
                                </option>
                                <option value="first_aid_bls_oxygen" {{ old('national_certification_level', $certification->national_certification_level) == 'first_aid_bls_oxygen' ? 'selected' : '' }}>
                                    {{ __('diving.first_aid_bls_oxygen') }}
                                </option>
                                <option value="compressor_operator" {{ old('national_certification_level', $certification->national_certification_level) == 'compressor_operator' ? 'selected' : '' }}>
                                    {{ __('diving.compressor_operator') }}
                                </option>
                            </select>
                            @error('national_certification_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if($certificateUrl)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('diving.current_certificate') }}
                                </label>
                                <p class="text-sm text-gray-600">
                                    <a href="{{ $certificateUrl }}" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ __('diving.view_current_certificate') }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="certificate" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('diving.certificate_document') }}
                                @if(!$certificateUrl)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <p class="text-sm text-gray-500 mb-2">
                                {{ __('diving.upload_new_certificate_to_replace') }}
                            </p>
                            @livewire('individual.diving-certification-upload')
                            @error('certificate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('individual.diving_certifications.index') }}" class="btn btn-secondary">
                            {{ __('diving.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ __('diving.update_certification') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>