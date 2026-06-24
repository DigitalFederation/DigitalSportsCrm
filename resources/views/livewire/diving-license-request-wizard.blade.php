<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex items-center justify-center">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full 
                        {{ $i < $currentStep ? 'bg-primary text-white' : ($i === $currentStep ? 'bg-primary text-white' : 'bg-gray-200 text-gray-400') }}">
                        @if ($i < $currentStep)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @else
                            {{ $i }}
                        @endif
                    </div>
                    @if ($i < $totalSteps)
                        <div class="w-16 h-1 {{ $i < $currentStep ? 'bg-primary' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endfor
        </div>
        <div class="text-center mt-2">
            <h3 class="text-lg font-medium text-gray-900">{{ $this->getStepTitle() }}</h3>
        </div>
    </div>

    <!-- Form Content -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if ($currentStep === 1)
                <!-- Step 1: License Selection -->
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('diving.select_license_type_description') }}
                    </p>
                    
                    <div>
                        <label for="licenseId" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.license_type') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="licenseId" id="licenseId"
                                class="form-select w-full @error('licenseId') border-rose-300 @enderror">
                            <option value="">{{ __('diving.choose_license_type') }}</option>
                            @foreach($availableLicenses as $availableLicense)
                                <option value="{{ $availableLicense->id }}">
                                    {{ $availableLicense->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('licenseId')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($license)
                        <div class="mt-6 bg-blue-50 p-4 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        {{ $license->name }}
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>{{ $license->description }}</p>
                                    </div>
                                    @if($certificationRequirementText && !empty($requiredCertificationLevels))
                                        <div class="mt-3 pt-3 border-t border-blue-200">
                                            <h4 class="text-sm font-medium text-blue-800">
                                                {{ __('diving.certification_requirements') }}
                                            </h4>
                                            <p class="text-sm text-blue-700 mt-1">
                                                {{ $certificationRequirementText }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            @elseif ($currentStep === 2)
                <!-- Step 2: Certification Systems Selection -->
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('diving.select_certification_systems_description') }}
                    </p>

                    <!-- Certification Systems Selection -->
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('diving.select_certification_systems') }} <span class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-gray-600 mb-3">
                            {{ __('diving.select_systems_for_license') }}
                        </p>
                        <div class="space-y-2">
                            @foreach($certificationSystems as $system)
                                <label class="inline-flex items-center mr-4">
                                    <input type="checkbox" 
                                           wire:model.live="selectedCertificationSystems" 
                                           value="{{ $system }}"
                                           class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">{{ $system }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedCertificationSystems')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                        @if(!empty($selectedCertificationSystems))
                            <p class="text-sm text-blue-600 mt-2">
                                {{ __('diving.systems_selected', ['count' => count($selectedCertificationSystems)]) }}: {{ implode(', ', $selectedCertificationSystems) }}
                            </p>
                            <p class="text-sm text-green-600 mt-1">
                                {{ __('diving.professionals_available', ['count' => count($filteredDirectors)]) }}
                            </p>
                            @if($certificationRequirementText && !empty($requiredCertificationLevels))
                                <div class="mt-2 text-sm text-amber-600">
                                    <strong>{{ __('diving.note') }}:</strong> {{ $certificationRequirementText }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

            @elseif ($currentStep === 3)
                <!-- Step 3: Technical Directors -->
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('diving.select_technical_directors_description') }}
                    </p>
                    
                    <!-- Display any general errors -->
                    @if($errors->any())
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        {{ __('common.validation_errors') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc space-y-1 pl-5">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(empty($selectedCertificationSystems))
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <p class="text-sm text-yellow-800">
                                {{ __('diving.please_select_certification_systems_first') }}
                            </p>
                        </div>
                    @elseif(count($filteredDirectors) === 0)
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <p class="text-sm text-red-800">
                                {{ __('diving.no_professionals_found_for_systems') }}
                            </p>
                        </div>
                    @else
                        <!-- Search box and controls -->
                        <div class="mb-4 space-y-3">
                            <div>
                                <label for="director-search" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('common.search') }}
                                </label>
                                <input type="text" 
                                       id="director-search"
                                       wire:model.live.debounce.300ms="directorSearch"
                                       placeholder="{{ __('common.search_by_name_or_number') }}"
                                       class="form-input w-full">
                            </div>
                            
                            @if(count($filteredDirectors) > 0)
                                <div class="flex items-center space-x-4">
                                    <button type="button" 
                                            wire:click="$set('selectedDirectorIds', {{ json_encode($filteredDirectors->pluck('id')->toArray()) }})"
                                            class="text-sm text-blue-600 hover:text-blue-500">
                                        {{ __('common.select_all') }}
                                    </button>
                                    <button type="button" 
                                            wire:click="$set('selectedDirectorIds', [])"
                                            class="text-sm text-blue-600 hover:text-blue-500">
                                        {{ __('common.deselect_all') }}
                                    </button>
                                    <span class="text-sm text-gray-500">
                                        {{ __('common.showing') }} {{ count($filteredDirectors) }} {{ __('common.results') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Table of eligible professionals with checkboxes -->
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                            <span class="sr-only">{{ __('common.select') }}</span>
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" colspan="2">
                                            {{ __('diving.technical_directors') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            {{ __('diving.certification_systems') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($filteredDirectors as $director)
                                        <tr wire:key="director-{{ $director->id }}" class="hover:bg-gray-50">
                                            <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                                <input type="checkbox"
                                                       wire:model.live="selectedDirectorIds"
                                                       value="{{ $director->id }}"
                                                       class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900" colspan="2">
                                                <div class="font-medium">
                                                    {{ $director->full_name }}
                                                    @if($director->member_number)
                                                        ({{ __('common.filiation_number') }}: {{ $director->member_number }})
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 text-sm text-gray-500">
                                                @php
                                                    $certs = $this->getDirectorCertificationSystems($director->id);
                                                @endphp
                                                @if(count($certs) > 0)
                                                    @foreach($certs as $cert)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                            {{ $cert }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-gray-400">{{ __('common.none') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(count($selectedDirectorIds) > 0)
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mt-4">
                                <p class="text-sm text-blue-800">
                                    <strong>{{ __('diving.selected_directors') }}:</strong> {{ count($selectedDirectorIds) }}
                                </p>
                            </div>
                        @endif
                    @endif
                    
                    @error('entity_documents')
                        <div class="rounded-md bg-yellow-50 p-4 mt-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        {{ __('diving.missing_required_document') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>{{ $message }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @enderror
                </div>

            @elseif ($currentStep === 4)
                <!-- Step 4: Additional Notes -->
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('diving.add_additional_notes_description') }}
                    </p>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.additional_notes') }}
                        </label>
                        <textarea wire:model="notes" id="notes" rows="6"
                                  class="form-textarea w-full"
                                  placeholder="{{ __('diving.additional_info_placeholder') }}"></textarea>
                        @error('notes')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            @elseif ($currentStep === 5)
                <!-- Step 5: Review and Submit -->
                <div class="space-y-6">
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('diving.review_request_before_submit') }}
                    </p>

                    <!-- License Summary -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('diving.license_information') }}</h4>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">{{ __('diving.license_type') }}:</span> {{ $license->name ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Certification Systems Summary -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('diving.certification_systems') }}</h4>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="text-sm text-gray-700">
                                @php
                                    $actualSystems = $this->getActualSelectedCertificationSystems();
                                @endphp
                                @if(count($actualSystems) > 0)
                                    {{ implode(', ', $actualSystems) }}
                                @else
                                    <span class="text-gray-500 italic">{{ __('diving.no_systems_with_directors') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Technical Directors Summary -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('diving.technical_directors') }}</h4>
                        <div class="bg-gray-50 p-4 rounded-md space-y-2">
                            @foreach($selectedDirectorIds as $directorId)
                                @php
                                    $director = \Domain\Individuals\Models\Individual::find($directorId);
                                @endphp
                                @if($director)
                                    <div class="text-sm text-gray-700">
                                        <span class="font-medium">{{ $director->full_name }}</span>
                                        @if($director->member_number)
                                            <span class="text-gray-500">({{ __('common.filiation_number') }}: {{ $director->member_number }})</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Notes Summary -->
                    @if($notes)
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('diving.additional_notes') }}</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-700">{{ $notes }}</p>
                            </div>
                        </div>
                    @endif

                    @error('submit')
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800">{{ $message }}</p>
                                </div>
                            </div>
                        </div>
                    @enderror
                </div>
            @endif
        </div>

        <!-- Navigation -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between">
            <div>
                @if($currentStep > 1)
                    <button type="button" wire:click="previousStep"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ __('diving.previous') }}
                    </button>
                @else
                    <a href="{{ route('entity.diving_licenses.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('diving.cancel') }}
                    </a>
                @endif
            </div>

            <div>
                @if($currentStep < $totalSteps)
                    <button type="button" wire:click="nextStep"
                            class="inline-flex items-center btn btn-primary">
                        {{ __('diving.next') }}
                        <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @else
                    <button type="button" wire:click="submitRequest" wire:loading.attr="disabled"
                            class="inline-flex items-center btn btn-primary">
                        <span wire:loading.remove>
                            {{ __('diving.submit_request') }}
                        </span>
                        <span wire:loading>
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('diving.submitting') }}...
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>