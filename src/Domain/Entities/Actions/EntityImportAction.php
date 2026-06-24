<?php

namespace Domain\Entities\Actions;

use App\Imports\EntityImport;
use Domain\Imports\Actions\ValidateEntityImportFileAction;
use Domain\Imports\Data\ImportValidationResult;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class EntityImportAction
{
    public function __invoke(
        UploadedFile $file,
        array $fieldMapping,
        ?int $federationId = null,
        array $options = []
    ): array {
        try {
            // Validate file
            $this->validateFile($file);

            // Create import instance with field mapping
            $import = new EntityImport($fieldMapping);

            // Perform the import
            Excel::import($import, $file);

            // Get import results
            $results = $import->getResults();

            // Log import activity
            $this->logImportActivity($results, $federationId);

            // Clean up temporary file
            if ($file->isValid()) {
                @unlink($file->getRealPath());
            }

            return [
                'success' => true,
                'results' => $results,
                'message' => $this->generateSummaryMessage($results),
            ];

        } catch (Exception $e) {
            Log::error('Entity import failed: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'federation_id' => $federationId,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => null,
            ];
        }
    }

    public function analyzeFile(UploadedFile $file): array
    {
        try {
            $this->validateFile($file);

            // Check if it's a CSV file and handle delimiter detection
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'csv') {
                // For CSV files, detect and use the appropriate delimiter
                $data = $this->readCSVWithDelimiterDetection($file);
            } else {
                // For Excel files, use the standard approach
                $data = Excel::toArray(new class {}, $file)[0];
            }

            if (empty($data)) {
                throw new Exception('File is empty or could not be read');
            }

            $headers = array_shift($data);
            $sampleRows = array_slice($data, 0, 5);

            // Suggest field mappings based on header names
            $suggestions = $this->suggestFieldMappings($headers);

            // Analyze data quality
            $analysis = $this->analyzeDataQuality($headers, $sampleRows);

            return [
                'success' => true,
                'headers' => $headers,
                'sample_rows' => $sampleRows,
                'row_count' => count($data),
                'suggested_mappings' => $suggestions,
                'data_analysis' => $analysis,
            ];

        } catch (Exception $e) {
            Log::error('File analysis failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function validateMapping(UploadedFile $file, array $fieldMapping): ImportValidationResult
    {
        $this->validateFile($file);

        // Use the entity-specific ValidateEntityImportFileAction for full validation
        $validateAction = app(ValidateEntityImportFileAction::class);

        return $validateAction->execute($file, $fieldMapping);
    }

    protected function validateFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new Exception('Uploaded file is not valid');
        }

        $allowedMimes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception('File must be CSV, XLS, or XLSX format');
        }

        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception('File size must be less than 10MB');
        }
    }

    protected function suggestFieldMappings(array $headers): array
    {
        $supportedFields = EntityImport::getSupportedFields();
        $suggestions = [];

        foreach ($headers as $header) {
            $normalizedHeader = strtolower(trim($header));
            $bestMatch = null;
            $highestScore = 0;

            foreach ($supportedFields as $fieldKey => $fieldConfig) {
                foreach ($fieldConfig['suggestions'] as $suggestion) {
                    $normalizedSuggestion = strtolower($suggestion);

                    // Exact match
                    if ($normalizedHeader === $normalizedSuggestion) {
                        $bestMatch = $fieldKey;
                        $highestScore = 100;
                        break 2;
                    }

                    // Contains match
                    if (str_contains($normalizedHeader, $normalizedSuggestion) ||
                        str_contains($normalizedSuggestion, $normalizedHeader)) {
                        $score = 80;
                        if ($score > $highestScore) {
                            $bestMatch = $fieldKey;
                            $highestScore = $score;
                        }
                    }

                    // Similar string match
                    $similarity = 0;
                    similar_text($normalizedHeader, $normalizedSuggestion, $similarity);
                    if ($similarity > 70 && $similarity > $highestScore) {
                        $bestMatch = $fieldKey;
                        $highestScore = $similarity;
                    }
                }
            }

            $suggestions[$header] = [
                'suggested_field' => $bestMatch,
                'confidence' => $highestScore,
                'options' => array_keys($supportedFields),
            ];
        }

        return $suggestions;
    }

    protected function analyzeDataQuality(array $headers, array $sampleRows): array
    {
        $analysis = [
            'total_columns' => count($headers),
            'empty_columns' => 0,
            'columns_with_data' => 0,
            'potential_issues' => [],
        ];

        foreach ($headers as $index => $header) {
            $columnData = array_column($sampleRows, $index);
            $nonEmptyCount = count(array_filter($columnData, fn ($val) => ! empty($val)));

            if ($nonEmptyCount === 0) {
                $analysis['empty_columns']++;
                $analysis['potential_issues'][] = "Column '{$header}' appears to be empty";
            } else {
                $analysis['columns_with_data']++;
            }

            // Check for potential email columns
            if (str_contains(strtolower($header), 'email') || str_contains(strtolower($header), 'mail')) {
                $validEmails = 0;
                foreach ($columnData as $value) {
                    if (! empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $validEmails++;
                    }
                }
                if ($validEmails === 0 && $nonEmptyCount > 0) {
                    $analysis['potential_issues'][] = "Column '{$header}' seems to be email but contains invalid email formats";
                }
            }

            // Check for potential URL columns
            if (str_contains(strtolower($header), 'url') || str_contains(strtolower($header), 'website')) {
                $validUrls = 0;
                foreach ($columnData as $value) {
                    if (! empty($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                        $validUrls++;
                    }
                }
                if ($validUrls === 0 && $nonEmptyCount > 0) {
                    $analysis['potential_issues'][] = "Column '{$header}' seems to be URL but contains invalid URL formats";
                }
            }
        }

        return $analysis;
    }

    protected function logImportActivity(array $results, ?int $federationId): void
    {
        Log::info('Entity import completed', [
            'federation_id' => $federationId,
            'total_rows' => $results['total_rows'],
            'success_count' => $results['success_count'],
            'error_count' => $results['error_count'],
            'warning_count' => $results['warning_count'],
        ]);
    }

    /**
     * Read CSV file with automatic delimiter detection.
     */
    protected function readCSVWithDelimiterDetection(UploadedFile $file): array
    {
        $delimiter = $this->detectCSVDelimiter($file);

        // Read the CSV file with the detected delimiter
        $handle = fopen($file->getRealPath(), 'r');
        $data = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $data[] = $row;
        }

        fclose($handle);

        return $data;
    }

    /**
     * Detect CSV delimiter by analyzing the first line.
     */
    protected function detectCSVDelimiter(UploadedFile $file): string
    {
        $handle = fopen($file->getRealPath(), 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        // Return the delimiter with the highest count
        arsort($counts);
        $detectedDelimiter = array_key_first($counts);

        // If no delimiter found, default to comma
        return $counts[$detectedDelimiter] > 0 ? $detectedDelimiter : ',';
    }

    protected function generateSummaryMessage(array $results): string
    {
        $message = 'Import completed: ';
        $message .= "{$results['success_count']} entities imported successfully";

        if ($results['error_count'] > 0) {
            $message .= ", {$results['error_count']} failed";
        }

        if ($results['warning_count'] > 0) {
            $message .= ", {$results['warning_count']} with warnings";
        }

        return $message;
    }

    public function generateTemplate(): array
    {
        $supportedFields = EntityImport::getSupportedFields();
        $headers = [];
        $sampleData = [];

        foreach ($supportedFields as $fieldKey => $fieldConfig) {
            $headers[] = $fieldConfig['label'];

            // Generate sample data based on field type
            // Note: country_id is auto-set from Main Federation, not included in template
            $sampleData[] = match ($fieldKey) {
                'name' => 'Example Club',
                'legal_name' => 'Example Sports Association',
                'member_number' => '12345',
                'vat_number' => '000000000',
                'email' => 'club@example.test',
                'phone' => '+15550101000',
                'website' => 'https://club.example.test',
                'address' => 'Example Street 1',
                'location' => 'Example City',
                'postal_code' => '0000-000',
                'district_id' => 'Example District',
                'federation_id' => '1',
                'legal_responsible_person' => 'Example Responsible Person',
                'public_description' => 'Example club public description',
                'facebook_url' => 'https://social.example.test/example-club',
                'x_url' => 'https://social.example.test/example-club',
                'instagram_url' => 'https://social.example.test/example-club',
                'linkedin_url' => 'https://social.example.test/example-club',
                default => ''
            };
        }

        return [
            'headers' => $headers,
            'sample_data' => $sampleData,
            'field_descriptions' => $supportedFields,
        ];
    }
}
