<?php

namespace Domain\Imports\Actions;

use Domain\Imports\Models\Import;
use Domain\Imports\Models\ImportError;
use Illuminate\Support\Facades\Log;

class ProcessEntityImportChunkAction
{
    protected ValidateEntityBulkDataAction $validateAction;

    protected BulkInsertEntitiesAction $bulkInsertAction;

    public function __construct(
        ValidateEntityBulkDataAction $validateAction,
        BulkInsertEntitiesAction $bulkInsertAction
    ) {
        $this->validateAction = $validateAction;
        $this->bulkInsertAction = $bulkInsertAction;
    }

    /**
     * Process a chunk of entity import data.
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

            Log::info('Entity chunk validation results', [
                'import_id' => $importId,
                'chunk_index' => $chunkIndex,
                'total_records' => count($mappedData),
                'valid_count' => count($validationResults['valid'] ?? []),
                'error_count' => count($validationResults['errors'] ?? []),
            ]);

            // Process validation errors
            $this->logValidationErrors($import, $validationResults['errors'], $chunkIndex);

            // Handle valid records based on duplicate strategy
            if (! empty($validationResults['valid'])) {
                $this->processValidRecords(
                    $import,
                    $validationResults['valid'],
                    $import->options['duplicate_strategy'] ?? 'skip'
                );
            }

            // Update progress
            $import->increment('processed_rows', count($chunk));
            $import->increment('error_count', count($validationResults['errors']));
            $import->increment('success_count', count($validationResults['valid']));

            // Check if this was the last chunk
            if ($import->processed_rows >= $import->total_rows) {
                $import->markAsCompleted();
            }

        } catch (\Exception $e) {
            Log::error('Entity import chunk processing failed', [
                'import_id' => $importId,
                'chunk_index' => $chunkIndex,
                'error' => $e->getMessage(),
            ]);

            // Log the error
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => $chunkIndex * 1000,
                'error_message' => 'Chunk processing failed: ' . $e->getMessage(),
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
                    } elseif ($systemField === 'district_id' && ! empty($value)) {
                        // Store district name/id for validation
                        $mappedRow['district'] = $value;
                        if (is_numeric($value)) {
                            $mappedRow[$systemField] = (int) $value;
                        }
                    } elseif ($systemField === 'federation_id' && ! empty($value)) {
                        // Handle federation ID
                        if (is_numeric($value)) {
                            $mappedRow[$systemField] = (int) $value;
                        } else {
                            // Try to resolve federation name to ID
                            // Escape LIKE wildcards to prevent SQL injection
                            $escapedValue = str_replace(['%', '_'], ['\\%', '\\_'], $value);
                            $federation = \Domain\Federations\Models\Federation::where('name', 'LIKE', '%' . $escapedValue . '%')->first();
                            if ($federation) {
                                $mappedRow[$systemField] = $federation->id;
                            }
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

    /**
     * Process valid records based on duplicate strategy.
     */
    protected function processValidRecords(Import $import, array $validRecords, string $strategy): void
    {
        // Find duplicates by member_number or name+country
        $duplicates = $this->detectDuplicates($validRecords);
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
                        $record['id'] = $duplicates[$index]['entity']->id;
                        $toUpdate[] = $record;
                        break;
                    case 'create_with_suffix':
                        $toInsert[] = $record;
                        break;
                    case 'manual_review':
                        ImportError::create([
                            'import_id' => $import->id,
                            'row_number' => $index,
                            'row_data' => $record,
                            'error_message' => 'Duplicate found - requires manual review',
                            'severity' => 'warning',
                        ]);
                        break;
                }
            } else {
                $toInsert[] = $record;
            }
        }

        // Process insertions
        if (! empty($toInsert)) {
            Log::info('ProcessEntityImportChunkAction processing insertions', [
                'strategy' => $strategy,
                'toInsert_count' => count($toInsert),
                'duplicates_count' => count($duplicates),
            ]);

            if ($strategy === 'create_with_suffix' && ! empty($duplicates)) {
                // Separate duplicates that need suffix
                $needSuffix = [];
                $regular = [];

                foreach ($toInsert as $record) {
                    if ($this->isDuplicate($record)) {
                        $needSuffix[] = $record;
                    } else {
                        $regular[] = $record;
                    }
                }

                // Insert regular records
                if (! empty($regular)) {
                    $this->bulkInsertAction->execute($regular, $import->options);
                }

                // Insert with suffix
                if (! empty($needSuffix)) {
                    $this->bulkInsertAction->createWithSuffix($needSuffix, '_import_' . date('Ymd'));
                }
            } else {
                $this->bulkInsertAction->execute($toInsert, $import->options);
            }
        }

        // Process updates
        if (! empty($toUpdate)) {
            $this->bulkInsertAction->updateExisting($toUpdate);
        }

        // Log skipped records
        foreach ($toSkip as $index) {
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => $index,
                'row_data' => $validRecords[$index],
                'error_message' => 'Skipped - duplicate entity',
                'severity' => 'warning',
            ]);
        }
    }

    /**
     * Detect duplicate entities in the import batch.
     */
    protected function detectDuplicates(array $records): array
    {
        $duplicates = [];

        foreach ($records as $index => $record) {
            $existingEntity = $this->findExistingEntity($record);
            if ($existingEntity) {
                $duplicates[$index] = [
                    'entity' => $existingEntity,
                    'match_type' => $this->determineMatchType($existingEntity, $record),
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Find existing entity by member_number or name+country.
     */
    protected function findExistingEntity(array $record): ?\Domain\Entities\Models\Entity
    {
        // First, try to find by member_number
        if (! empty($record['member_number'])) {
            $entity = \Domain\Entities\Models\Entity::where('member_number', $record['member_number'])
                ->whereNull('deleted_at')
                ->first();
            if ($entity) {
                return $entity;
            }
        }

        // Then, try to find by name + country
        if (! empty($record['name']) && ! empty($record['country_id'])) {
            return \Domain\Entities\Models\Entity::where('name', $record['name'])
                ->where('country_id', $record['country_id'])
                ->whereNull('deleted_at')
                ->first();
        }

        return null;
    }

    /**
     * Determine the type of match (member_number or name+country).
     */
    protected function determineMatchType(\Domain\Entities\Models\Entity $entity, array $record): string
    {
        if (! empty($record['member_number']) && $entity->member_number === $record['member_number']) {
            return 'member_number';
        }

        return 'name_country';
    }

    /**
     * Check if a record is a duplicate.
     */
    protected function isDuplicate(array $record): bool
    {
        return $this->findExistingEntity($record) !== null;
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
}
