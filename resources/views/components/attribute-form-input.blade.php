@php
    $attributeId = $attributeId ?? null;
    $attributeType = $attributeType ?? 'TEXT';
    $isRequired = $isRequired ?? false;
    $inputName = $wire ?? '';
    $options = $options ?? [];
    $attributeName = $attributeName ?? '';
    $isReadOnly = $isReadOnly ?? false;

    // Log incoming values

    \Log::debug('Attribute Form Input - Incoming Values', [
        'attributeId' => $attributeId,
        'attributeType' => $attributeType,
        'value' => $value ?? null,
        'options' => $options,
        'attributeName' => $attributeName,
        'isReadOnly' => $isReadOnly,
        'inputName' => $inputName,
        'value_type' => is_object($value) ? get_class($value) : gettype($value),
    ]);


    // Helper function to normalize value
    $normalizeValue = function($val) {

        if (is_array($val)) {
            $result = $val['value'] ?? '';

            return $result;
        }
        $result = (string)($val ?? '');

        return $result;
    };

    // Helper function to normalize option
    $normalizeOption = function($value, $key = null) {
        if (is_array($value)) {
            $result = [
                'label' => $value['label'] ?? $value['name'] ?? '',
                'value' => $value['value'] ?? $value['label'] ?? $value['name'] ?? ''
            ];

            return $result;
        }
        // Handle key-value pairs where key is the value and value is the label
        if ($key !== null) {
            $result = [
                'label' => $value,
                'value' => $key
            ];

            return $result;
        }
        $result = [
            'label' => (string)$value,
            'value' => (string)$value
        ];

        return $result;
    };

    // Normalize the main value
    $value = $normalizeValue($value);


    // Normalize options if they exist
    $normalizedOptions = [];
    if ($attributeType === 'SELECT' || $attributeType === 'OUTOFRACE') {
        foreach ($options as $key => $option) {
            $normalized = $normalizeOption($option, is_string($key) ? $key : null);
            $normalizedOptions[$normalized['value']] = $normalized['label'];
        }
    } else {
        foreach ($options as $k => $v) {
            if (is_numeric($k)) {
                $normalized = $normalizeOption($v);
                $normalizedOptions[$normalized['value']] = $normalized['label'];
            } else {
                $normalizedOptions[$k] = is_array($v) ? json_encode($v) : (string)$v;
            }
        }
    }


    $baseInputClass = 'w-full rounded-lg border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-400 shadow-sm';
    $selectClass = 'form-select ' . $baseInputClass;
    $inputClass = 'form-input ' . $baseInputClass;
    $textareaClass = 'form-textarea ' . $baseInputClass;

@endphp

<style>
    .checked {
     background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
     border-color: transparent;
     background-color: #3b82f6;
     background-size: 100% 100%;
     background-position: center;
     background-repeat: no-repeat;
 }
</style>

@if ($attributeId)
    @if ($isReadOnly)
        <div class="space-y-1">
            <label class="block text-sm font-medium text-blue-800">
                {{ $attributeName }}
            </label>
            <div class="mt-1 text-sm text-blue-700 bg-blue-50 p-3 rounded-lg border border-blue-100">
                {{ $value }}
            </div>
        </div>
    @else
        @if ($attributeType === 'OUTOFRACE')

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $attributeName }}
                </label>

                <div class="flex flex-wrap gap-4">
                    @foreach ($normalizedOptions as $optionValue => $optionLabel)
                        <label class="inline-flex items-center">
                            <input type="radio"
                                   wire:model="{{ $inputName }}"
                                   name="attributes[{{ $attributeId }}]"
                                   value="{{ $optionValue }}"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-400 border-blue-300 {{ $optionValue == $value ? 'checked' : '' }}"
                                   @checked($optionValue == $value)
                                   @if ($isRequired) required @endif>
                            <span class="ml-2 text-sm text-blue-700">{{ $optionLabel }}</span>
                        </label>
                    @endforeach
                </div>
                @error($inputName)
                    <p class="mt-1 text-sm text-red-500 bg-red-50 p-2 rounded-lg border-l-4 border-red-400">{{ $message }}</p>
                @enderror
            </div>
        @elseif($attributeType === 'SELECT')
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $attributeName }}
                    @if ($isRequired)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <select wire:model="{{ $inputName }}" name="attributes[{{ $attributeId }}]"
                    id="attribute_{{ $attributeId }}" class="{{ $selectClass }}"
                    {{ $isRequired ? 'required' : '' }}>
                    <option value="">{{ __('Select') }} {{ $attributeName }}</option>
                    @foreach ($normalizedOptions as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" {{ $optionValue == $value ? 'selected' : '' }}>
                            {{ $optionLabel }}
                        </option>
                    @endforeach
                </select>

                @error("attributes.{$attributeId}")
                    <p class="mt-1 text-sm text-red-500 bg-red-50 p-2 rounded-lg border-l-4 border-red-400">{{ $message }}</p>
                @enderror
            </div>
        @elseif($attributeType === 'TEXTAREA')
            <div class="space-y-1">
                <label class="block text-sm font-medium text-blue-800">
                    {{ $attributeName }}
                    @if ($isRequired)
                        <span class="text-red-500 ml-1">*</span>
                    @endif
                </label>
                <textarea wire:model="{{ $inputName }}" name="attributes[{{ $attributeId }}]" id="attribute_{{ $attributeId }}"
                    class="{{ $textareaClass }}" {{ $isRequired ? 'required' : '' }} rows="3">{{ $value }}</textarea>
            </div>
        @elseif($attributeType === 'TEXT')
            <div class="space-y-1">
                <label class="block text-sm font-medium text-blue-800">
                    {{ $attributeName }}
                    @if ($isRequired)
                        <span class="text-red-500 ml-1">*</span>
                    @endif
                </label>
                <input type="text" wire:model="{{ $inputName }}" name="attributes[{{ $attributeId }}]"
                    id="attribute_{{ $attributeId }}" class="{{ $inputClass }}" value="{{ $value }}"
                    placeholder="{{ $attributeName }}" {{ $isRequired ? 'required' : '' }}>
            </div>
        @elseif($attributeType === 'TIME' || $attributeType === 'BESTTIME')
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $attributeName }}
                    @if ($isRequired)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                @include('components.evt_event.attribute-time-input', [
                    'attributeId' => $attributeId,
                    'inputName' => $inputName,
                    'value' => $value,
                    'isRequired' => $isRequired,
                ])
            </div>
        @elseif($attributeType === 'BIRTHDATE')
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $attributeName }}
                    @if ($isRequired)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="date" wire:model="{{ $inputName }}" name="attributes[{{ $attributeId }}]"
                    id="attribute_{{ $attributeId }}" class="{{ $inputClass }}" value="{{ $value }}"
                    {{ $isRequired ? 'required' : '' }}>
            </div>
        @else
            <div class="space-y-1">
                <label class="block text-sm font-medium text-blue-800">
                    {{ $attributeName }}
                    @if ($isRequired)
                        <span class="text-red-500 ml-1">*</span>
                    @endif
                </label>

                <input type="text" wire:model="{{ $inputName }}" name="attributes[{{ $attributeId }}]"
                    id="attribute_{{ $attributeId }}" class="{{ $inputClass }}" value="{{ $value }}"
                    placeholder="{{ $attributeName }}" {{ $isRequired ? 'required' : '' }}>
            </div>
        @endif
    @endif
@endif
