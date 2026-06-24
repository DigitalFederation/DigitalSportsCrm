<div class="w-full">
    @if (!$showForm)
        <!-- Upload Button -->
        <div class="flex justify-end mb-4">
            <button wire:click="toggleForm" 
                    class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                {{ __('main.Upload Document') }}
            </button>
        </div>
    @endif

    @if ($showForm)
        <!-- Upload Form -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('main.Upload Official Document') }}</h3>
                <button wire:click="toggleForm" 
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @if ($uploadSuccess)
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="ml-3 text-sm text-green-700">
                            {{ __('main.Document uploaded successfully') }}
                        </p>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Document Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('main.Document Type') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="type" 
                                id="type"
                                class="form-select w-full @error('type') border-red-300 @enderror">
                            <option value="">{{ __('main.Select document type') }}</option>
                            @foreach($this->availableTypes as $documentType)
                                <option value="{{ $documentType->value }}">
                                    {{ \App\Enums\OfficialDocumentTypeEnum::toString($documentType->value) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('main.File') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="file" 
                               wire:model="attachment" 
                               id="attachment"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="form-input w-full @error('attachment') border-red-300 @enderror">
                        @error('attachment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">{{ __('main.Accepted formats') }}: PDF, JPG, PNG ({{ __('main.Max') }} 10MB)</p>
                    </div>

                    <!-- Issue Date -->
                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('main.Issue Date') }}
                            @if($type === \App\Enums\OfficialDocumentTypeEnum::ADELCertificate->value)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <input type="date" 
                               wire:model="issue_date" 
                               id="issue_date"
                               class="form-input w-full @error('issue_date') border-red-300 @enderror">
                        @error('issue_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('main.Expiry Date') }}
                        </label>
                        <input type="date" 
                               wire:model="expiry_date" 
                               id="expiry_date"
                               class="form-input w-full @error('expiry_date') border-red-300 @enderror">
                        @error('expiry_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @error('federation')
                    <div class="p-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    </div>
                @enderror

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" 
                            wire:click="toggleForm"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        {{ __('main.Cancel') }}
                    </button>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <span wire:loading.remove wire:target="save">{{ __('main.Upload') }}</span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('main.Uploading...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Existing Documents List -->
    @if($individual->officialDocuments->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('main.Official Documents') }}</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($individual->officialDocuments->sortByDesc('created_at') as $document)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ __('main.Uploaded') }} {{ $document->created_at->format('d/m/Y') }}
                                            @if($document->expiry_date)
                                                • {{ __('main.Expires') }} {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <!-- Status Badge -->
                                @if($document->status_class)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                          style="background-color: {{ $document->stateColor() }}20; color: {{ $document->stateColor() }};">
                                        {{ $document->stateName() }}
                                    </span>
                                @endif
                                
                                <!-- View Document -->
                                @if($document->getFirstMediaUrl('media'))
                                    <a href="{{ $document->getFirstMediaUrl('media') }}" 
                                       target="_blank"
                                       class="text-primary hover:text-primary-dark">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>