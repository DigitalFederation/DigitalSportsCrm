<!-- resources/views/components/entity/entity-individual-selector.blade.php -->
<div x-data="{
        openModal() {
            Livewire.dispatch('open-modal-{{ $inputId }}');
        }
    }"
     x-on:entity-individual-selected.window="
        if ($event.detail.inputId === '{{ $inputId }}') {
            document.getElementById('{{ $inputId }}').value = $event.detail.code;
            @if($wireModel) $wire.set('{{$wireModel}}', $event.detail.code); @endif
        }
    "
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

    <livewire:entity.entity-individual-selector-modal :entity-id="$entityId"
                                                      :input-id="$inputId"
                                                      :wire:key="'modal-' . $inputId" />
</div>
