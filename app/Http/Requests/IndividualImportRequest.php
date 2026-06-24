<?php

namespace App\Http\Requests;

use App\Imports\IndividualImport;
use Illuminate\Foundation\Http\FormRequest;

class IndividualImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'file' => [
                'required',
                'file',
                'mimes:csv,xlsx,xls',
                'max:10240', // 10MB max
            ],
            'step' => 'required|string|in:upload,mapping,validation,import',
            'field_mapping' => 'required_if:step,validation,import|array',
            'federation_ids' => 'nullable|array',
            'federation_ids.*' => 'integer|exists:federation,id',
            'main_federation_id' => 'nullable|integer|exists:federation,id',
            'entity_id' => 'nullable|integer|exists:entity,id',
            'duplicate_strategy' => 'nullable|string|in:skip,update,create_with_suffix,manual_review',
            'send_notifications' => 'nullable|boolean',
            'auto_assign_federation' => 'nullable|boolean',
        ];

        // Add field mapping validation when step is mapping or later
        if (in_array($this->step, ['mapping', 'validation', 'import'])) {
            $supportedFields = array_keys(IndividualImport::getSupportedFields());
            $supportedFields[] = ''; // Allow empty mapping (skip column)

            $rules['field_mapping.*'] = 'nullable|string|in:' . implode(',', $supportedFields);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be in CSV, XLS, or XLSX format.',
            'file.max' => 'The file size must not exceed 10MB.',
            'step.required' => 'Import step is required.',
            'step.in' => 'Invalid import step.',
            'field_mapping.required_if' => 'Field mapping is required for this step.',
            'field_mapping.array' => 'Field mapping must be an array.',
            'field_mapping.*.in' => 'Invalid field mapping selection.',
            'federation_ids.array' => 'Federations must be an array.',
            'federation_ids.*.exists' => 'One or more selected federations do not exist.',
            'main_federation_id.exists' => 'Main federation does not exist.',
            'entity_id.exists' => 'Selected entity does not exist.',
            'duplicate_strategy.in' => 'Invalid duplicate handling strategy.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'file' => 'import file',
            'field_mapping' => 'field mapping',
            'federation_ids' => 'federations',
            'main_federation_id' => 'main federation',
            'entity_id' => 'entity',
            'duplicate_strategy' => 'duplicate strategy',
            'send_notifications' => 'send notifications',
            'auto_assign_federation' => 'auto assign federation',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate required field mappings when importing
            if ($this->step === 'import' && is_array($this->field_mapping)) {
                $supportedFields = IndividualImport::getSupportedFields();
                $requiredFields = array_filter($supportedFields, fn ($field) => $field['required']);
                $mappedFields = array_filter($this->field_mapping);

                foreach ($requiredFields as $fieldKey => $fieldConfig) {
                    if (! in_array($fieldKey, $mappedFields)) {
                        $validator->errors()->add(
                            'field_mapping',
                            "Required field '{$fieldConfig['label']}' must be mapped to a column."
                        );
                    }
                }

                // Check for duplicate mappings (same internal field mapped to multiple columns)
                $mappedValues = array_filter($this->field_mapping);
                if (count($mappedValues) !== count(array_unique($mappedValues))) {
                    $validator->errors()->add(
                        'field_mapping',
                        'Each field can only be mapped to one column.'
                    );
                }
            }

            // Validate file content when available
            if ($this->hasFile('file') && $this->file('file')->isValid()) {
                try {
                    // Basic file structure validation
                    $this->validateFileStructure();
                } catch (\Exception $e) {
                    $validator->errors()->add('file', $e->getMessage());
                }
            }
        });
    }

    /**
     * Validate the basic structure of the uploaded file.
     */
    protected function validateFileStructure(): void
    {
        $file = $this->file('file');

        // Check if file can be read
        try {
            $handle = fopen($file->getRealPath(), 'r');
            if (! $handle) {
                throw new \Exception('File cannot be read.');
            }

            // Check if file has content
            $firstLine = fgets($handle);
            if (empty($firstLine)) {
                throw new \Exception('File appears to be empty.');
            }

            // Check minimum column count for CSV
            if ($file->getClientOriginalExtension() === 'csv') {
                // Detect delimiter
                $delimiters = [',', ';', "\t", '|'];
                $delimiter = $this->detectDelimiter($firstLine, $delimiters);

                $headers = str_getcsv($firstLine, $delimiter);
                if (count($headers) < 3) {
                    throw new \Exception('File must have at least 3 columns.');
                }
            }

            fclose($handle);

        } catch (\Exception $e) {
            throw new \Exception('File validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the field mapping with proper validation.
     */
    public function getFieldMapping(): array
    {
        $mapping = $this->input('field_mapping', []);

        // Filter out empty mappings
        return array_filter($mapping, fn ($value) => ! empty($value));
    }

    /**
     * Get import options.
     */
    public function getImportOptions(): array
    {
        return [
            'federation_ids' => $this->input('federation_ids', []),
            'main_federation_id' => $this->input('main_federation_id'),
            'entity_id' => $this->input('entity_id'),
            'duplicate_strategy' => $this->input('duplicate_strategy', 'skip'),
            'send_notifications' => $this->boolean('send_notifications', true),
            'auto_assign_federation' => $this->boolean('auto_assign_federation', false),
        ];
    }

    /**
     * Check if this is a specific step.
     */
    public function isStep(string $step): bool
    {
        return $this->input('step') === $step;
    }

    /**
     * Get the current step.
     */
    public function getStep(): string
    {
        return $this->input('step', 'upload');
    }

    /**
     * Detect CSV delimiter from a line of text.
     */
    protected function detectDelimiter(string $line, array $delimiters = [',', ';', "\t", '|']): string
    {
        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }

        // Return the delimiter with the highest count
        arsort($counts);
        $detectedDelimiter = array_key_first($counts);

        // If no delimiter found, default to comma
        return $counts[$detectedDelimiter] > 0 ? $detectedDelimiter : ',';
    }
}
