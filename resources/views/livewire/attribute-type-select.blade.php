<div>
    <label class="block text-sm font-medium mb-1">{{ __('Options for the Select') }}</label>
    @foreach ($options as $index => $option)
        <div class="flex space-x-2">
            <input type="text" name="attribute_data[]" wire:model.live="options.{{ $index }}" class="form-input w-full"
                   placeholder="{{ __('Option') }} {{ $index + 1 }}">
            <button type="button" wire:click="removeOption({{ $index }})"
                    class="px-2 py-1 text-sm text-red-600 bg-red-100 rounded hover:bg-red-200 focus:outline-none">
                {{ __('Remove') }}
            </button>
        </div>
    @endforeach
    <button type="button" wire:click="addOption"
            class="mt-2 btn btn-info">
        {{ __('Insert Option') }}
    </button>
</div>
