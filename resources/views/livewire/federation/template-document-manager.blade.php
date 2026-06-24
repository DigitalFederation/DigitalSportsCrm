<div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="border-b border-gray-200 bg-gray-50/50">
            <div class="px-6 py-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="font-semibold text-gray-700">{{ __('event_applications.sections.required_documents') }}</span>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-4 sm:p-6">

            {{-- Success Message --}}
            @if($successMessage)
                <div class="mb-4 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-3 rounded-lg text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $successMessage }}</span>
                </div>
            @endif

            {{-- Error Message --}}
            @if($errorMessage)
                <div class="mb-4 flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-lg text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
            @endif

            {{-- Upload Section (always visible) --}}
            <div class="mb-6 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4">
                <div class="flex flex-col md:flex-row md:items-end gap-4"
                     x-data="{
                         uploading: false,
                         progress: 0,
                         fileError: false,
                         errorMessage: ''
                     }"
                     x-on:livewire-upload-start="uploading = true; fileError = false"
                     x-on:livewire-upload-finish="uploading = false"
                     x-on:livewire-upload-error="uploading = false; fileError = true; errorMessage = '{{ __('event_applications.document_uploaded_error') }}'"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">

                    {{-- File Input --}}
                    <div class="w-full md:flex-1">
                        <label for="newAttachment" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('event_applications.labels.file') }} <span class="text-rose-500">*</span>
                        </label>
                        <input id="newAttachment"
                               type="file"
                               wire:model="newAttachment"
                               class="border border-slate-200 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold cursor-pointer"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                        {{-- Upload Progress Bar --}}
                        <div x-show="uploading" x-cloak class="mt-2">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 transition-all duration-300" :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">{{ __('common.uploading') }}: <span x-text="progress"></span>%</p>
                        </div>

                        {{-- Alpine Error --}}
                        <div x-show="fileError" x-cloak class="mt-2">
                            <p class="text-xs text-red-500" x-text="errorMessage"></p>
                        </div>

                        @error('newAttachment')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror

                        <p class="text-xs text-gray-500 mt-1.5">
                            {{ __('event_applications.help.file_types') }} (PDF, DOC, DOCX, JPG, JPEG, PNG - {{ __('event_applications.help.max_file_size') }})
                        </p>
                    </div>

                    {{-- Optional File Name --}}
                    <div class="w-full md:flex-1">
                        <label for="newAttachmentName" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('event_applications.labels.file_name_optional') }}
                        </label>
                        <input type="text"
                               id="newAttachmentName"
                               wire:model="newAttachmentName"
                               class="form-input w-full">
                        @error('newAttachmentName')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Upload Button --}}
                    <div>
                        <button type="button"
                                wire:click="saveAttachment"
                                wire:loading.attr="disabled"
                                wire:target="saveAttachment,newAttachment"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 border border-primary-600 rounded-lg font-medium text-sm text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                            <span wire:loading.remove wire:target="saveAttachment">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </span>
                            <svg wire:loading wire:target="saveAttachment" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveAttachment">{{ __('common.upload') }}</span>
                            <span wire:loading wire:target="saveAttachment">{{ __('common.uploading') }}...</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Documents Table --}}
            @if($template->documents->isEmpty())
                <div class="text-center py-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">{{ __('event_applications.messages.no_documents') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('event_applications.help.upload_documents_for_entities') }}</p>
                </div>
            @else
                <x-dynamic-table :headers="[__('event_applications.labels.date'), __('common.name'), __('event_applications.table.type'), __('common.size'), '']">
                    @foreach($template->documents as $document)
                        <tr wire:key="doc-{{ $document->id }}" class="hover:bg-gray-100">
                            <td class="pl-5 text-sm">{{ $document->created_at->format('d/m/Y') }}</td>
                            <td class="pl-5 text-sm">{{ $document->file_name }}</td>
                            <td class="py-1 text-sm">{{ getSimplifiedFileType($document->mime_type) }}</td>
                            <td class="py-1 text-sm">{{ formatBytes($document->file_size) }}</td>
                            <td class="py-1 flex items-center justify-end gap-x-2">
                                <a href="{{ Storage::disk('public')->url($document->file_path) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline text-blue-600"
                                   title="{{ __('event_applications.actions.download_document') }}">
                                    <x-svg.box-arrow-down class="w-4 h-4" />
                                </a>
                                <button type="button"
                                        wire:click="confirmDelete({{ $document->id }})"
                                        class="btn btn-sm btn-outline text-red-500"
                                        title="{{ __('event_applications.actions.delete_document') }}">
                                    <x-svg.trash class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-livewire-confirmation-modal
        :isOpen="$confirmingDeletion"
        :title="__('common.warning')"
        :message="__('event_applications.confirmations.delete_document')"
        confirmMethod="delete"
        :confirmText="__('common.delete')"
        cancelMethod="$set('confirmingDeletion', false)"
        :cancelText="__('common.no')"
    />
</div>
