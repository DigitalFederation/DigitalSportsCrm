<?php

namespace App\Livewire\Admin;

use App\Imports\EntityImport;
use App\Jobs\ProcessEntityImportJob;
use Domain\Federations\Models\Federation;
use Domain\Imports\Actions\ProcessImportFileAction;
use Domain\Imports\Actions\ValidateEntityImportFileAction;
use Domain\Imports\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class EntityImportWizard extends Component
{
    use WithFileUploads;

    // Current wizard step
    public string $currentStep = 'upload';

    // File upload
    public $importFile;

    public ?string $fileName = null;

    public ?string $filePath = null;

    // File analysis
    public array $headers = [];

    public array $sampleRows = [];

    public array $fileAnalysis = [];

    // Field mapping
    public array $fieldMapping = [];

    public array $supportedFields = [];

    // Validation results
    public array $validationResults = [];

    public array $validationErrors = [];

    public array $validationWarnings = [];

    // Import options
    public array $selectedFederations = [];

    public string $duplicateStrategy = 'skip';

    public bool $showAdvancedOptions = false;

    public array $duplicateStrategies = [];

    // Import progress
    public bool $importInProgress = false;

    public int $progressPercentage = 0;

    public array $importResults = [];

    public ?int $currentImportId = null;

    // Error handling
    public array $errors = [];

    protected $listeners = ['updateImportProgress'];

    public function mount(): void
    {
        $this->supportedFields = EntityImport::getSupportedFields();
        $this->duplicateStrategies = [
            'skip' => __('import.duplicate_skip'),
            'update' => __('import.duplicate_update'),
            'create_with_suffix' => __('import.duplicate_create_suffix'),
            'manual_review' => __('import.duplicate_manual_review'),
        ];

        // Set default federation (main federation)
        $mainFederation = Federation::where('is_default_federation', 1)->first();
        if ($mainFederation) {
            $this->selectedFederations = [$mainFederation->id];
        }
    }

    public function updatedImportFile(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,xls,xlsx|max:10240',
        ]);

        if ($this->importFile) {
            $this->fileName = $this->importFile->getClientOriginalName();

            // Store the file temporarily
            $path = $this->importFile->store('imports/entities', 'local');
            $this->filePath = $path;

            // Analyze the file
            $this->analyzeFile();
        }
    }

    protected function analyzeFile(): void
    {
        if (! $this->filePath) {
            return;
        }

        try {
            $fullPath = Storage::disk('local')->path($this->filePath);
            $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);

            if (strtolower($extension) === 'csv') {
                $data = $this->readCSVWithDelimiterDetection($fullPath);
            } else {
                $data = Excel::toArray(new class {}, $fullPath)[0];
            }

            if (empty($data)) {
                $this->errors['importFile'] = 'File is empty or could not be read';

                return;
            }

            $this->headers = array_shift($data);
            $this->sampleRows = array_slice($data, 0, 5);
            $this->fileAnalysis = [
                'row_count' => count($data),
                'column_count' => count($this->headers),
            ];

            // Auto-suggest field mappings
            $this->suggestFieldMappings();

        } catch (\Exception $e) {
            Log::error('File analysis failed: ' . $e->getMessage());
            $this->errors['importFile'] = 'Failed to analyze file: ' . $e->getMessage();
        }
    }

    protected function readCSVWithDelimiterDetection(string $filePath): array
    {
        $delimiter = $this->detectCSVDelimiter($filePath);

        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setDelimiter($delimiter);

        $data = [];
        foreach ($reader as $row) {
            $data[] = $row;
        }

        return $data;
    }

    protected function detectCSVDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
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

    protected function suggestFieldMappings(): void
    {
        foreach ($this->headers as $header) {
            $normalizedHeader = strtolower(trim($header));
            $bestMatch = null;
            $highestScore = 0;

            foreach ($this->supportedFields as $fieldKey => $fieldConfig) {
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

            $this->fieldMapping[$header] = $highestScore >= 70 ? $bestMatch : '';
        }
    }

    public function nextStep(): void
    {
        switch ($this->currentStep) {
            case 'upload':
                if ($this->fileName && $this->filePath) {
                    $this->currentStep = 'mapping';
                } else {
                    $this->errors['importFile'] = __('import.file_required');
                }
                break;

            case 'mapping':
                if ($this->validateFieldMapping()) {
                    $this->validateData();
                    $this->currentStep = 'validation';
                }
                break;

            case 'validation':
                $this->executeImport();
                $this->currentStep = 'import';
                break;
        }
    }

    public function previousStep(): void
    {
        switch ($this->currentStep) {
            case 'mapping':
                $this->currentStep = 'upload';
                break;

            case 'validation':
                $this->currentStep = 'mapping';
                break;
        }
    }

    protected function validateFieldMapping(): bool
    {
        $mappedFields = array_values(array_filter($this->fieldMapping));
        $requiredFields = ['name'];

        $missingRequired = array_diff($requiredFields, $mappedFields);

        if (! empty($missingRequired)) {
            $this->errors['fieldMapping'] = 'Missing required field mappings: ' . implode(', ', $missingRequired);

            return false;
        }

        return true;
    }

    protected function validateData(): void
    {
        try {
            $fullPath = Storage::disk('local')->path($this->filePath);
            $file = new UploadedFile($fullPath, basename($this->filePath));

            $validateAction = app(ValidateEntityImportFileAction::class);
            $result = $validateAction->execute($file, $this->fieldMapping);

            $this->validationResults = $result->toArray();
            $this->validationErrors = $result->errors;
            $this->validationWarnings = $result->warnings;

        } catch (\Exception $e) {
            Log::error('Validation failed: ' . $e->getMessage());
            $this->errors['validation'] = 'Validation failed: ' . $e->getMessage();
        }
    }

    protected function executeImport(): void
    {
        try {
            $this->importInProgress = true;
            $this->progressPercentage = 0;

            // Create import record
            $import = Import::create([
                'filename' => $this->fileName,
                'file_path' => $this->filePath,
                'import_type' => 'entity',
                'field_mapping' => $this->fieldMapping,
                'options' => [
                    'federation_ids' => $this->selectedFederations,
                    'duplicate_strategy' => $this->duplicateStrategy,
                ],
                'status' => 'processing',
                'total_rows' => $this->fileAnalysis['row_count'] ?? 0,
                'processed_rows' => 0,
                'success_count' => 0,
                'error_count' => 0,
                'user_id' => auth()->id(),
            ]);

            $this->currentImportId = $import->id;

            // Dispatch the import processing using the existing infrastructure
            // For entity imports, we modify the ProcessImportFileAction to dispatch entity-specific jobs
            $this->processEntityImport($import);

            // Start polling for progress updates
            $this->dispatch('start-import-polling', importId: $import->id);

        } catch (\Exception $e) {
            Log::error('Import execution failed: ' . $e->getMessage());
            $this->errors['import'] = 'Import failed: ' . $e->getMessage();
            $this->importInProgress = false;
        }
    }

    protected function processEntityImport(Import $import): void
    {
        $fullPath = Storage::disk('local')->path($import->file_path);
        $extension = pathinfo($import->filename, PATHINFO_EXTENSION);
        $chunkSize = 1000;

        if (strtolower($extension) === 'csv') {
            $data = $this->readCSVWithDelimiterDetection($fullPath);
        } else {
            $data = Excel::toArray(new class {}, $fullPath)[0];
        }

        // Remove headers
        $headers = array_shift($data);
        $totalRows = count($data);
        $import->update(['total_rows' => $totalRows]);

        // Process in chunks
        $chunks = array_chunk($data, $chunkSize);
        foreach ($chunks as $chunkIndex => $chunkData) {
            // Convert rows to associative arrays with headers
            $processedChunk = [];
            foreach ($chunkData as $row) {
                if (count($row) === count($headers)) {
                    $processedChunk[] = array_combine($headers, $row);
                }
            }

            if (! empty($processedChunk)) {
                ProcessEntityImportJob::dispatch($import->id, $processedChunk, $chunkIndex)
                    ->delay(now()->addSeconds($chunkIndex * 2));
            }
        }
    }

    public function updateImportProgress(): void
    {
        if (! $this->currentImportId) {
            return;
        }

        $import = Import::find($this->currentImportId);
        if (! $import) {
            return;
        }

        if ($import->total_rows > 0) {
            $this->progressPercentage = round(($import->processed_rows / $import->total_rows) * 100);
        }

        $this->importResults = [
            'total_rows' => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'success_count' => $import->success_count,
            'error_count' => $import->error_count,
        ];

        if (in_array($import->status, ['completed', 'failed', 'cancelled'])) {
            $this->importInProgress = false;
            $this->currentStep = 'completed';
            $this->dispatch('stop-import-polling');
        }
    }

    public function cancelImport(): void
    {
        if ($this->currentImportId) {
            $import = Import::find($this->currentImportId);
            if ($import) {
                $import->update(['status' => 'cancelled']);
            }
        }

        $this->importInProgress = false;
        $this->dispatch('stop-import-polling');
        session()->flash('message', __('import.import_cancelled'));
    }

    public function toggleAdvancedOptions(): void
    {
        $this->showAdvancedOptions = ! $this->showAdvancedOptions;
    }

    public function resetWizard(): void
    {
        // Delete temporary file
        if ($this->filePath && Storage::disk('local')->exists($this->filePath)) {
            Storage::disk('local')->delete($this->filePath);
        }

        // Reset all properties
        $this->currentStep = 'upload';
        $this->importFile = null;
        $this->fileName = null;
        $this->filePath = null;
        $this->headers = [];
        $this->sampleRows = [];
        $this->fileAnalysis = [];
        $this->fieldMapping = [];
        $this->validationResults = [];
        $this->validationErrors = [];
        $this->validationWarnings = [];
        $this->importInProgress = false;
        $this->progressPercentage = 0;
        $this->importResults = [];
        $this->currentImportId = null;
        $this->errors = [];
    }

    public function isStepCompleted(string $step): bool
    {
        $stepOrder = ['upload', 'mapping', 'validation', 'import', 'completed'];
        $currentIndex = array_search($this->currentStep, $stepOrder);
        $stepIndex = array_search($step, $stepOrder);

        return $stepIndex < $currentIndex;
    }

    #[Computed]
    public function federations()
    {
        return Federation::orderBy('name')->get();
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
        return $this->validationResults['total_rows'] ?? ($this->fileAnalysis['row_count'] ?? 0);
    }

    public function render()
    {
        return view('livewire.admin.entity-import-wizard');
    }
}
