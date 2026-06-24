<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AttributeFormInput extends Component
{
    public array $attribute;
    public $value;
    public array $options;
    public ?array $selected;
    public string $wire;
    public bool $isReadOnly;
    public array $attributeDebug;

    private const OUTOFRACE_OPTIONS = [
        'no' => 'Official Competitor',
        'yes' => 'Out of Competition',
    ];

    /**
     * Create a new component instance.
     */
    public function __construct(
        array $attribute,
        $value,
        array $options = [],
        ?array $selected = null,
        string $wire = '',
        bool $isReadOnly = false
    ) {
        $this->attribute = $attribute;
        $this->wire = $wire;
        $this->isReadOnly = (bool) ($attribute['read_only'] ?? $attribute['attribute_data']['read_only'] ?? $isReadOnly);

        // Debug the attribute structure
        $this->attributeDebug = [
            'full_attribute' => $attribute,
            'attribute_data_type' => gettype($attribute['attribute_data'] ?? null),
            'attribute_data_is_array' => is_array($attribute['attribute_data'] ?? null),
            'attribute_data_keys' => is_array($attribute['attribute_data'] ?? null) ? array_keys($attribute['attribute_data']) : [],
            'raw_options' => $options,
        ];

        // Set default value for OUTOFRACE attribute if value is null
        if ($this->isOutOfRaceAttribute() && $value === null) {
            $this->value = 'no';
        } else {
            $this->value = $value;
        }

        // Set options based on attribute type
        if ($this->isOutOfRaceAttribute()) {
            $this->options = self::OUTOFRACE_OPTIONS;
        } else {
            // Check various possible locations for options
            if (! empty($attribute['attribute_data']['options'])) {
                $this->options = $attribute['attribute_data']['options'];
            } elseif (! empty($attribute['attribute_data']['attribute_data']) && is_array($attribute['attribute_data']['attribute_data'])) {
                // If options are nested in attribute_data.attribute_data
                $this->options = $attribute['attribute_data']['attribute_data'];
            } elseif (! empty($attribute['attribute_data']) && is_array($attribute['attribute_data']) && isset($attribute['attribute_data'][0])) {
                // If attribute_data is a direct array of options
                $this->options = $attribute['attribute_data'];
            } elseif (! empty($options)) {
                $this->options = $options;
            } else {
                $this->options = [];
            }
        }

        $this->selected = $selected;
    }

    /**
     * Check if the attribute type is TIME.
     */
    public function isTimeAttribute(): bool
    {
        return ($this->attribute['attribute_data']['attribute_type'] ?? '') === 'TIME';
    }

    public function getAttributeId(): ?int
    {
        return $this->attribute['attribute_data']['id'] ?? null;
    }

    public function getAttributeType(): string
    {
        return $this->attribute['attribute_data']['attribute_type'] ?? $this->attribute['attribute_type'] ?? $this->attribute['attribute_data']['type'] ?? 'TEXT';
    }

    public function isRequired(): bool
    {
        return $this->attribute['attribute_data']['required'] ?? false;
    }

    /**
     * Get the time input pattern for validation.
     */
    public function getTimePattern(): string
    {
        return '^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)[.,](\d{2})$';
    }

    protected function getOptions(): array
    {
        if (! empty($this->attribute['attribute_data']['options'])) {
            return $this->attribute['attribute_data']['options'];
        } elseif (! empty($this->attribute['attribute_data']['attribute_data']) && is_array($this->attribute['attribute_data']['attribute_data'])) {
            // If options are nested in attribute_data.attribute_data
            return $this->attribute['attribute_data']['attribute_data'];
        } elseif (! empty($this->attribute['attribute_data']) && is_array($this->attribute['attribute_data']) && isset($this->attribute['attribute_data'][0])) {
            // If attribute_data is directly an array of options
            return $this->attribute['attribute_data'];
        }

        return [];
    }

    public function isOutOfRaceAttribute(): bool
    {
        return $this->getAttributeType() === 'OUTOFRACE';
    }

    public function getAttributeName(): string
    {
        if ($this->isOutOfRaceAttribute()) {
            return 'Competition Status';
        }

        return $this->attribute['name']
            ?? $this->attribute['attribute_data']['name']
            ?? '';
    }

    protected function getFormattedOptions(): array
    {
        $options = $this->options;

        if (empty($options)) {
            if (! empty($this->attribute['attribute_data']['options'])) {
                $options = $this->attribute['attribute_data']['options'];
            } elseif (! empty($this->attribute['attribute_data']['attribute_data']) && is_array($this->attribute['attribute_data']['attribute_data'])) {
                // If options are nested in attribute_data.attribute_data
                $options = $this->attribute['attribute_data']['attribute_data'];
            } elseif (! empty($this->attribute['attribute_data']) && is_array($this->attribute['attribute_data']) && isset($this->attribute['attribute_data'][0])) {
                // If attribute_data is directly an array of options
                $options = $this->attribute['attribute_data'];
            } else {
                $options = [];
            }
        }

        // Handle empty options
        if (empty($options)) {
            return [];
        }

        // If first element is an array, assume it's already properly formatted
        if (is_array(reset($options))) {
            return $options;
        }

        // If options is a sequential array, convert to associative
        // Use the option value as both key and value to ensure the text is saved
        if (array_keys($options) === range(0, count($options) - 1)) {
            $result = [];
            foreach ($options as $option) {
                $result[$option] = $option;
            }

            return $result;
        }

        return $options;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.attribute-form-input', [
            'attributeId' => $this->getAttributeId(),
            'attributeType' => $this->isOutOfRaceAttribute() ? 'OUTOFRACE' : $this->getAttributeType(),
            'isRequired' => $this->isRequired(),
            'options' => $this->getFormattedOptions(),
            'attributeName' => $this->getAttributeName(),
            'isReadOnly' => $this->isReadOnly,
            'attributeDebug' => $this->attributeDebug ?? [],
        ]);
    }
}
