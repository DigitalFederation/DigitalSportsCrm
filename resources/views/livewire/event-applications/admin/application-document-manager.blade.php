<div class="card">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('event_applications.sections.documents') }}</h2>
        <button type="button"
                wire:click="toggleUploadForm"
                class="btn btn-sm btn-primary">
            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
            </svg>
            <span class="ml-2">{{ __('event_applications.actions.upload_document') }}</span>
        </button>
    </div>

    @if($showUploadForm)
        <div class="mb-6 p-4 bg-slate-50 rounded-lg"
             x-data
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form wire:submit="uploadDocument">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" for="file">
                        {{ __('File') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="file"
                           id="file"
                           wire:model="file"
                           class="form-input w-full @error('file') border-rose-300 @enderror"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    @error('file')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                    <p class="text-xs text-slate-500 mt-1">{{ __('Accepted formats: PDF, DOC, DOCX, JPG, PNG. Max size: 20MB') }}</p>

                    @if($file)
                        <div class="mt-2 text-sm text-slate-600">
                            {{ __('Selected:') }} {{ $file->getClientOriginalName() }}
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" for="document_type">
                        {{ __('Document Type') }}
                    </label>
                    <select id="document_type"
                            wire:model="document_type"
                            class="form-select w-full @error('document_type') border-rose-300 @enderror">
                        <option value="">{{ __('common.select') }}</option>
                        <option value="proposal">{{ __('event_applications.document_types.proposal') }}</option>
                        <option value="budget">{{ __('event_applications.document_types.budget') }}</option>
                        <option value="insurance">{{ __('event_applications.document_types.insurance') }}</option>
                        <option value="venue_agreement">{{ __('event_applications.document_types.venue_agreement') }}</option>
                        <option value="technical_plan">{{ __('event_applications.document_types.technical_plan') }}</option>
                        <option value="marketing_plan">{{ __('event_applications.document_types.marketing_plan') }}</option>
                        <option value="safety_plan">{{ __('event_applications.document_types.safety_plan') }}</option>
                        <option value="other">{{ __('event_applications.document_types.other') }}</option>
                    </select>
                    @error('document_type')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" for="description">
                        {{ __('event_applications.labels.description') }}
                    </label>
                    <textarea id="description"
                              wire:model="description"
                              rows="2"
                              class="form-textarea w-full @error('description') border-rose-300 @enderror"></textarea>
                    @error('description')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button"
                            wire:click="toggleUploadForm"
                            class="btn btn-sm btn-secondary">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit"
                            class="btn btn-sm btn-primary"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="file, uploadDocument">{{ __('event_applications.actions.upload_document') }}</span>
                        <span wire:loading wire:target="file">{{ __('Preparing...') }}</span>
                        <span wire:loading wire:target="uploadDocument">{{ __('Uploading...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-3">
        @forelse($model->documents as $document)
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                <div class="flex items-center space-x-3 flex-1">
                    <svg class="w-6 h-6 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $document->filename }}</p>
                        @if($document->document_type)
                            <p class="text-xs text-slate-500">{{ __('event_applications.document_types.' . $document->document_type) }}</p>
                        @endif
                        @if($document->description)
                            <p class="text-xs text-slate-500 mt-1">{{ $document->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2 ml-3">
                    <a href="{{ route('admin.application-documents.download', $document) }}"
                       class="text-indigo-500 hover:text-indigo-600"
                       title="{{ __('event_applications.actions.download_document') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </a>
                    <button type="button"
                            wire:click="deleteDocument({{ $document->id }})"
                            wire:confirm="{{ __('event_applications.confirmations.delete_document') }}"
                            class="text-rose-500 hover:text-rose-600"
                            title="{{ __('event_applications.actions.delete_document') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-slate-500">
                {{ __('No documents uploaded yet') }}
            </div>
        @endforelse
    </div>
</div>
