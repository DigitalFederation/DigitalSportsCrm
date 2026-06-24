<?php

namespace App\Livewire\Admin;

use App\Imports\IndividualImport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\IndividualImportAction;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class IndividualImportWizard extends Component
{
    use WithFileUploads;

    // Wizard state
    public string $currentStep = 'upload';
    public array $steps = ['upload', 'mapping', 'validation', 'import', 'completed'];

    // File upload
    public $importFile;
    public string $fileName = '';
    public array $fileAnalysis = [];

    // Field mapping
    public array $headers = [];
    public array $sampleRows = [];
    public array $fieldMapping = [];
    public array $supportedFields = [];

    // Validation results
    public array $validationResults = [];
    public array $validationErrors = [];
    public array $validationWarnings = [];

    // Import options
    public array $selectedFederations = [];
    public ?int $mainFederationId = null;
    public ?int $entityId = null;
    public string $duplicateStrategy = 'skip';
    public bool $sendNotifications = false;
    public bool $autoAssignFederation = false;

    // Import results
    public array $importResults = [];
    public bool $importInProgress = false;
    public int $progressPercentage = 0;
    public ?int $importId = null;
    public ?string $importStatus = null;
    public ?string $importErrorMessage = null;

    // UI state
    public bool $showAdvancedOptions = false;
    public array $errors = [];
    public array $warnings = [];

    // Validation rules
    protected $rules = [
        'importFile' => 'required|file|mimes:csv,xlsx,xls|max:10240',
    ];

    protected IndividualImportAction $importAction;

    public function boot(IndividualImportAction $importAction): void
    {
        $this->importAction = $importAction;
    }

    public function mount(): void
    {
        $this->supportedFields = IndividualImport::getSupportedFields();
        $this->initializeFieldMapping();
        $this->initializeFederations();
    }

    protected function initializeFederations(): void
    {
        $federations = Federation::select('id', 'name', 'is_default_federation', 'parent_id', 'is_local')
            ->orderBy('name')
            ->get();

        // Find and add main federation to selectedFederations
        foreach ($federations as $federation) {
            if ($federation->is_default_federation || $federation->isMainFederation()) {
                $this->selectedFederations[] = $federation->id;
                $this->mainFederationId = $federation->id;
                break;
            }
        }

        // If only one federation exists, select it by default
        if ($federations->count() === 1) {
            $this->selectedFederations[] = $federations->first()->id;
            $this->mainFederationId = $federations->first()->id;
        }
    }

    public function updatedImportFile()
    {
        if ($this->importFile) {
            try {
                $this->fileName = $this->importFile->getClientOriginalName();
                $this->clearErrors();

                // Validate file extension
                $extension = strtolower($this->importFile->getClientOriginalExtension());
                if (! in_array($extension, ['csv', 'xls', 'xlsx'])) {
                    throw new Exception('File must be CSV, XLS, or XLSX format');
                }

                // Validate file size (10MB)
                if ($this->importFile->getSize() > 10 * 1024 * 1024) {
                    throw new Exception('File size must not exceed 10MB');
                }

            } catch (Exception $e) {
                $this->fileName = '';
                $this->addCustomError('importFile', 'Invalid file: ' . $e->getMessage());
            }
        } else {
            $this->fileName = '';
        }
    }

    protected function initializeFieldMapping(): void
    {
        foreach ($this->supportedFields as $fieldKey => $fieldConfig) {
            $this->fieldMapping[$fieldKey] = '';
        }
    }

    public function render()
    {
        $federations = Federation::select('id', 'name')->orderBy('name')->get();

        return view('livewire.admin.individual-import-wizard', [
            'federations' => $federations,
            'duplicateStrategies' => [
                'skip' => __('import.duplicate_skip'),
                'update' => __('import.duplicate_update'),
                'create_with_suffix' => __('import.duplicate_create_suffix'),
                'manual_review' => __('import.duplicate_manual_review'),
            ],
        ]);
    }

    public function uploadFile(): void
    {
        // Clear any previous Livewire validation errors
        $this->resetErrorBag();

        // Custom validation to bypass Livewire's global rules
        if (! $this->importFile) {
            $this->addCustomError('importFile', 'Please select a file to upload.');

            return;
        }

        $extension = strtolower($this->importFile->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'xls', 'xlsx'])) {
            $this->addCustomError('importFile', 'The file must be in CSV, XLS, or XLSX format.');

            return;
        }

        if ($this->importFile->getSize() > 10 * 1024 * 1024) {
            $this->addCustomError('importFile', 'The file size must not exceed 10MB.');

            return;
        }

        try {
            $this->fileName = $this->importFile->getClientOriginalName();

            // Analyze the uploaded file
            $analysis = $this->importAction->analyzeFile($this->importFile);

            if (! $analysis['success']) {
                $this->addCustomError('importFile', $analysis['error']);

                return;
            }

            $this->fileAnalysis = $analysis;
            $this->headers = $analysis['headers'];
            $this->sampleRows = $analysis['sample_rows'];

            // Auto-suggest field mappings
            $this->applySuggestedMappings($analysis['suggested_mappings']);

            $this->currentStep = 'mapping';
            $this->clearErrors();

        } catch (Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            $this->addCustomError('importFile', 'File upload failed: ' . $e->getMessage());
        }
    }

    protected function applySuggestedMappings(array $suggestions): void
    {
        $this->fieldMapping = [];

        foreach ($this->headers as $header) {
            $suggestion = $suggestions[$header] ?? null;
            if ($suggestion && $suggestion['confidence'] > 80) {
                $this->fieldMapping[$header] = $suggestion['suggested_field'];
            } else {
                $this->fieldMapping[$header] = '';
            }
        }
    }

    public function validateMapping(): void
    {
        try {
            // Check that required fields are mapped
            $requiredFields = array_filter($this->supportedFields, fn ($field) => $field['required']);
            $mappedFields = array_filter($this->fieldMapping);

            $missingFields = [];
            foreach ($requiredFields as $fieldKey => $fieldConfig) {
                if (! in_array($fieldKey, $mappedFields)) {
                    $missingFields[] = $fieldConfig['label'];
                }
            }

            if (! empty($missingFields)) {
                $this->addCustomError('fieldMapping', 'Required fields must be mapped: ' . implode(', ', $missingFields));

                return;
            }

            // Check for duplicate mappings
            $mappedValues = array_filter($this->fieldMapping);
            if (count($mappedValues) !== count(array_unique($mappedValues))) {
                $this->addCustomError('fieldMapping', 'Each field can only be mapped to one column.');

                return;
            }

            // Show loading state
            $this->dispatch('validation-started');

            // Validate the entire file
            $validationResult = $this->importAction->validateMapping($this->importFile, $this->fieldMapping);

            // Store validation results
            $this->validationResults = [
                'total_rows' => $validationResult->totalRows,
                'valid_rows' => $validationResult->validRows,
                'error_rows' => $validationResult->errorRows,
                'warning_rows' => $validationResult->warningRows,
                'validation_time' => $validationResult->validationTime,
                'sample_valid_records' => $validationResult->sampleValidRecords,
                'sample_error_records' => $validationResult->sampleErrorRecords,
            ];

            $this->validationErrors = $validationResult->errors;
            $this->validationWarnings = $validationResult->warnings;

            $this->currentStep = 'validation';
            $this->clearErrors();

            // Dispatch completion event
            $this->dispatch('validation-completed', [
                'summary' => $validationResult->getValidationSummary(),
            ]);

        } catch (Exception $e) {
            Log::error('Mapping validation error: ' . $e->getMessage());
            $this->addCustomError('fieldMapping', 'Validation failed: ' . $e->getMessage());
            $this->dispatch('validation-failed');
        }
    }

    public function executeImport(): void
    {
        try {
            $this->importInProgress = true;
            $this->progressPercentage = 0;
            $this->currentStep = 'import';

            // Store the file temporarily
            $filePath = $this->importFile->store('imports', 'local');

            // Always ensure main federation is included
            if ($this->mainFederationId && ! in_array($this->mainFederationId, $this->selectedFederations)) {
                $this->selectedFederations[] = $this->mainFederationId;
            }

            $options = [
                'federation_ids' => $this->selectedFederations,
                'main_federation_id' => $this->mainFederationId,
                'entity_id' => $this->entityId,
                'duplicate_strategy' => $this->duplicateStrategy,
                'send_notifications' => $this->sendNotifications,
                'auto_assign_federation' => $this->autoAssignFederation,
                'skip_invalid_rows' => $this->getInvalidRowsCount() > 0,
            ];

            // Create import record
            $import = \Domain\Imports\Models\Import::create([
                'user_id' => auth()->id(),
                'type' => 'individual',
                'filename' => $this->fileName,
                'file_path' => $filePath,
                'status' => 'pending',
                'field_mapping' => array_filter($this->fieldMapping),
                'options' => $options,
                'total_rows' => $this->getTotalRowsCount(),
                'valid_rows' => $this->getValidRowsCount(),
                'error_rows' => $this->getInvalidRowsCount(),
            ]);

            $this->importId = $import->id;
            $this->importStatus = 'pending';

            // Run import synchronously to avoid queue issues
            \App\Jobs\ProcessIndividualImportJob::dispatchSync($import->id);

            // Check final status after sync execution
            $import->refresh();
            if ($import->status === 'completed') {
                $this->importInProgress = false;
                $this->currentStep = 'completed';
                $this->importResults = [
                    'total_rows' => $import->total_rows,
                    'processed_rows' => $import->processed_rows,
                    'success_count' => $import->success_count,
                    'error_count' => $import->error_count,
                ];
            } elseif ($import->status === 'failed') {
                $this->importInProgress = false;
                $this->importErrorMessage = $import->error_message;
                $this->addCustomError('import', $import->error_message ?? __('import.import_failed'));
            }

        } catch (Exception $e) {
            Log::error('Import execution error: ' . $e->getMessage());
            $this->importInProgress = false;
            $this->addCustomError('import', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate(): void
    {
        $this->redirect(route('admin.individual.import.template'));
    }

    public function downloadErrorReport(): void
    {
        if (! empty($this->validationErrors) || ! empty($this->importResults['errors'] ?? [])) {
            $this->redirect(route('admin.individual.import.error-report'));
        }
    }

    public function resetWizard(): void
    {
        $this->currentStep = 'upload';
        $this->importFile = null;
        $this->fileName = '';
        $this->fileAnalysis = [];
        $this->headers = [];
        $this->sampleRows = [];
        $this->initializeFieldMapping();
        $this->validationResults = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
        $this->importResults = [];
        $this->importInProgress = false;
        $this->progressPercentage = 0;
        $this->importId = null;
        $this->importStatus = null;
        $this->importErrorMessage = null;
        $this->clearErrors();
    }

    public function goToStep(string $step): void
    {
        if (in_array($step, $this->steps)) {
            $this->currentStep = $step;
        }
    }

    public function previousStep(): void
    {
        $currentIndex = array_search($this->currentStep, $this->steps);
        if ($currentIndex > 0) {
            $this->currentStep = $this->steps[$currentIndex - 1];
        }
    }

    public function nextStep(): void
    {
        switch ($this->currentStep) {
            case 'upload':
                $this->uploadFile();
                break;
            case 'mapping':
                $this->validateMapping();
                break;
            case 'validation':
                $this->executeImport();
                break;
        }
    }

    public function toggleAdvancedOptions(): void
    {
        $this->showAdvancedOptions = ! $this->showAdvancedOptions;
    }

    public function getStepNumber(): int
    {
        return array_search($this->currentStep, $this->steps) + 1;
    }

    public function getTotalSteps(): int
    {
        return count($this->steps) - 1; // Exclude 'completed' step from count
    }

    public function isStepCompleted(string $step): bool
    {
        $currentIndex = array_search($this->currentStep, $this->steps);
        $stepIndex = array_search($step, $this->steps);

        return $stepIndex < $currentIndex;
    }

    public function isStepAccessible(string $step): bool
    {
        $currentIndex = array_search($this->currentStep, $this->steps);
        $stepIndex = array_search($step, $this->steps);

        return $stepIndex <= $currentIndex + 1;
    }

    public function clearErrors(): void
    {
        $this->errors = [];
        $this->resetErrorBag();
    }

    public function addCustomError(string $key, string $message): void
    {
        $this->errors[$key] = $message;
    }

    public function getValidRowsCount(): int
    {
        return $this->validationResults['valid_rows'] ?? 0;
    }

    public function getInvalidRowsCount(): int
    {
        return $this->validationResults['error_rows'] ?? 0;
    }

    public function getWarningRowsCount(): int
    {
        return $this->validationResults['warning_rows'] ?? 0;
    }

    public function getTotalRowsCount(): int
    {
        return $this->validationResults['total_rows'] ?? 0;
    }

    public function updateImportProgress(): void
    {
        if (! $this->importId || ! $this->importInProgress) {
            return;
        }

        try {
            $import = \Domain\Imports\Models\Import::where('user_id', auth()->id())
                ->find($this->importId);

            if (! $import) {
                $this->importInProgress = false;

                return;
            }

            $this->importStatus = $import->status;
            $this->progressPercentage = $import->progress_percentage;

            // Update import results
            $this->importResults = [
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'success_count' => $import->success_count,
                'error_count' => $import->error_count,
                'warning_count' => $import->warning_count,
                'processing_rate' => $import->processing_rate,
                'estimated_time_remaining' => $import->estimated_time_remaining,
            ];

            // Check if import is complete
            if (in_array($import->status, ['completed', 'failed', 'cancelled'])) {
                $this->importInProgress = false;

                if ($import->status === 'completed') {
                    $this->currentStep = 'completed';
                    session()->flash('success', __('import.import_completed_successfully'));
                } elseif ($import->status === 'failed') {
                    $this->importErrorMessage = $import->error_message;
                    $errorMsg = $import->error_message
                        ? __('import.import_failed') . ': ' . $import->error_message
                        : __('import.import_failed');
                    $this->addCustomError('import', $errorMsg);
                }

                // Stop polling
                $this->dispatch('stop-import-polling');
            }

        } catch (Exception $e) {
            Log::error('Import progress update error: ' . $e->getMessage());
            $this->importInProgress = false;
            $this->dispatch('stop-import-polling');
        }
    }

    public function cancelImport(): void
    {
        if (! $this->importId) {
            return;
        }

        try {
            $import = \Domain\Imports\Models\Import::where('user_id', auth()->id())
                ->find($this->importId);

            if ($import && $import->canBeCancelled()) {
                $import->markAsCancelled();
                $this->importInProgress = false;
                $this->importStatus = 'cancelled';
                $this->dispatch('stop-import-polling');
                session()->flash('info', __('import.import_cancelled'));

                // Reset the wizard to start fresh
                $this->resetWizard();
            }

        } catch (Exception $e) {
            Log::error('Import cancellation error: ' . $e->getMessage());
            $this->addCustomError('import', 'Failed to cancel import');
        }
    }
}
