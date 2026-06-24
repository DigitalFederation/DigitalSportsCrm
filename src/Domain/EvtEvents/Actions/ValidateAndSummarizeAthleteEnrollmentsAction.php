<?php

namespace Domain\EvtEvents\Actions;

class ValidateAndSummarizeAthleteEnrollmentsAction
{
    protected $validationMessages = [
        'input_required' => 'This attribute is required.',
        // Add other rules as needed
    ];

    public function execute(array $selectedIndividuals, array $attributeRules, ?array $inputAttributes = null)
    {
        $summary = [];

        foreach ($selectedIndividuals as $individual) {
            $isValid = true;
            $failedRules = [];

            foreach ($attributeRules as $attribute) {
                // Ensure we have the correct structure for attribute data
                $attributeData = $attribute['attribute_data'] ?? $attribute;
                $fillableType = $attributeData['fillable_type'] ?? 'MANUAL';
                $attributeId = $attributeData['id'] ?? null;

                if (! $attributeId) {
                    continue;
                }

                $attributeValue = null;

                if ($fillableType === 'MANUAL') {
                    $attributeValue = $inputAttributes[$individual['id']][$attributeId] ?? null;
                } else {
                    $comparisonField = $attributeData['comparison_field'] ?? null;
                    if (! $comparisonField || strpos($comparisonField, '.') === false) {
                        continue;
                    }

                    [$table, $column] = explode('.', $comparisonField);
                    if ($table === 'individuals') {
                        $attributeValue = $individual[$column] ?? null;
                    } else {
                        $attributeValue = $individual['individual_federations'][0][$table][$column] ?? null;
                    }
                }

                // Skip if no value and not required
                if (is_null($attributeValue) && ! ($attributeData['required'] ?? false)) {
                    continue;
                }

                // Validate required fields
                if (is_null($attributeValue) && ($attributeData['required'] ?? false)) {
                    $isValid = false;
                    $failedRules[] = [
                        'attribute' => $attributeId,
                        'rule' => 'required',
                        'message' => $this->validationMessages['input_required'],
                    ];

                    continue;
                }

                // Validate rules if they exist
                if (! empty($attribute['rules'])) {
                    foreach ($attribute['rules'] as $rule) {
                        $processAction = new ProcessAttributeRulesAction;
                        $comparisonValue = $rule['default_value'] ?? $attributeValue;

                        if (! $processAction->execute(
                            $attributeValue,
                            $rule['operator'],
                            null,
                            null,
                            null,
                            $comparisonValue,
                            true
                        )) {
                            $isValid = false;
                            $failedRules[] = [
                                'attribute' => $attributeId,
                                'rule' => $rule,
                                'message' => $this->validationMessages[$rule] ?? $rule,
                            ];
                        }
                    }
                }
            }

            $summary[] = [
                'individual' => $individual,
                'valid' => $isValid,
                'failed_rules' => $failedRules,
            ];
        }

        return $summary;
    }
}
