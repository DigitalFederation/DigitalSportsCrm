{{-- Step 9: Required Documents --}}
<div>
    @if ($application)
        <!-- Documents Info Card -->
        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
            <div class="flex">
                <div class="shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        {{ __('event_applications.wizard.documents_info') }}
                    </p>
                </div>
            </div>
        </div>

        <livewire:event-applications.entity.application-document-uploader :application="$application" wire:key="doc-uploader-{{ $application->id }}" />
    @else
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 text-center">
            <x-heroicon-o-exclamation-triangle class="mx-auto h-10 w-10 text-yellow-500 mb-3" />
            <p class="text-sm text-yellow-700">
                {{ __('event_applications.wizard.upload_docs_hint') }}
            </p>
            <button type="button" wire:click="saveDraft" class="btn btn-info mt-4" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveDraft">{{ __('event_applications.actions.save_draft') }}</span>
                <span wire:loading wire:target="saveDraft">{{ __('common.saving') }}...</span>
            </button>
        </div>
    @endif
</div>
