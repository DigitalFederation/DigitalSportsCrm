<?php

namespace Domain\Imports\Actions;

use App\Models\Country;
use Domain\Imports\Models\Import;
use Domain\Imports\Models\ImportError;
use Illuminate\Support\Facades\Log;

class ProcessImportChunkAction
{
    protected ValidateBulkDataAction $validateAction;

    protected DetectDuplicatesAction $detectDuplicatesAction;

    protected BulkInsertIndividualsAction $bulkInsertAction;

    public function __construct(
        ValidateBulkDataAction $validateAction,
        DetectDuplicatesAction $detectDuplicatesAction,
        BulkInsertIndividualsAction $bulkInsertAction
    ) {
        $this->validateAction = $validateAction;
        $this->detectDuplicatesAction = $detectDuplicatesAction;
        $this->bulkInsertAction = $bulkInsertAction;
    }

    /**
     * Process a chunk of import data.
     */
    public function execute(int $importId, array $chunk, int $chunkIndex): void
    {
        $import = Import::find($importId);
        if (! $import || $import->status === 'cancelled') {
            return;
        }

        try {
            // Map fields according to import configuration
            $mappedData = $this->mapFields($chunk, $import->field_mapping);

            // Validate the chunk
            $validationResults = $this->validateAction->execute($mappedData);

            \Log::info('Chunk validation results', [
                'import_id' => $importId,
                'chunk_index' => $chunkIndex,
                'total_records' => count($mappedData),
                'valid_count' => count($validationResults['valid'] ?? []),
                'error_count' => count($validationResults['errors'] ?? []),
                'first_errors' => array_slice($validationResults['errors'] ?? [], 0, 3, true),
                'first_mapped_row' => $mappedData[0] ?? null,
            ]);

            // Process validation errors
            $this->logValidationErrors($import, $validationResults['errors'], $chunkIndex);

            // Handle valid records based on duplicate strategy
            $operationCounts = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
            if (! empty($validationResults['valid'])) {
                $operationCounts = $this->processValidRecords(
                    $import,
                    $validationResults['valid'],
                    $import->options['duplicate_strategy'] ?? 'skip'
                );
            }

            // Update progress with ACTUAL counts, not validation counts
            $import->increment('processed_rows', count($chunk));
            $import->increment('error_count', count($validationResults['errors']));
            // Use actual inserted + updated count as success (not validation valid count)
            $actualSuccessCount = $operationCounts['inserted'] + $operationCounts['updated'];
            $import->increment('success_count', $actualSuccessCount);

            \Log::info('Chunk progress updated', [
                'import_id' => $importId,
                'chunk_index' => $chunkIndex,
                'validation_valid' => count($validationResults['valid'] ?? []),
                'actual_inserted' => $operationCounts['inserted'],
                'actual_updated' => $operationCounts['updated'],
                'actual_skipped' => $operationCounts['skipped'],
                'success_count_incremented' => $actualSuccessCount,
            ]);

            // Check if this was the last chunk
            if ($import->processed_rows >= $import->total_rows) {
                $import->markAsCompleted();
            }

        } catch (\Exception $e) {
            Log::error('Import chunk processing failed', [
                'import_id' => $importId,
                'chunk_index' => $chunkIndex,
                'error' => $e->getMessage(),
            ]);

            // Log the error
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => $chunkIndex * 1000,
                'error_message' => 'Chunk processing failed: '.$e->getMessage(),
                'severity' => 'error',
                'row_data' => ['chunk_index' => $chunkIndex],
            ]);

