<?php

namespace Domain\Imports\Actions;

use Domain\Imports\Data\ImportValidationResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class ValidateEntityImportFileAction
{
    protected ValidateEntityBulkDataAction $validateBulkAction;

    protected int $chunkSize = 500;

    public function __construct(ValidateEntityBulkDataAction $validateBulkAction)
    {
        $this->validateBulkAction = $validateBulkAction;
    }

    /**
     * Validate entire import file with all rows.
     */
    public function execute(UploadedFile $file, array $fieldMapping): ImportValidationResult
    {
        $startTime = microtime(true);

        try {
            // Read file data based on type
            $data = $this->readFileData($file);

            if (empty($data)) {
                throw new \Exception('File is empty or could not be read');
            }

            // Remove header row
            $headers = array_shift($data);
            $totalRows = count($data);

            // Initialize counters
            $validRows = 0;
            $errorRows = 0;
            $warningRows = 0;
            $allErrors = [];
            $allWarnings = [];
            $validRecords = [];
            $sampleValidRecords = [];
            $sampleErrorRecords = [];

            // Process in chunks for memory efficiency
            $chunks = array_chunk($data, $this->chunkSize);

            foreach ($chunks as $chunkIndex => $chunk) {
                // Map fields for this chunk
                $mappedChunk = $this->mapFields($chunk, $headers, $fieldMapping);

                // Validate chunk
                $chunkValidation = $this->validateBulkAction->execute($mappedChunk);

                // Process results
                foreach ($chunkValidation['errors'] as $index => $errors) {
                    $rowNumber = ($chunkIndex * $this->chunkSize) + $index + 2; // +2 for header and 0-index
                    $errorRows++;

                    $errorRecord = [
                        'row_number' => $rowNumber,
                        'errors' => $errors,
                        'data' => $mappedChunk[$index] ?? [],
                    ];

                    $allErrors[$rowNumber] = $errors;

                    // Keep sample of error records
                    if (count($sampleErrorRecords) < 5) {
                        $sampleErrorRecords[] = $errorRecord;
                    }
                }

                foreach ($chunkValidation['warnings'] as $index => $warnings) {
                    $rowNumber = ($chunkIndex * $this->chunkSize) + $index + 2;
                    $warningRows++;
                    $allWarnings[$rowNumber] = $warnings;
                }

                foreach ($chunkValidation['valid'] as $index => $validRecord) {
                    $rowNumber = ($chunkIndex * $this->chunkSize) + $index + 2;
                    $validRows++;

                    // Store all valid records for actual import
                    $validRecords[$rowNumber] = $validRecord;

                    // Keep sample of valid records for preview
                    if (count($sampleValidRecords) < 10) {
                        $sampleValidRecords[] = [
                            'row_number' => $rowNumber,
                            'data' => $validRecord,
                        ];
                    }
                }
            }

            $validationTime = microtime(true) - $startTime;

            return new ImportValidationResult(
                totalRows: $totalRows,
                validRows: $validRows,
                errorRows: $errorRows,
                warningRows: $warningRows,
                errors: $allErrors,
                warnings: $allWarnings,
                validRecords: $validRecords,
                sampleValidRecords: $sampleValidRecords,
                sampleErrorRecords: $sampleErrorRecords,
                hasErrors: $errorRows > 0,
                validationTime: $validationTime
            );

        } catch (\Exception $e) {
            Log::error('Entity import file validation failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            throw $e;
        }
    }

    /**
     * Read file data based on file type.
     */
    protected function readFileData(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->readCSV($file);
        }

        // Excel files
        return Excel::toArray(new class {}, $file)[0];
    }

    /**
     * Read CSV file with delimiter detection.
     */
    protected function readCSV(UploadedFile $file): array
    {
        $delimiter = $this->detectCSVDelimiter($file);

        $reader = Reader::createFromPath($file->getRealPath(), 'r');
        $reader->setDelimiter($delimiter);

        $data = [];
        foreach ($reader as $row) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Detect CSV delimiter.
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

        arsort($counts);
        $detectedDelimiter = array_key_first($counts);

        return $counts[$detectedDelimiter] > 0 ? $detectedDelimiter : ',';
    }

    /**
     * Map CSV fields to system fields.
     */
    protected function mapFields(array $chunk, array $headers, array $fieldMapping): array
    {
        $mappedData = [];

        foreach ($chunk as $row) {
            $mappedRow = [];

            // Combine headers with row data
            $rowData = array_combine($headers, $row);

            foreach ($fieldMapping as $csvColumn => $systemField) {
                if (! empty($systemField) && isset($rowData[$csvColumn])) {
                    $value = trim($rowData[$csvColumn]);

                    // Special handling for country_id field
                    if ($systemField === 'country_id' && ! empty($value)) {
                        $mappedRow['country'] = $value; // Store original country value for validation
                        if (is_numeric($value)) {
                            $mappedRow[$systemField] = (int) $value;
                        }
                    } elseif ($systemField === 'district_id' && ! empty($value)) {
                        // Store district name/id for validation
                        $mappedRow['district'] = $value;
                        if (is_numeric($value)) {
                            $mappedRow[$systemField] = (int) $value;
                        }
                    } elseif ($systemField === 'member_number' && ! empty($value)) {
                        // Ensure member_number is treated as integer if numeric
                        $mappedRow[$systemField] = is_numeric($value) ? (int) $value : $value;
                    } else {
                        $mappedRow[$systemField] = $value;
                    }
                }
            }

            // Ensure email is lowercase
            if (isset($mappedRow['email'])) {
                $mappedRow['email'] = strtolower($mappedRow['email']);
            }

            $mappedData[] = $mappedRow;
        }

        return $mappedData;
    }
}
