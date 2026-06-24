<div class="p-6">
    <!-- Progress Steps -->
    <div class="mb-12">
        <nav aria-label="Progress">
            <ol class="relative flex items-center justify-between">
                @php
                    $steps = ['upload' => __('import.step_upload_file'), 'mapping' => __('import.step_map_fields'), 'validation' => __('import.step_validate_data'), 'import' => __('import.step_import')];
                    $stepKeys = array_keys($steps);
                    $currentIndex = array_search($currentStep, $stepKeys);
                @endphp

                @foreach($steps as $step => $label)
                    @php
                        $stepIndex = array_search($step, $stepKeys);
                        $isCompleted = $this->isStepCompleted($step);
                        $isCurrent = $currentStep === $step;
                        $isPast = $currentIndex !== false && $stepIndex < $currentIndex;
                    @endphp

                    <li class="relative flex-1">
                        <!-- Line connector -->
                        @if(!$loop->last)
                            <div class="absolute top-4 left-1/2 w-full h-0.5 -ml-px">
                                <div class="h-full {{ $isCompleted || $isPast ? 'bg-blue-600' : 'bg-slate-200' }}"></div>
                            </div>
                        @endif

                        <!-- Step circle and label -->
                        <div class="relative flex flex-col items-center">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full z-10
                                        {{ $isCurrent ? 'bg-blue-600 ring-4 ring-blue-100' :
                                           ($isCompleted ? 'bg-blue-600' : 'bg-white border-2 border-slate-300') }}">
                                @if($isCompleted)
                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <span class="text-xs font-semibold {{ $isCurrent ? 'text-white' : 'text-slate-500' }}">
                                        {{ $loop->iteration }}
                                    </span>
                                @endif
                            </div>
                            <span class="mt-2 text-xs font-medium text-center whitespace-nowrap
                                         {{ $isCurrent || $isCompleted ? 'text-slate-800 font-semibold' : 'text-slate-500' }}">
                                {{ $label }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    <!-- Step Content -->
    <div class="min-h-96">
        @if($currentStep === 'upload')
            <!-- Step 1: File Upload -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-800 mb-2">{{ __('import.upload_your_file') }}</h3>
                <p class="text-sm text-slate-600 mb-6">{{ __('import.entity_choose_file_description') }}</p>

                <div class="max-w-lg mx-auto">
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>{{ __('import.upload_a_file') }}</span>
                                    <input wire:model="importFile" id="file-upload" name="file-upload" type="file" accept=".csv,.xls,.xlsx" class="sr-only">
                                </label>
                                <p class="pl-1">{{ __('import.or_drag_drop') }}</p>
                            </div>
                            <p class="text-xs text-slate-500">{{ __('import.file_types_size') }}</p>
                        </div>
                    </div>

                    @if($fileName)
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-2 text-sm text-green-700">{{ $fileName }}</span>
                            </div>
                        </div>
                    @endif

                    @if(isset($errors['importFile']))
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm text-red-700">{{ $errors['importFile'] }}</p>
                        </div>
                    @endif

                    <div wire:loading wire:target="importFile" class="mt-4">
                        <div class="animate-pulse flex space-x-4">
                            <div class="rounded-full bg-blue-200 h-3 w-3"></div>
                            <div class="flex-1 space-y-2 py-1">
                                <div class="h-2 bg-blue-200 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($currentStep === 'mapping')
            <!-- Step 2: Field Mapping -->
            <div>
                <div class="text-center mb-6">
                    <h3 class="text-lg font-medium text-slate-800 mb-2">{{ __('import.map_your_fields') }}</h3>
                    <p class="text-sm text-slate-600">{{ __('import.connect_columns_description') }}</p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ count($headers) }}</div>
                            <div class="text-slate-600">{{ __('import.columns_found') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $fileAnalysis['row_count'] ?? 0 }}</div>
                            <div class="text-slate-600">{{ __('import.total_rows') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ count(array_filter($supportedFields, fn($f) => $f['required'])) }}</div>
                            <div class="text-slate-600">{{ __('import.required_fields') }}</div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('import.your_column') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('import.maps_to') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('import.sample_data') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($headers as $index => $header)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $header }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select wire:model="fieldMapping.{{ $header }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="">{{ __('import.skip_column') }}</option>
                                            @foreach($supportedFields as $fieldKey => $fieldConfig)
                                                <option value="{{ $fieldKey }}">
                                                    {{ $fieldConfig['label'] }}
                                                    @if($fieldConfig['required']) * @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        @if(isset($sampleRows[0][$index]))
                                            <div class="max-w-xs truncate">{{ $sampleRows[0][$index] }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(isset($errors['fieldMapping']))
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-700">{{ $errors['fieldMapping'] }}</p>
                    </div>
                @endif
            </div>

        @elseif($currentStep === 'validation')
            <!-- Step 3: Validation Results -->
            <div>
                <div class="text-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('import.validation_results') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('import.review_before_importing') }}</p>
                    @if(isset($validationResults['total_rows']))
                        <p class="text-xs text-gray-500 mt-2">
                            Validated all {{ $validationResults['total_rows'] }} rows in {{ number_format($validationResults['validation_time'] ?? 0, 2) }}s
                        </p>
                    @endif
                </div>

                <!-- Validation Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-8 w-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ __('import.valid_records') }}</p>
                                <p class="text-2xl font-bold text-green-900">{{ $this->getValidRowsCount() }}<span class="text-sm font-normal text-green-700">/{{ $this->getTotalRowsCount() }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-8 w-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-800">{{ __('import.warnings') }}</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ $this->getWarningRowsCount() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-8 w-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ __('import.errors') }}</p>
                                <p class="text-2xl font-bold text-red-900">{{ $this->getInvalidRowsCount() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Valid Records -->
                @if($this->getValidRowsCount() > 0 && isset($validationResults['sample_valid_records']))
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-slate-800 mb-2">{{ __('import.sample_valid_records') }}</h4>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            @foreach($validationResults['sample_valid_records'] as $record)
                                <div class="text-sm text-green-700 mb-1">
                                    Row {{ $record['row_number'] }}: {{ $record['data']['name'] ?? '' }} ({{ $record['data']['email'] ?? 'no email' }})
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Sample Error Records -->
                @if($this->getInvalidRowsCount() > 0 && isset($validationResults['sample_error_records']))
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-red-800 mb-2">Sample Error Records</h4>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            @foreach($validationResults['sample_error_records'] as $record)
                                <div class="mb-3">
                                    <div class="text-sm font-medium text-red-800">Row {{ $record['row_number'] }}:</div>
                                    <ul class="text-sm text-red-700 ml-4 list-disc">
                                        @foreach($record['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                            @if($this->getInvalidRowsCount() > count($validationResults['sample_error_records']))
                                <div class="text-xs text-red-600 mt-2">
                                    ... and {{ $this->getInvalidRowsCount() - count($validationResults['sample_error_records']) }} more errors
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Advanced Options -->
                <div class="border-t pt-6">
                    <button wire:click="toggleAdvancedOptions" type="button" class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                        <span>{{ __('import.advanced_options') }}</span>
                        <svg class="ml-1 h-4 w-4 transform {{ $showAdvancedOptions ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    @if($showAdvancedOptions)
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ __('import.federations') }}</label>
                                <p class="text-xs text-slate-500 mb-2">{{ __('import.entity_federations_help') }}</p>
                                <div class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3">
                                    @foreach($this->federations as $federation)
                                        <label class="flex items-center space-x-2 hover:bg-slate-50 p-1 rounded">
                                            <input type="checkbox"
                                                   wire:model="selectedFederations"
                                                   value="{{ $federation->id }}"
                                                   @if($federation->is_default_federation || $federation->isMainFederation()) checked @endif
                                                   class="form-checkbox text-blue-600">
                                            <span class="text-sm @if($federation->is_default_federation || $federation->isMainFederation()) font-semibold @endif">
                                                {{ $federation->name }}
                                                @if($federation->is_default_federation || $federation->isMainFederation())
                                                    <span class="text-xs text-slate-500">({{ __('import.main_federation') }})</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">{{ __('import.duplicate_strategy') }}</label>
                                <select wire:model="duplicateStrategy" class="form-select w-full">
                                    @foreach($duplicateStrategies as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        @elseif($currentStep === 'import')
            <!-- Step 4: Import Progress -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-blue-600 mb-4">
                    @if($importInProgress)
                        <svg class="animate-spin w-full h-full" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    @else
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>

                @if($importInProgress)
                    <h3 class="text-lg font-medium text-slate-800 mb-2">{{ __('import.import_in_progress') }}</h3>
                    <p class="text-sm text-slate-600 mb-6">{{ __('import.please_wait_processing') }}</p>

                    <div class="max-w-md mx-auto">
                        <div class="bg-slate-200 rounded-full h-2 mb-4">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                        </div>
                        <p class="text-sm text-slate-600">{{ __('import.percent_complete', ['percentage' => $progressPercentage]) }}</p>

                        @if(!empty($importResults))
                            <div class="mt-6 grid grid-cols-3 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-xl font-semibold text-slate-700">{{ $importResults['processed_rows'] ?? 0 }}/{{ $importResults['total_rows'] ?? 0 }}</div>
                                    <div class="text-slate-600">{{ __('import.rows_processed') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-semibold text-green-600">{{ $importResults['success_count'] ?? 0 }}</div>
                                    <div class="text-slate-600">{{ __('import.successful') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-semibold text-red-600">{{ $importResults['error_count'] ?? 0 }}</div>
                                    <div class="text-slate-600">{{ __('import.errors') }}</div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6">
                            <button wire:click="cancelImport" type="button"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('import.cancel_import') }}
                            </button>
                        </div>
                    </div>
                @else
                    <h3 class="text-lg font-medium text-slate-800 mb-2">{{ __('import.import_starting') }}</h3>
                    <p class="text-sm text-slate-600 mb-6">{{ __('import.preparing_import') }}</p>
                @endif
            </div>

        @elseif($currentStep === 'completed')
            <!-- Step 5: Completion -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 text-green-600 mb-4">
                    <svg fill="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-800 mb-2">{{ __('import.import_completed') }}</h3>

                @if(!empty($importResults))
                    <div class="max-w-lg mx-auto mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $importResults['success_count'] ?? 0 }}</div>
                                    <div class="text-slate-600">{{ __('import.successfully_imported') }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-red-600">{{ $importResults['error_count'] ?? 0 }}</div>
                                    <div class="text-slate-600">{{ __('import.failed') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-x-4">
                    <a href="{{ route('admin.entity.index') }}"
                       class="btn btn-primary">
                        {{ __('import.view_entities') }}
                    </a>
                    <button wire:click="resetWizard" type="button"
                            class="btn btn-secondary">
                        {{ __('import.import_more') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8 pt-6 border-t border-gray-200">
        <div>
            @if($currentStep !== 'upload' && $currentStep !== 'completed' && $currentStep !== 'import')
                <button wire:click="previousStep" type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Previous
                </button>
            @endif
        </div>

        <div>
            @if($currentStep === 'upload')
                <button wire:click="nextStep" type="button"
                        @if(!$fileName) disabled @endif
                        class="btn @if($fileName) btn-primary @else btn-secondary opacity-50 cursor-not-allowed @endif">
                    {{ __('import.analyze_file') }}
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            @elseif($currentStep === 'mapping')
                <button wire:click="nextStep" type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('import.validate_data') }}
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            @elseif($currentStep === 'validation')
                @if($this->getInvalidRowsCount() === 0)
                    <button wire:click="nextStep" type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        {{ __('import.start_import') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </button>
                @else
                    <button wire:click="nextStep" type="button"
                            wire:confirm="{{ __('import.confirm_import_with_errors', ['count' => $this->getInvalidRowsCount()]) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        {{ __('import.import_valid_only') }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </button>
                @endif
            @endif
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading.delay wire:target="nextStep,uploadFile,validateMapping,executeImport"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-sm text-gray-600">Processing...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let pollInterval = null;

        // Listen for start polling event
        window.addEventListener('start-import-polling', function(event) {
            const importId = event.detail.importId;

            // Clear any existing interval
            if (pollInterval) {
                clearInterval(pollInterval);
            }

            // Poll every 2 seconds
            pollInterval = setInterval(function() {
                @this.updateImportProgress();
            }, 2000);
        });

        // Listen for stop polling event
        window.addEventListener('stop-import-polling', function() {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (pollInterval) {
                clearInterval(pollInterval);
            }
        });
    });
</script>
