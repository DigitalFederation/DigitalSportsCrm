@section('title', __('diving.upload_diving_certification'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="mb-8">
            <h1 class="page-first-title">{{ __('diving.upload_diving_certification') }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ __('diving.professional_certification_upload_info') }}</p>
        </div>

        <!-- Information Alert -->
        <div class="mb-6">
            <x-information-box 
                title="{{ __('diving.important_information') }}" 
                body="{{ __('diving.professional_certification_requirements') }}">
            </x-information-box>
        </div>

        <!-- Form Card -->
        <form action="{{ route('individual.diving_certifications.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card">
                <div class="flex flex-col md:flex-row md:-mr-px">
                    <section class="mb-4 w-full">
                        <!-- Sistema de Formação de Mergulho -->
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                            <div class="w-full">
                                <label for="certification_system" class="block text-sm font-medium mb-1">
                                    {{ __('diving.training_system') }} <span class="text-rose-500">*</span>
                                </label>
                                <select id="certification_system" 
                                        name="certification_system" 
                                        class="form-select w-full {{ $errors->has('certification_system') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="">{{ __('diving.select_training_system') }}</option>
                                    @foreach($certificationSystems as $key => $value)
                                        <option value="{{ $key }}" {{ old('certification_system') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->has('certification_system'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('certification_system') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Nome da Certificação -->
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="w-full">
                                <label for="certification_name" class="block text-sm font-medium mb-1">
                                    {{ __('diving.certification_name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="certification_name" 
                                       name="certification_name" 
                                       value="{{ old('certification_name') }}"
                                       class="form-input w-full {{ $errors->has('certification_name') ? 'border-rose-300' : '' }}"
                                       placeholder="{{ __('diving.certification_name_placeholder') }}"
                                       required>
                                @if ($errors->has('certification_name'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('certification_name') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Número da Certificação e Nível de Certificação Nacional -->
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="sm:w-1/2">
                                <label for="certification_number" class="block text-sm font-medium mb-1">
                                    {{ __('diving.certification_number') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="certification_number" 
                                       name="certification_number" 
                                       value="{{ old('certification_number') }}"
                                       class="form-input w-full {{ $errors->has('certification_number') ? 'border-rose-300' : '' }}"
                                       placeholder="{{ __('diving.certification_number_placeholder') }}"
                                       required>
                                @if ($errors->has('certification_number'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('certification_number') }}
                                    </div>
                                @endif
                            </div>
                            <div class="sm:w-1/2">
                                <label for="national_certification_level" class="block text-sm font-medium mb-1">
                                    {{ __('diving.national_certification_level') }} <span class="text-rose-500">*</span>
                                </label>
                                <select id="national_certification_level" 
                                        name="national_certification_level" 
                                        class="form-select w-full {{ $errors->has('national_certification_level') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="">{{ __('diving.select_certification_level') }}</option>
                                    <option value="diver_level_3" {{ old('national_certification_level') == 'diver_level_3' ? 'selected' : '' }}>
                                        {{ __('diving.diver_level_3_dive_leader') }}
                                    </option>
                                    <option value="instructor_level_1" {{ old('national_certification_level') == 'instructor_level_1' ? 'selected' : '' }}>
                                        {{ __('diving.instructor_level_1') }}
                                    </option>
                                    <option value="instructor_level_2" {{ old('national_certification_level') == 'instructor_level_2' ? 'selected' : '' }}>
                                        {{ __('diving.instructor_level_2') }}
                                    </option>
                                    <option value="instructor_level_3" {{ old('national_certification_level') == 'instructor_level_3' ? 'selected' : '' }}>
                                        {{ __('diving.instructor_level_3') }}
                                    </option>
                                    <option value="first_aid_bls_oxygen" {{ old('national_certification_level') == 'first_aid_bls_oxygen' ? 'selected' : '' }}>
                                        {{ __('diving.first_aid_bls_oxygen') }}
                                    </option>
                                    <option value="compressor_operator" {{ old('national_certification_level') == 'compressor_operator' ? 'selected' : '' }}>
                                        {{ __('diving.compressor_operator') }}
                                    </option>
                                </select>
                                @if ($errors->has('national_certification_level'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('national_certification_level') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Data de Emissão e Data de Validade -->
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="sm:w-1/2">
                                <label for="issue_date" class="block text-sm font-medium mb-1">
                                    {{ __('diving.issue_date') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" 
                                       id="issue_date" 
                                       name="issue_date" 
                                       value="{{ old('issue_date') }}"
                                       max="{{ date('Y-m-d') }}"
                                       class="form-input w-full {{ $errors->has('issue_date') ? 'border-rose-300' : '' }}"
                                       required>
                                @if ($errors->has('issue_date'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('issue_date') }}
                                    </div>
                                @endif
                            </div>
                            <div class="sm:w-1/2">
                                <label for="expiration_date" class="block text-sm font-medium mb-1">
                                    {{ __('diving.expiration_date') }} <span class="text-gray-500">{{ __('(Optional)') }}</span>
                                </label>
                                <input type="date" 
                                       id="expiration_date" 
                                       name="expiration_date" 
                                       value="{{ old('expiration_date') }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       class="form-input w-full {{ $errors->has('expiration_date') ? 'border-rose-300' : '' }}">
                                @if ($errors->has('expiration_date'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('expiration_date') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Certificate Document -->
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="w-full">
                                <label for="certificate_document" class="block text-sm font-medium mb-1">
                                    {{ __('diving.certificate_document') }} <span class="text-rose-500">*</span>
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md {{ $errors->has('certificate_document') ? 'border-rose-300' : '' }}">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="certificate_document" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>{{ __('diving.upload_a_file') }}</span>
                                                <input id="certificate_document" 
                                                       name="certificate_document" 
                                                       type="file" 
                                                       class="sr-only"
                                                       accept=".pdf,.jpg,.jpeg,.png"
                                                       onchange="updateFileName(this)"
                                                       required>
                                            </label>
                                            <p class="pl-1">{{ __('diving.or_drag_drop') }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ __('diving.file_types_size') }}</p>
                                        <p id="file-name" class="text-sm text-gray-900 hidden"></p>
                                    </div>
                                </div>
                                @if ($errors->has('certificate_document'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('certificate_document') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>

                <x-forms.card-form-submit 
                    :back-route="'individual.diving_certifications.index'" 
                    :button-text="__('diving.upload_certification')" />
            </div>
        </form>

    </div>

    @push('scripts')
    <script>
        function updateFileName(input) {
            const fileName = input.files[0]?.name;
            const fileNameElement = document.getElementById('file-name');
            
            if (fileName) {
                fileNameElement.textContent = '{{ __("diving.file_selected") }} ' + fileName;
                fileNameElement.classList.remove('hidden');
            } else {
                fileNameElement.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-layout>