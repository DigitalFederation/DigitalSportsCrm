@section('title', __('diving.request_diving_license'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('diving.request_diving_license') }}</h1>
        </div>

        <!-- Information Box -->
        <div class="mb-6">
            <x-information-box 
                title="{{ __('diving.important_information') }}" 
                body="{{ __('diving.directors_need_certifications') . PHP_EOL . 
                       __('diving.professional_receives_email') . PHP_EOL . 
                       __('diving.must_accept_invitation') . PHP_EOL . 
                       __('diving.documents_required_license') }}">
            </x-information-box>
        </div>

        <form action="{{ route('entity.diving_licenses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="sm:flex sm:space-x-4">
                <div class="mb-8 w-full">
                    <div class="card">
                        <div class="grow flex flex-col gap-y-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                {{ __('diving.license_information') }}
                            </h3>

                            <section>
                                <!-- License Type -->
                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/2">
                                        <label for="license_id" class="block text-sm font-medium mb-1">
                                            {{ __('diving.license_type') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select id="license_id" name="license_id" required
                                                class="form-select w-full {{ $errors->has('license_id') ? 'border-rose-300' : '' }}">
                                            <option value="">{{ __('diving.select_license_type') }}</option>
                                            @foreach($availableLicenses as $license)
                                                <option value="{{ $license->id }}" {{ old('license_id') == $license->id ? 'selected' : '' }}>
                                                    {{ $license->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('license_id'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('license_id') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Technical Director -->
                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/2" x-data="{ selectedName: '' }">
                                        <label for="technical_director_id" class="block text-sm font-medium mb-1">
                                            {{ __('diving.technical_director') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <p class="text-sm text-gray-500 mb-2">
                                            {{ __('diving.select_certified_professional') }}
                                        </p>
                                        <div class="flex">
                                            <input id="technical_director_id" 
                                                   name="technical_director_id" 
                                                   type="text" 
                                                   class="form-input w-full {{ $errors->has('technical_director_id') ? 'border-rose-300' : '' }}" 
                                                   value="{{ old('technical_director_id') }}"
                                                   placeholder="{{ __('diving.enter_member_code') }}"
                                                   required>
                                            <x-entity-instructor-selector 
                                                :entity-id="$entity->id" 
                                                :input-id="'technical_director_id'" />
                                        </div>
                                        <div x-show="selectedName" x-text="selectedName" class="text-sm text-green-600 mt-1"
                                             x-on:individual-selected.window="
                                                if ($event.detail.inputId === 'technical_director_id') {
                                                    selectedName = $event.detail.name;
                                                }
                                             ">
                                        </div>
                                        @if ($errors->has('technical_director_id'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('technical_director_id') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Certification System -->
                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/2">
                                        <label for="certification_system" class="block text-sm font-medium mb-1">
                                            {{ __('diving.certification_system') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <p class="text-sm text-gray-500 mb-2">
                                            {{ __('diving.certification_system_of_director') }}
                                        </p>
                                        <select id="certification_system" name="certification_system" required
                                                class="form-select w-full {{ $errors->has('certification_system') ? 'border-rose-300' : '' }}">
                                            <option value="">{{ __('diving.select_certification_system') }}</option>
                                            <option value="SSI" {{ old('certification_system') == 'SSI' ? 'selected' : '' }}>SSI</option>
                                            <option value="PADI" {{ old('certification_system') == 'PADI' ? 'selected' : '' }}>PADI</option>
                                            <option value="SDI/TDI" {{ old('certification_system') == 'SDI/TDI' ? 'selected' : '' }}>SDI/TDI</option>
                                            <option value="DDI" {{ old('certification_system') == 'DDI' ? 'selected' : '' }}>DDI</option>
                                            <option value="GUE" {{ old('certification_system') == 'GUE' ? 'selected' : '' }}>GUE</option>
                                            <option value="CMAS" {{ old('certification_system') == 'CMAS' ? 'selected' : '' }}>CMAS</option>
                                            <option value="Other" {{ old('certification_system') == 'Other' ? 'selected' : '' }}>{{ __('diving.other') }}</option>
                                        </select>
                                        @if ($errors->has('certification_system'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('certification_system') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Additional Notes -->
                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="w-full">
                                        <label for="notes" class="block text-sm font-medium mb-1">
                                            {{ __('diving.additional_notes') }}
                                        </label>
                                        <textarea id="notes" name="notes" rows="3"
                                                  class="form-textarea w-full {{ $errors->has('notes') ? 'border-rose-300' : '' }}"
                                                  placeholder="{{ __('diving.additional_info_placeholder') }}">{{ old('notes') }}</textarea>
                                        @if ($errors->has('notes'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('notes') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </section>
                        </div>

                        <x-forms.card-form-submit 
                            :back-route="'entity.diving_licenses.index'" 
                            :button-text="__('diving.submit_request')" />
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Update certification system based on selected technical director if needed
        document.addEventListener('livewire:load', function () {
            Livewire.on('individual-selected', function (data) {
                // You can implement logic here to auto-select certification system
                // based on the instructor's certifications
                console.log('Individual selected:', data);
            });
        });
    </script>
    @endpush
</x-layout>