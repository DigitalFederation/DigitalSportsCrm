<div>
    @if(!$readonly)
        <!-- Upload Form -->
        <form wire:submit.prevent="uploadDocument" class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- Document Type -->
                <div>
                    <label class="block text-sm font-medium mb-1" for="document_type">
                        {{ __('event_applications.labels.document_type') }} <span class="text-rose-500">*</span>
                    </label>
                    <select id="document_type"
                            wire:model="document_type"
                            class="form-select w-full @error('document_type') border-rose-300 @enderror"
                            required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($documentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('document_type')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>

                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium mb-1" for="file">
                        {{ __('event_applications.actions.upload_document') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="file"
                           id="file"
                           wire:model="file"
                           class="form-input w-full @error('file') border-rose-300 @enderror"
                           required>
                    @error('file')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                    <p class="text-xs text-slate-500 mt-1">
                        {{ __('event_applications.validation.file_mimes') }}
                    </p>
                </div>

            </div>

            <!-- Upload Button -->
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2" wire:loading.remove wire:target="uploadDocument">
                        {{ __('event_applications.actions.upload_document') }}
                    </span>
                    <span class="ml-2" wire:loading wire:target="uploadDocument">
                        {{ __('common.uploading') }}...
                    </span>
                </button>

                @if($file)
                    <span class="ml-3 text-sm text-slate-600" wire:loading.remove wire:target="file">
                        {{ $file->getClientOriginalName() }}
                    </span>
                    <span class="ml-3 text-sm text-slate-600" wire:loading wire:target="file">
                        {{ __('common.uploading') }}...
                    </span>
                @endif
            </div>
        </form>

        <div class="border-t border-slate-200 pt-6 mt-6"></div>
    @endif

    <!-- Documents List -->
    @if($documents->isEmpty())
        <div class="text-center py-6">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-slate-900">
                {{ __('event_applications.messages.no_documents') }}
            </h3>
            @if(!$readonly)
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('event_applications.help.upload_documents_hint') }}
                </p>
            @endif
        </div>
    @else
        <div class="space-y-3">
            @foreach($documents as $document)
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="flex items-center flex-1">
                        <svg class="h-8 w-8 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-900">
                                {{ !empty($document->document_type) && isset($documentTypes[$document->document_type]) ? $documentTypes[$document->document_type] : $document->file_name }}
                            </p>
                            @if(!empty($document->document_type) && isset($documentTypes[$document->document_type]))
                                <p class="text-xs text-slate-500 truncate">{{ $document->file_name }}</p>
                            @endif
                            <p class="text-xs text-slate-400">
                                {{ __('common.uploaded_at') }}: {{ $document->created_at->format('d/m/Y H:i') }}
                                @if($document->file_size)
                                    &middot; {{ Number::fileSize($document->file_size) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('federation.application-documents.download', $document) }}"
                           target="_blank"
                           class="inline-flex items-center px-3 py-1.5 border border-slate-300 shadow-sm text-xs font-medium rounded text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            {{ __('event_applications.actions.download_document') }}
                        </a>

                        <button type="button"
                                wire:click="deleteDocument({{ $document->id }})"
                                onclick="return confirm('{{ __('event_applications.confirmations.delete_document') }}')"
                                class="inline-flex items-center px-3 py-1.5 border border-rose-300 shadow-sm text-xs font-medium rounded text-rose-700 bg-white hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('common.delete') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
