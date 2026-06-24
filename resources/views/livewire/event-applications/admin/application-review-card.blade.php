<div class="card">
    <h3 class="grow font-semibold text-slate-800 truncate mb-4">{{ __('event_applications.sections.admin_section') }}</h3>

    <div class="space-y-2">
        @php
            $state = $application->state;
            $availableTransitions = $state->availableTransitions();
        @endphp

        @if(in_array('validate', $availableTransitions))
            <button type="button"
                    wire:click="openModal('validate')"
                    class="btn btn-primary w-full">
                {{ __('Validate Application') }}
            </button>
        @endif

        @if(in_array('approve', $availableTransitions))
            <button type="button"
                    wire:click="openModal('approve')"
                    class="btn btn-success w-full">
                {{ __('event_applications.actions.approve') }}
            </button>
        @endif

        @if(in_array('returnForCorrection', $availableTransitions))
            <button type="button"
                    wire:click="openModal('return')"
                    class="btn btn-warning w-full">
                {{ __('event_applications.actions.return_for_correction') }}
            </button>
        @endif

        @if(in_array('reject', $availableTransitions))
            <button type="button"
                    wire:click="openModal('reject')"
                    class="btn btn-danger w-full">
                {{ __('event_applications.actions.reject') }}
            </button>
        @endif

        @if(in_array('publish', $availableTransitions))
            <button type="button"
                    wire:click="openModal('publish')"
                    class="btn btn-info w-full">
                {{ __('event_applications.actions.publish') }}
            </button>
        @endif
    </div>

    @if($showModal)
        <div class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 transition-opacity"
             wire:click="closeModal"
             x-data
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">

            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full"
                         wire:click.stop
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">

                        <div class="px-6 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800">{{ $modalTitle }}</h3>
                        </div>

                        <form wire:submit="updateState">
                            <div class="px-6 py-4">
                                <label class="block text-sm font-medium mb-2" for="notes">
                                    {{ __('Notes') }}
                                    @if($action === 'reject' || $action === 'return')
                                        <span class="text-rose-500">*</span>
                                    @endif
                                </label>
                                <textarea id="notes"
                                          wire:model="notes"
                                          rows="4"
                                          class="form-textarea w-full @error('notes') border-rose-300 @enderror"
                                          placeholder="{{ $action === 'reject' ? __('event_applications.placeholders.rejection_reason') : ($action === 'return' ? __('event_applications.placeholders.correction_notes') : __('event_applications.placeholders.approval_notes')) }}"></textarea>
                                @error('notes')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="px-6 py-4 bg-slate-50 rounded-b-lg flex justify-end space-x-2">
                                <button type="button"
                                        wire:click="closeModal"
                                        class="btn btn-secondary">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                        class="btn {{ $action === 'approve' ? 'btn-success' : ($action === 'validate' || $action === 'publish' ? 'btn-primary' : ($action === 'return' ? 'btn-warning' : 'btn-danger')) }}"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove>{{ __('common.confirm') }}</span>
                                    <span wire:loading>{{ __('Processing...') }}</span>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
