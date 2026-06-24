@props(['inputId'])

<div
    x-data="{
        openModal() {
            Livewire.dispatch('open-event-modal-{{ $inputId }}');
        }
    }"
    class="relative inline-block">

    <button type="button"
            @click="openModal"
            class="btn btn-secondary">
        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd" />
        </svg>
        <span class="ml-2">{{ __('Select') }}</span>
    </button>

    <livewire:event-individual-selector-modal 
        :input-id="$inputId"
        :wire:key="'event-modal-' . $inputId" />

</div>