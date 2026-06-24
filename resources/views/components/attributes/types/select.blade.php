<select name="attributes[{{ $attributeId }}]"
        wire:model="attributeValues.{{ $selectedId }}.{{ $attributeId }}"
        id="attribute_{{ $attributeId }}"
        class="form-input w-full">
    <option value="">{{ __(' -- Select --') }}</option>
    @foreach($options as $option)
        <option value="{{ $option }}">{{ $option }}</option>
    @endforeach
</select>