            // Mark import as failed if too many errors
            if ($import->error_count > ($import->total_rows * 0.5)) {
                $import->markAsFailed('Too many errors encountered');
            }
        }
    }

    /**
     * Map CSV fields to system fields based on mapping configuration.
     */
    protected function mapFields(array $chunk, array $fieldMapping): array
    {
        $mappedData = [];

        foreach ($chunk as $rowIndex => $row) {
            $mappedRow = [];

            foreach ($fieldMapping as $csvColumn => $systemField) {
                if (! empty($systemField) && isset($row[$csvColumn])) {
                    $value = trim($row[$csvColumn]);

                    // Special handling for country_id field
                    if ($systemField === 'country_id' && ! empty($value)) {
                        $mappedRow['country'] = $value; // Store original country value for validation
                        if (is_numeric($value)) {
                            $mappedRow[$systemField] = (int) $value;
                        }
                        // Non-numeric country values will be resolved by ValidateBulkDataAction
                    } elseif ($systemField === 'district_id' && ! empty($value)) {
                        // Store district value for validation and resolution
                        $mappedRow['district'] = $value;
                        if (is_numeric($value)) {
                            $mappedRow[$systemField] = (int) $value;
                        }
                        // Non-numeric district values will be resolved by ValidateBulkDataAction
                    } elseif ($systemField === 'entity_member_number' && ! empty($value)) {
                        // Store entity_member_number for validation and resolution to entity_id
                        $mappedRow[$systemField] = $value;
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

    /**
     * Process valid records based on duplicate strategy.
     *
     * @return array{inserted: int, updated: int, skipped: int}
     */
    protected function processValidRecords(Import $import, array $validRecords, string $strategy): array
    {
        $counts = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];

        // Find duplicates
        $duplicates = $this->detectDuplicatesAction->execute($validRecords);
        $toInsert = [];
        $toUpdate = [];
        $toSkip = [];

        foreach ($validRecords as $index => $record) {
            if (isset($duplicates[$index])) {
                switch ($strategy) {
                    case 'skip':
                        $toSkip[] = $index;
                        break;
                    case 'update':
                        $record['id'] = $duplicates[$index]['individual']->id;
                        $toUpdate[] = $record;
                        break;
                    case 'create_with_suffix':
                        $toInsert[] = $record; // Will be handled by createWithSuffix
                        break;
                    case 'manual_review':
                        // Log for manual review
                        ImportError::create([
                            'import_id' => $import->id,
                            'row_number' => $index,
                            'row_data' => $record,
                            'error_message' => 'Duplicate found - requires manual review',
                            'severity' => 'warning',
                        ]);
                        $counts['skipped']++;
                        break;
                }
            } else {
                $toInsert[] = $record;
            }
        }

        // Process insertions
        if (! empty($toInsert)) {
            \Log::info('ProcessImportChunkAction processing insertions', [
                'strategy' => $strategy,
                'toInsert_count' => count($toInsert),
                'duplicates_count' => count($duplicates),
                'toInsert_emails' => array_column($toInsert, 'email'),
            ]);

            if ($strategy === 'create_with_suffix' && ! empty($duplicates)) {
                // Separate duplicates that need suffix
                $needSuffix = [];
                $regular = [];

                foreach ($toInsert as $record) {
                    if ($this->detectDuplicatesAction->existsByEmail($record['email'])) {
                        $needSuffix[] = $record;
                    } else {
                        $regular[] = $record;
                    }
                }

                \Log::info('Separated records for suffix strategy', [
                    'needSuffix_count' => count($needSuffix),
                    'regular_count' => count($regular),
                    'needSuffix_emails' => array_column($needSuffix, 'email'),
                ]);

                // Insert regular records
                if (! empty($regular)) {
                    $result = $this->bulkInsertAction->execute($regular, $import->options);
                    $this->logInsertFailures($import, $result['failures'] ?? []);
                    $counts['inserted'] += $result['individuals'] ?? 0;
                    $counts['skipped'] += $result['skipped'] ?? 0;
                }

                // Insert with suffix
                if (! empty($needSuffix)) {
                    \Log::info('Calling createWithSuffix', [
                        'records' => count($needSuffix),
                        'suffix' => '_import_'.date('Ymd'),
                    ]);
                    $suffixResult = $this->bulkInsertAction->createWithSuffix($needSuffix, '_import_'.date('Ymd'));
                    $counts['inserted'] += $suffixResult['individuals'] ?? 0;
                    $counts['skipped'] += $suffixResult['skipped'] ?? 0;
                }
            } else {
                $result = $this->bulkInsertAction->execute($toInsert, $import->options);
                $this->logInsertFailures($import, $result['failures'] ?? []);
                $counts['inserted'] += $result['individuals'] ?? 0;
                $counts['skipped'] += $result['skipped'] ?? 0;
            }
        }

        // Process updates
        if (! empty($toUpdate)) {
            $updatedCount = $this->bulkInsertAction->updateExisting($toUpdate);
            $counts['updated'] += $updatedCount;
        }

        // Log skipped records (duplicates with skip strategy)
        foreach ($toSkip as $index) {
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => $index,
                'row_data' => $validRecords[$index],
                'error_message' => 'Skipped - duplicate record',
                'severity' => 'warning',
            ]);
            $counts['skipped']++;
        }

        return $counts;
    }

    /**
     * Log validation errors to the database.
     */
    protected function logValidationErrors(Import $import, array $errors, int $chunkIndex): void
    {
        foreach ($errors as $rowIndex => $rowErrors) {
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => ($chunkIndex * 1000) + $rowIndex,
                'error_message' => implode('; ', $rowErrors),
                'severity' => 'error',
                'row_data' => [],
            ]);
        }
    }

    /**
     * Log insert failures to the database.
     * Note: error_count for insert failures is handled here since these are not validation errors.
     * success_count is NOT decremented because we now use actual counts from BulkInsertIndividualsAction.
     */
    protected function logInsertFailures(Import $import, array $failures): void
    {
        foreach ($failures as $failure) {
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => 0,
                'error_message' => 'Insert failed for ' . ($failure['email'] ?? 'unknown') . ': ' . ($failure['error'] ?? 'Unknown error'),
                'severity' => 'error',
                'row_data' => $failure,
            ]);

            // Increment error count for insert failures (these are separate from validation errors)
            $import->increment('error_count');
        }
    }
}
