@props(['entityId', 'inputId', 'wireModel' => null])

<!-- resources/views/components/entity-instructor-selector.blade.php -->
<div
    x-data="{
        openModal() {
            Livewire.dispatch('open-entity-instructor-modal-{{ $inputId }}', { inputId: '{{ $inputId }}' });
        }
    }"
    x-on:individual-selected.window=" // Keep listener name generic for potential reuse
        if ($event.detail.inputId === '{{ $inputId }}') {
            const inputElement = document.getElementById('{{ $inputId }}');
            if (inputElement) {
                inputElement.value = $event.detail.code;
                inputElement.dispatchEvent(new Event('input')); // Trigger input event for filters
            }
             @if($wireModel)
                $wire.set('{{$wireModel}}', $event.detail.code);
             @endif
        }"

    class="relative inline-block">

    <button type="button"
            @click="openModal"
            class="btn bg-slate-200 rounded-none btn-info">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Reference the new Livewire component --}}
    <livewire:entity-instructor-selector-modal :entity-id="$entityId"
                                               :input-id="$inputId"
                                               :wire:key="'entity-instructor-modal-' . $inputId" />

</div>
