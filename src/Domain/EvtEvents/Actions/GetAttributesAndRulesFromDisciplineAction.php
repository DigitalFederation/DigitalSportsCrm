<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Discipline;

class GetAttributesAndRulesFromDisciplineAction
{
    public function execute($disciplineId): array
    {
        if (empty($disciplineId)) {
            return [
                'attributes' => [],
                'global_attributes' => [],
            ];
        }

        try {
            $discipline = Discipline::with(['attributes.rules'])->findOrFail($disciplineId);

            $attributes = [];
            $globalAttributes = [];

            foreach ($discipline->attributes as $disciplineAttribute) {

                // Ensure we have a valid attribute_data array
                $attributeData = is_array($disciplineAttribute->attribute_data) ? $disciplineAttribute->attribute_data : [];

                // Extract the base options
                $options = $attributeData['options'] ?? [];

                // Also look for numeric keys at the top level, they might be options
                $numericKeyValues = [];
                foreach ($attributeData as $key => $value) {
                    if (is_numeric($key)) {
                        $numericKeyValues[$key] = $value;
                        // Remove from the attribute data to prevent duplication
                        unset($attributeData[$key]);
                    }
                }

                // If we found numeric keys and options is empty, use the numeric keys as options
                if (! empty($numericKeyValues) && empty($options)) {
                    $options = $numericKeyValues;
                }
                // Don't merge if options already exist - explicit options take precedence
                // This ensures backward compatibility with existing data

                // Convert sequential options to associative if needed
                // This matches the logic in AttributeFormInput::getFormattedOptions
                if (! empty($options) && array_keys($options) === range(0, count($options) - 1)) {
                    $formattedOptions = [];
                    foreach ($options as $option) {
                        $formattedOptions[$option] = $option;
                    }
                    $options = $formattedOptions;
                }

                \Log::debug('Extracted and formatted options', [
                    'options' => $options,
                    'options_type' => gettype($options),
                ]);

                // Create a base attribute data structure with required fields
                $baseData = [
                    'id' => $disciplineAttribute->id,
                    'name' => $disciplineAttribute->name,
                    'type' => $disciplineAttribute->attribute_type,
                    'required' => $attributeData['required'] ?? false,
                    'default_value' => $disciplineAttribute->pivot->custom_value ?? $disciplineAttribute->default_value,
                    'options' => $options,
                ];

                // Remove options from original data to prevent duplication
                if (isset($attributeData['options'])) {
                    unset($attributeData['options']);
                }

                // Merge the base data with the attribute data
                $attributeData = array_merge($baseData, $attributeData);

                \Log::debug('Final attribute data', [
                    'attributeData' => $attributeData,
                ]);

                if ($disciplineAttribute->fillable_global) {
                    $globalAttributes[] = $attributeData;
                } else {
                    $attributes[] = [
                        'attribute_data' => $attributeData,
                        'rules' => $disciplineAttribute->rules->toArray(),
                    ];
                }
            }

            return [
                'attributes' => $attributes,
                'global_attributes' => $globalAttributes,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting discipline attributes', [
                'error' => $e->getMessage(),
                'discipline_id' => $disciplineId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'attributes' => [],
                'global_attributes' => [],
            ];
        }
    }
}
