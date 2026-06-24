<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAttributeRuleOperatorsEnum;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Illuminate\Validation\ValidationException;

class ValidateAttributeRulesAction
{
    /**
     * Validates a single value against a rule.
     *
     * @param  mixed  $value  The value to be validated.
     * @param  string  $operator  The operator used for validation.
     * @param  mixed|null  $comparison_value  The value to compare against.
     * @param  array  $context  The context for validation, usually an array of existing values.
     * @return bool True if the validation passes, false otherwise.
     */
    public function validateValue($value, string $operator, $comparison_value = null, array $context = []): bool
    {
        return match ($operator) {
            EvtAttributeRuleOperatorsEnum::EQUAL->value => $value == $comparison_value,
            EvtAttributeRuleOperatorsEnum::NOT_EQUAL->value => $value != $comparison_value,
            EvtAttributeRuleOperatorsEnum::IDENTICAL->value => $value === $comparison_value,
            EvtAttributeRuleOperatorsEnum::NOT_IDENTICAL->value => $value !== $comparison_value,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN->value => $value > $comparison_value,
            EvtAttributeRuleOperatorsEnum::LESS_THAN->value => $value < $comparison_value,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN_OR_EQUAL->value => $value >= $comparison_value,
            EvtAttributeRuleOperatorsEnum::LESS_THAN_OR_EQUAL->value => $value <= $comparison_value,
            EvtAttributeRuleOperatorsEnum::REGEX_MATCH->value => preg_match($comparison_value, $value) === 1,
            EvtAttributeRuleOperatorsEnum::STARTS_WITH->value => str_starts_with($value, $comparison_value),
            EvtAttributeRuleOperatorsEnum::ENDS_WITH->value => str_ends_with($value, $comparison_value),
            EvtAttributeRuleOperatorsEnum::CONTAINS->value => str_contains($value, $comparison_value),
            EvtAttributeRuleOperatorsEnum::ELEMENT_EXISTS->value => in_array($value, (array) $comparison_value),
            EvtAttributeRuleOperatorsEnum::KEY_EXISTS->value => array_key_exists($value, (array) $comparison_value),
            EvtAttributeRuleOperatorsEnum::MAX_OCCURRENCES->value => count(array_filter($context, fn ($v) => $v === $value)) < $comparison_value,
            default => false,
        };
    }

