<div>
    @if($uploadSuccess)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ __('diving.certification_uploaded_success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('diving.upload_diving_certification') }}</h3>
            <button
                wire:click="toggleForm"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
            >
                @if($showForm)
                    {{ __('diving.cancel') }}
                @else
                    {{ __('diving.add_certification') }}
                @endif
            </button>
        </div>

        @if($showForm)
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Certification Name -->
                    <div>
                        <label for="certification_name" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.certification_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="certification_name"
                            id="certification_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="{{ __('diving.certification_name_placeholder') }}"
                        >
                        @error('certification_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Certification System -->
                    <div>
                        <label for="certification_system" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.certification_system') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            wire:model="certification_system"
                            id="certification_system"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">{{ __('diving.select_a_system') }}</option>
                            @foreach($certificationSystems as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('certification_system')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Certification Level -->
                    <div>
                        <label for="certification_level" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.certification_level') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="certification_level"
                            id="certification_level"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="{{ __('diving.certification_level_placeholder') }}"
                        >
                        @error('certification_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Certification Number -->
                    <div>
                        <label for="certification_number" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.certification_number') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="certification_number"
                            id="certification_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="{{ __('diving.certification_number_placeholder') }}"
                        >
                        @error('certification_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- National Equivalency -->
                    <div>
                        <label for="national_equivalency" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.national_equivalency') }}
                        </label>
                        <input
                            type="text"
                            wire:model="national_equivalency"
                            id="national_equivalency"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="{{ __('diving.national_equivalency_placeholder') }}"
                        >
                        @error('national_equivalency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Issue Date -->
                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.issue_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            wire:model="issue_date"
                            id="issue_date"
                            max="{{ date('Y-m-d') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('issue_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expiration Date -->
                    <div>
                        <label for="expiration_date" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.expiration_date') }}
                        </label>
                        <input
                            type="date"
                            wire:model="expiration_date"
                            id="expiration_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('expiration_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Certificate Document -->
                    <div class="md:col-span-2">
                        <label for="certificate_document" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.certificate_document') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="certificate_document" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>{{ __('diving.upload_a_file') }}</span>
                                        <input
                                            id="certificate_document"
                                            wire:model="certificate_document"
                                            type="file"
                                            class="sr-only"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                        >
                                    </label>
                                    <p class="pl-1">{{ __('diving.or_drag_drop') }}</p>
                                </div>
                                <p class="text-xs text-gray-500">{{ __('diving.file_types_size') }}</p>
                                @if($certificate_document)
                                    <p class="text-sm text-green-600 mt-2">
                                        {{ __('diving.file_selected') }} {{ $certificate_document->getClientOriginalName() }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        @error('certificate_document')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        wire:click="toggleForm"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        {{ __('diving.cancel') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        <span wire:loading.remove>{{ __('diving.upload_certification') }}</span>
                        <span wire:loading>{{ __('diving.uploading') }}</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>