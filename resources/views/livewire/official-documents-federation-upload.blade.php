<div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-lg mb-4">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ __('official_documents.upload_title') }}</h2>
        <p class="text-gray-600">{{ __('official_documents.upload_description') }}</p>
    </div>

    <!-- Information Box -->
    <div class="bg-blue-50 p-4 rounded-lg mb-8 flex items-start">
        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <p class="text-sm text-gray-700">
                <strong>{{ __('official_documents.upload_info_important') }}</strong> {{ __('official_documents.upload_info_text') }}
            </p>
        </div>
    </div>

    <!-- Error Message -->
    @if(!empty($message))
        <div class="bg-red-50 p-4 rounded-lg mb-8 flex items-start">
            <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-red-700">{{ $message }}</div>
        </div>
    @endif

    <!-- Upload Form -->
    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Individual Selection -->
        @if($isForIndividual)
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('official_documents.select_members') }}</label>
            <div class="relative" wire:ignore>
                <select
                    id="individuals-select"
                    name="selectedIndividuals[]"
                    multiple
                    class="w-full form-select rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    @foreach($individuals as $individual)
                        <option value="{{ $individual['id'] }}" @if(in_array($individual['id'], $selectedIndividuals)) selected @endif>
                            {{ $individual['display_name'] }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            @error('selectedIndividuals')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <!-- Document Type -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('official_documents.document_type_label') }}</label>
            <div class="relative">
                <select
                    wire:model="type"
                    required
                    class="w-full form-select rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="" selected disabled>{{ __('official_documents.select_document_type') }}</option>
                    @foreach($types as $value => $name)
                        <option value="{{ $value }}">{{ $name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Date Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Issue Date -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">{{ __('official_documents.issue_date') }}</label>
                <input
                    type="date"
                    wire:model="issue_date"
                    class="w-full form-input rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                @error('issue_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expiry Date -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">{{ __('official_documents.expiry_date_label') }}</label>
                <input
                    type="date"
                    wire:model="expiry_date"
                    class="w-full form-input rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                @error('expiry_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- File Upload -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('official_documents.upload_files') }}</label>
            <div
                x-data="{ isUploading: false, progress: 0 }"
                x-on:livewire-upload-start="isUploading = true"
                x-on:livewire-upload-finish="isUploading = false"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                class="relative"
            >
                <!-- Upload Progress -->
                <div x-show="isUploading" class="absolute inset-0 bg-white/90 rounded-lg flex items-center justify-center">
                    <div class="w-64">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500" :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="mt-2 text-sm text-center text-gray-600">{{ __('official_documents.uploading') }} <span x-text="progress"></span>%</p>
                    </div>
                </div>

                <!-- File Input -->
                <div class="flex items-center justify-center w-full">
                    <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500">
                                <span class="font-semibold">{{ __('official_documents.click_to_upload') }}</span> {{ __('official_documents.or_drag_and_drop') }}
                            </p>
                            <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG (MAX. 50MB)</p>
                        </div>
                        <input
                            id="dropzone-file"
                            type="file"
                            class="hidden"
                            wire:model.live="attachments"
                            multiple
                        />
                    </label>
                </div>
            </div>
            @error('attachments')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-6">
            <button
                type="submit"
                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                {{ __('official_documents.upload_document') }}
            </button>
        </div>
    </form>
</div>

@push('footer-scripts')
<script>
    document.addEventListener('livewire:init', () => {
        let choices;

        // Initialize Choices.js
        function initChoices() {
            const selectElement = document.getElementById('individuals-select');
            if (selectElement) {
                choices = new Choices(selectElement, {
                    removeItemButton: true,
                    shouldSort: false,
                    maxItemCount: -1,
                    searchPlaceholderValue: '{{ __('official_documents.search_members') }}',
                    placeholderValue: '{{ __('official_documents.select_members_placeholder') }}',
                    noResultsText: '{{ __('official_documents.no_members_found') }}',
                    noChoicesText: '{{ __('official_documents.no_more_members') }}',
                    itemSelectText: '{{ __('official_documents.click_to_select') }}',
                });

                selectElement.addEventListener('change', (event) => {
                    const selectedValues = Array.from(event.target.selectedOptions)
                        .map(option => option.value);
                    @this.set('selectedIndividuals', selectedValues);
                });
            }
        }

        // Initialize on first load
        initChoices();

        // Reinitialize after Livewire updates
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(() => {
                if (component.id === @this.__instance.id) {
                    if (choices) {
                        choices.destroy();
                    }
                    initChoices();
                }
            });
        });
    });
</script>
@endpush
