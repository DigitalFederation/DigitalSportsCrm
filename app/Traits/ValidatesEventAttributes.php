<?php

namespace App\Traits;

use Domain\EvtEvents\Actions\ValidateAttributeRulesAction;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

trait ValidatesEventAttributes
{
    /**
     * @param  array|\Illuminate\Support\Collection  $attributeValues
     * @param  array|\Illuminate\Support\Collection  $disciplineAttributes
     */
    protected function validateAttributesAndRules($attributeValues, $disciplineAttributes): void
    {
        // Convert to array if Collection
        $attributeValues = $attributeValues instanceof Collection ? $attributeValues->toArray() : $attributeValues;
        $disciplineAttributes = $disciplineAttributes instanceof Collection ? $disciplineAttributes->toArray() : $disciplineAttributes;

        $validateAttributes = new ValidateAttributeRulesAction;

        try {
            // First validate required attributes
            $validateAttributes->validateRequiredAttributes(
                $attributeValues,
                $disciplineAttributes['attributes']
            );

            // Then validate attribute rules
            foreach ($disciplineAttributes['attributes'] as $attribute) {
                $attributeData = $attribute['attribute_data'] ?? $attribute;
                $attributeId = $attributeData['id'] ?? null;

                if ($attributeId) {
                    $attributeModel = Attribute::find($attributeId);
                    if ($attributeModel && $attributeModel->required) {
                        $value = $attributeValues[$attributeId] ?? null;

                        // Check if the value is empty or is the default value
                        $isEmpty = ! isset($value) || $value === null || $value === '';

                        // For time/besttime attributes, also check if it's the empty time (00:00.00)
                        $timeTypes = ['time', 'TIME', 'besttime', 'BESTTIME'];
                        if (! $isEmpty && in_array($attributeModel->attribute_type, $timeTypes)) {
                            $isEmpty = $this->isEmptyTimeValue($value);
                        }

                        if ($isEmpty || $value === $attributeModel->default_value) {
                            throw ValidationException::withMessages([
                                'attributes' => __('validation.attributes.provide_value', ['attribute' => $attributeData['name']]),
                            ]);
                        }
                    }

                    // Validate rules if they exist
                    if (! empty($attribute['rules'])) {
                        try {
                            $validateAttributes->execute(
                                $attributeValues,
                                collect($attribute['rules'])->map(fn ($rule) => AttributeRules::find($rule['id']))->filter()->all()
                            );
                        } catch (ValidationException $e) {
                            // Make rule validation messages more user-friendly
                            $friendlyMessage = $this->getFriendlyValidationMessage($attributeData['name'], $e->getMessage());
                            throw ValidationException::withMessages([
                                'attributes' => $friendlyMessage,
                            ]);
                        }
                    }
                }
            }
        } catch (ValidationException $e) {
            if (property_exists($this, 'errorMessages')) {
                $this->errorMessages[] = $e->getMessage();
            }
            throw $e;
        }
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $attributeValues
     */
    protected function processAttributeValues($attributeValues): array
    {
        $attributeValues = $attributeValues instanceof Collection ? $attributeValues->toArray() : $attributeValues;
        $processed = [];

        // Handle both nested and flat attribute value structures
        foreach ($attributeValues as $key => $value) {
            if (is_array($value) && array_key_exists('id', $value)) {
                // Handle structure from ManageEnrollment
                $processed[$value['id']] = $value['value'] ?? null;
            } elseif (is_array($value) && isset($value['value'])) {
                // Handle structure from IndividualCreateAthleteEnrollment
                $processed[$key] = $value['value'];
            } else {
                // Handle direct value assignment
                $processed[$key] = $value;
            }
        }

        return $processed;
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $attributes
     */
    protected function initializeAttributeValues($attributes, array $defaults = []): array
    {
        $attributes = $attributes instanceof Collection ? $attributes->toArray() : $attributes;
        $values = [];

        foreach ($attributes as $attributeId => $attribute) {
            $attributeData = $attribute['attribute_data'] ?? $attribute;
            $defaultValue = $defaults[$attributeId] ?? $attributeData['default_value'] ?? null;

            // Set default value for OUTOFRACE attributes
            if (($attributeData['type'] ?? '') === 'OUTOFRACE') {
                $defaultValue = 'no';
            }

            $values[$attributeId] = $defaultValue;
        }

        return $values;
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $attributeValues
     */
    protected function validateBatchAttributes($attributeValues, array $existingAttributes = []): array
    {
        $attributeValues = $attributeValues instanceof Collection ? $attributeValues->toArray() : $attributeValues;

        return (new ValidateAttributeRulesAction)->validateBatchAttributes(
            $attributeValues,
            $existingAttributes
        );
    }

    /**
     * Convert technical validation messages into user-friendly ones
     */
    protected function getFriendlyValidationMessage(string $attributeName, string $message): string
    {
        // Extract the rule type from the message if possible
        if (str_contains($message, 'max')) {
            return __('validation.attributes.exceeds_maximum', ['attribute' => $attributeName]);
        }
        if (str_contains($message, 'min')) {
            return __('validation.attributes.below_minimum', ['attribute' => $attributeName]);
        }
        if (str_contains($message, 'regex')) {
            return __('validation.attributes.incorrect_format', ['attribute' => $attributeName]);
        }
        if (str_contains($message, 'unique')) {
            return __('validation.attributes.already_used', ['attribute' => $attributeName]);
        }

        // Default to a generic but friendly message
        return __('validation.attributes.not_valid', ['attribute' => $attributeName]);
    }

    /**
     * Check if a time value is effectively empty (e.g., 00:00.00)
     */
    protected function isEmptyTimeValue($value): bool
    {
        if (! is_string($value)) {
            return true;
        }

        // Normalize the value
        $value = trim($value);

        // Check for empty time patterns
        $emptyPatterns = [
            '00:00.00',
            '0:00.00',
            '00:0.00',
            '0:0.00',
            '00:00:00.00',
            '0:00:00.00',
            '00.00',
            '0.00',
        ];

        return in_array($value, $emptyPatterns);
    }

    /**
     * Check if a time value is in a valid format
     */
    protected function isValidTimeFormat($value): bool
    {
        if (! is_string($value) || empty($value)) {
            return false;
        }

        // MM:SS.ms format (from time input component)
        if (preg_match('/^\d{1,2}:\d{1,2}[.,]\d{2}$/', $value)) {
            return true;
        }

        // HH:MM:SS.ms format
        if (preg_match('/^\d{1,2}:\d{1,2}:\d{1,2}[.,]\d{2}$/', $value)) {
            return true;
        }

        // SS.ms format
        if (preg_match('/^\d{1,2}[.,]\d{2}$/', $value)) {
            return true;
        }

        return false;
    }
}
