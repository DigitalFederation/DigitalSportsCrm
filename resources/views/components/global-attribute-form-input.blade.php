<div class="flex flex-col gap-4">
    @foreach ($globalAttributes as $attribute)
        @php
            $attributeId = $attribute['id'] ?? null;
            $attributeType = $attribute['type'] ?? 'TEXT';
            $isRequired = $attribute['required'] ?? false;
            $inputName = "globalAttributeValues.{$attributeId}";
            $value = $values[$attributeId] ?? $attribute['default_value'] ?? '';
            $options = $attribute['options'] ?? [];
            $attributeName = $attribute['name'] ?? '';

            $baseInputClass = "w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500";
            $selectClass = "form-select " . $baseInputClass;
            $inputClass = "form-input " . $baseInputClass;
            $textareaClass = "form-textarea " . $baseInputClass;
        @endphp

        @if($attributeId)
            
            @if ($attributeType === 'HIDDEN')
                @php
                    // Do not render anything for hidden attributes in the UI
                    continue;
                @endphp
            @endif

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $attributeName }}
                    @if($isRequired)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($attributeType === 'SELECT')
                    <select
                        wire:model="{{ $inputName }}"
                        name="globalAttributes[{{ $attributeId }}]"
                        id="global_attribute_{{ $attributeId }}"
                        class="{{ $selectClass }}"
                        {{ $isRequired ? 'required' : '' }}>
                        <option value="">Select {{ $attributeName }}</option>
                        @foreach($options as $option)
                            <option value="{{ $option }}" {{ $option == $value ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>

                @elseif($attributeType === 'TEXTAREA')
                    <textarea
                        wire:model="{{ $inputName }}"
                        name="globalAttributes[{{ $attributeId }}]"
                        id="global_attribute_{{ $attributeId }}"
                        class="{{ $textareaClass }}"
                        {{ $isRequired ? 'required' : '' }}
                        rows="3">{{ $value }}</textarea>

                @elseif($attributeType === 'TIME')
                    @include('components.evt_event.attribute-time-input', [
                        'attributeId' => $attributeId,
                        'inputName' => $inputName,
                        'value' => $value,
                        'isRequired' => $isRequired
                    ])
                @else
                    <input
                        type="text"
                        wire:model="{{ $inputName }}"
                        name="globalAttributes[{{ $attributeId }}]"
                        id="global_attribute_{{ $attributeId }}"
                        class="{{ $inputClass }}"
                        value="{{ $value }}"
                        placeholder="{{ $attributeName }}"
                        {{ $isRequired ? 'required' : '' }}>
                @endif
            </div>
        @endif
    @endforeach
</div>