    public function validateAttributes(array $attributes, array $context = []): bool
    {
        foreach ($attributes as $attributeId => $value) {
            $attribute = Attribute::find($attributeId);
            if ($attribute) {
                foreach ($attribute->rules as $rule) {
                    if (! $this->validateValue($value, $rule->operator, $rule->comparison_value, $context)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Validates a batch of attributes against existing attributes and their rules.
     *
     * @param  array  $batchAttributes  An array of attribute values to be validated.
     * @param  array  $existingAttributes  An array of existing attribute values.
     * @return array An array of error messages for each validation failure.
     */
    public function validateBatchAttributes(array $batchAttributes, array $existingAttributes): array
    {
        $errorMessages = [];

        foreach ($batchAttributes as $attributeId => $values) {
            $attribute = Attribute::find($attributeId);
            if ($attribute) {
                // Convert $values to an array if it's a string
                $valuesArray = is_array($values) ? $values : [$values];

                foreach ($attribute->rules as $rule) {
                    $context = array_merge($existingAttributes[$attributeId] ?? [], $valuesArray);
                    $failed = false;
                    foreach ($valuesArray as $value) {
                        if (! $this->validateValue($value, $rule->operator, $rule->comparison_value, $context)) {
                            $failed = true;
                            break;
                        }
                    }
                    if ($failed) {
                        $errorMessages[] = $this->formatValidationError($attribute->name, $rule->operator, $rule->comparison_value);
                    }
                }
            }
        }

        return $errorMessages;
    }

    private function formatValidationError(string $attributeName, string $operator, $comparisonValue): string
    {
        $operatorEnum = EvtAttributeRuleOperatorsEnum::getEnumFromValue($operator);
        $params = ['attribute' => $attributeName, 'value' => $comparisonValue];

        return match ($operatorEnum) {
            EvtAttributeRuleOperatorsEnum::EQUAL => __('validation.attributes.must_be_equal', $params),
            EvtAttributeRuleOperatorsEnum::NOT_EQUAL => __('validation.attributes.must_not_be_equal', $params),
            EvtAttributeRuleOperatorsEnum::IDENTICAL => __('validation.attributes.must_be_identical', $params),
            EvtAttributeRuleOperatorsEnum::NOT_IDENTICAL => __('validation.attributes.must_not_be_identical', $params),
            EvtAttributeRuleOperatorsEnum::GREATER_THAN => __('validation.attributes.must_be_greater_than', $params),
            EvtAttributeRuleOperatorsEnum::LESS_THAN => __('validation.attributes.must_be_less_than', $params),
            EvtAttributeRuleOperatorsEnum::GREATER_THAN_OR_EQUAL => __('validation.attributes.must_be_greater_or_equal', $params),
            EvtAttributeRuleOperatorsEnum::LESS_THAN_OR_EQUAL => __('validation.attributes.must_be_less_or_equal', $params),
            EvtAttributeRuleOperatorsEnum::REGEX_MATCH => __('validation.attributes.invalid_format', $params),
            EvtAttributeRuleOperatorsEnum::STARTS_WITH => __('validation.attributes.must_start_with', $params),
            EvtAttributeRuleOperatorsEnum::ENDS_WITH => __('validation.attributes.must_end_with', $params),
            EvtAttributeRuleOperatorsEnum::CONTAINS => __('validation.attributes.must_contain', $params),
            EvtAttributeRuleOperatorsEnum::MAX => __('validation.attributes.must_not_exceed', $params),
            EvtAttributeRuleOperatorsEnum::MIN => __('validation.attributes.must_be_at_least', $params),
            EvtAttributeRuleOperatorsEnum::MAX_OCCURRENCES => __('validation.attributes.max_occurrences', $params),
            EvtAttributeRuleOperatorsEnum::ELEMENT_EXISTS => __('validation.attributes.must_exist_in_array', $params),
            default => __('validation.attributes.is_invalid', $params)
        };
    }

    /**
     * Validates a set of attribute values against their rules.
     *
     * @param  array  $attributeValues  The attribute values to validate
     * @param  array  $rules  The rules to validate against
     *
     * @throws ValidationException
     */
    public function execute(array $attributeValues, array $rules): bool
    {
        foreach ($rules as $rule) {
            if (! $this->validateRule($rule, $attributeValues)) {
                throw ValidationException::withMessages([
                    'attributes' => __('validation.attributes.validation_failed', ['attribute' => $rule->attribute->name, 'rule' => $rule->name]),
                ]);
            }
        }

        return true;
    }

    protected function validateRule(AttributeRules $rule, array $attributeValues): bool
    {
        // Get the attribute value
        $value = $attributeValues[$rule->attribute_id] ?? null;

        // Check if attribute is required
        if ($rule->attribute->required) {
            if ($value === null || $value === '') {
                throw ValidationException::withMessages([
                    'attributes' => __('validation.attributes.is_required', ['attribute' => $rule->attribute->name]),
                ]);
            }
        }

        // Skip validation if value is empty and not required
        if (($value === null || $value === '') && ! $rule->attribute->required) {
            return true;
        }

        // Special handling for TIME and BESTTIME type attributes
        $timeTypes = ['time', 'TIME', 'besttime', 'BESTTIME'];
        if (in_array($rule->attribute->attribute_type, $timeTypes)) {
            return $this->validateTimeValue($value, $rule->operator, $rule->comparison_value);
        }

        // Special handling for BIRTHDATE type attributes
        $dateTypes = ['birthdate', 'BIRTHDATE', 'date', 'DATE'];
        if (in_array($rule->attribute->attribute_type, $dateTypes)) {
            return $this->validateDateValue($value, $rule->operator, $rule->comparison_value);
        }

        // Use the validateValue method instead of switch statement to handle all operators
        return $this->validateValue($value, $rule->operator, $rule->comparison_value);
    }

    /**
     * Validates date values with proper date-based comparison.
     */
    protected function validateDateValue($value, string $operator, $comparisonValue = null): bool
    {
        // First validate the date format is correct
        if (! $this->isValidDateFormat($value)) {
            return false;
        }

        // If no comparison value, just format validation
        if ($comparisonValue === null) {
            return true;
        }

        // For REGEX_MATCH on date attributes, validate format instead
        if ($operator === EvtAttributeRuleOperatorsEnum::REGEX_MATCH->value) {
            return $this->isValidDateFormat($value);
        }

        // Convert dates to timestamps for comparison
        $valueTimestamp = strtotime($value);
        $comparisonTimestamp = strtotime($comparisonValue);

        if ($valueTimestamp === false || $comparisonTimestamp === false) {
            return false;
        }

        return match ($operator) {
            EvtAttributeRuleOperatorsEnum::EQUAL->value => $valueTimestamp == $comparisonTimestamp,
            EvtAttributeRuleOperatorsEnum::NOT_EQUAL->value => $valueTimestamp != $comparisonTimestamp,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN->value => $valueTimestamp > $comparisonTimestamp,
            EvtAttributeRuleOperatorsEnum::LESS_THAN->value => $valueTimestamp < $comparisonTimestamp,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN_OR_EQUAL->value => $valueTimestamp >= $comparisonTimestamp,
            EvtAttributeRuleOperatorsEnum::LESS_THAN_OR_EQUAL->value => $valueTimestamp <= $comparisonTimestamp,
            default => true,
        };
    }

    /**
     * Check if a value is a valid date format.
     */
    protected function isValidDateFormat($value): bool
    {
        if (! is_string($value) || empty($value)) {
            return false;
        }

        // Try to parse common date formats
        $timestamp = strtotime($value);

        return $timestamp !== false;
    }

    /**
     * Validates time values with proper time-based comparison.
     * Supports formats: MM:SS.ms, HH:MM:SS.ms, SS.ms
     */
    protected function validateTimeValue($value, string $operator, $comparisonValue = null): bool
    {
        // First validate the time format is correct
        if (! $this->isValidTimeFormat($value)) {
            return false;
        }

        // If no comparison value or just format validation, return true
        if ($comparisonValue === null || $operator === EvtAttributeRuleOperatorsEnum::REGEX_MATCH->value) {
            // For time attributes, always accept valid time formats regardless of regex
            return $this->isValidTimeFormat($value);
        }

        // Convert times to seconds for comparison
        $valueSeconds = $this->timeToSeconds($value);
        $comparisonSeconds = $this->timeToSeconds($comparisonValue);

        return match ($operator) {
            EvtAttributeRuleOperatorsEnum::EQUAL->value => $valueSeconds == $comparisonSeconds,
            EvtAttributeRuleOperatorsEnum::NOT_EQUAL->value => $valueSeconds != $comparisonSeconds,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN->value => $valueSeconds > $comparisonSeconds,
            EvtAttributeRuleOperatorsEnum::LESS_THAN->value => $valueSeconds < $comparisonSeconds,
            EvtAttributeRuleOperatorsEnum::GREATER_THAN_OR_EQUAL->value => $valueSeconds >= $comparisonSeconds,
            EvtAttributeRuleOperatorsEnum::LESS_THAN_OR_EQUAL->value => $valueSeconds <= $comparisonSeconds,
            default => true,
        };
    }

    /**
     * Check if a value is a valid time format.
     * Accepts: MM:SS.ms, HH:MM:SS.ms, SS.ms
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

    /**
     * Convert time string to total seconds (including hundredths as decimal).
     */
    protected function timeToSeconds($value): float
    {
        if (! is_string($value)) {
            return 0.0;
        }

        // Normalize separator to dot
        $value = str_replace(',', '.', $value);

        // Handle MM:SS.ms format
        if (preg_match('/^(\d{1,2}):(\d{1,2})\.(\d{2})$/', $value, $matches)) {
            $minutes = (int) $matches[1];
            $seconds = (int) $matches[2];
            $hundredths = (int) $matches[3];

            return ($minutes * 60) + $seconds + ($hundredths / 100);
        }

        // Handle HH:MM:SS.ms format
        if (preg_match('/^(\d{1,2}):(\d{1,2}):(\d{1,2})\.(\d{2})$/', $value, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];
            $hundredths = (int) $matches[4];

            return ($hours * 3600) + ($minutes * 60) + $seconds + ($hundredths / 100);
        }

        // Handle SS.ms format
        if (preg_match('/^(\d{1,2})\.(\d{2})$/', $value, $matches)) {
            $seconds = (int) $matches[1];
            $hundredths = (int) $matches[2];

            return $seconds + ($hundredths / 100);
        }

        return 0.0;
    }

    /**
     * Validates that all required attributes have values.
     *
     * @param  array  $attributeValues  The attribute values to validate
     * @param  array  $attributes  The attributes to check against
     *
     * @throws ValidationException
     */
    public function validateRequiredAttributes(array $attributeValues, array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            $attributeData = $attribute['attribute_data'] ?? $attribute;
            $attributeId = $attributeData['id'] ?? null;

            if ($attributeId) {
                $attribute = Attribute::find($attributeId);
                if ($attribute && $attribute->required) {
                    $value = $attributeValues[$attributeId] ?? null;
                    if ($value === null || $value === '') {
                        throw ValidationException::withMessages([
                            'attributes' => __('validation.attributes.is_required', ['attribute' => $attribute->name]),
                        ]);
                    }
                }
            }
        }

        return true;
    }
}
