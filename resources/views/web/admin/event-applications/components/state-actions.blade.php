@php
    $state = $application->state;
    $transitions = [];

    if ($state->canTransitionTo(\Domain\EventApplications\States\InValidationApplicationState::class)) {
        $transitions['validate'] = __('event_applications.actions.move_to_validation');
    }
    if ($state->canTransitionTo(\Domain\EventApplications\States\ApprovedApplicationState::class)) {
        $transitions['approve'] = __('event_applications.actions.validate_application');
    }
    if ($state->canTransitionTo(\Domain\EventApplications\States\ReturnedForCorrectionApplicationState::class)) {
        $transitions['return'] = __('event_applications.actions.request_changes');
    }
    if ($state->canTransitionTo(\Domain\EventApplications\States\RejectedApplicationState::class)) {
        $transitions['reject'] = __('event_applications.actions.reject_application');
    }
    if ($state->canTransitionTo(\Domain\EventApplications\States\PublishedApplicationState::class)) {
        $transitions['publish'] = __('event_applications.actions.publish');
    }
@endphp

<div class="card" style="background-color: #fff; backdrop-filter: none;" x-data="{
    selectedAction: '',
    showModal: false,
    modalNotes: '',
    stateRoutes: {
        validate: '{{ route($routeNamespace . '.event-applications.validate', ['application' => $application->id]) }}',
        approve: '{{ route($routeNamespace . '.event-applications.approve', ['application' => $application->id]) }}',
        return: '{{ route($routeNamespace . '.event-applications.return', ['application' => $application->id]) }}',
        reject: '{{ route($routeNamespace . '.event-applications.reject', ['application' => $application->id]) }}',
        publish: '{{ route($routeNamespace . '.event-applications.publish', ['application' => $application->id]) }}'
    },
    get requiresNotes() {
        return this.selectedAction === 'reject' || this.selectedAction === 'return';
    },
    get actionLabel() {
        const labels = @js($transitions);
        return labels[this.selectedAction] || '';
    },
    openModal() {
        if (!this.selectedAction) return;
        this.modalNotes = '';
        this.showModal = true;
    }
}">
    <h3 class="grow font-semibold text-slate-800 truncate mb-4">{{ __('event_applications.labels.application_state') }}</h3>

    {{-- Current State Badge --}}
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.current_state') }}</label>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
              style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
            {{ $application->stateName() }}
        </span>
    </div>

    @if(count($transitions) > 0)
        {{-- State Select Dropdown --}}
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1 text-slate-600" for="state-action-select">{{ __('event_applications.labels.select_new_state') }}</label>
            <select id="state-action-select"
                    x-model="selectedAction"
                    class="form-select w-full">
                <option value="">{{ __('event_applications.labels.select_new_state') }}</option>
                @foreach($transitions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Save State Button --}}
        <button type="button"
                @click="openModal()"
                :disabled="!selectedAction"
                class="btn btn-primary w-full"
                :class="{ 'opacity-50 cursor-not-allowed': !selectedAction }">
            {{ __('event_applications.actions.save_state') }}
        </button>
    @endif

    {{-- Confirmation Modal (teleported to body to escape card overflow:hidden) --}}
    <template x-teleport="body">
        <div x-show="showModal"
             class="fixed inset-0 bg-slate-900 bg-opacity-50 z-50 transition-opacity"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-out duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showModal = false">

            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full"
                         @click.stop
                         x-show="showModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-4">

                        <div class="px-6 py-4 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800" x-text="actionLabel"></h3>
                        </div>

                        <form :action="stateRoutes[selectedAction]"
                              method="POST">
                            @csrf

                            <div class="px-6 py-4">
                                <label class="block text-sm font-medium mb-2" for="notes">
                                    {{ __('event_applications.labels.notes') }}
                                    <span x-show="requiresNotes" class="text-rose-500">*</span>
                                </label>
                                <textarea id="notes"
                                          name="notes"
                                          rows="4"
                                          class="form-textarea w-full"
                                          x-model="modalNotes"
                                          :required="requiresNotes"
                                          :placeholder="selectedAction === 'reject' ? '{{ __('event_applications.placeholders.rejection_reason') }}' : (selectedAction === 'return' ? '{{ __('event_applications.placeholders.correction_notes') }}' : '{{ __('event_applications.placeholders.approval_notes') }}')"></textarea>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 rounded-b-lg flex justify-end space-x-2">
                                <button type="button"
                                        @click="showModal = false"
                                        class="btn btn-secondary">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                        class="btn"
                                        :class="{
                                            'btn-success': selectedAction === 'approve',
                                            'btn-warning': selectedAction === 'return',
                                            'btn-danger': selectedAction === 'reject'
                                        }">
                                    {{ __('common.confirm') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
