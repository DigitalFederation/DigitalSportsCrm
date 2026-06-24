<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\EntityImport;
use Domain\Entities\Actions\EntityImportAction;
use Domain\Imports\Actions\ProcessImportFileAction;
use Domain\Imports\Actions\ValidateEntityImportFileAction;
use Domain\Imports\Models\Import;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use League\Csv\Reader;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntityImportController extends Controller
{
    /**
     * The allowed directory prefix for import files.
     */
    private const ALLOWED_IMPORT_PATH_PREFIX = 'imports/entities/';

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|federation-admin|association-sport-admin|association-scientific-admin|association-admin|association-territorial-admin');
    }

    /**
     * Validate that the file path is within the allowed import directory.
     */
    private function validateImportFilePath(?string $filePath): bool
    {
        if (empty($filePath)) {
            return false;
        }

        // Reject any path traversal sequences
        if (str_contains($filePath, '..') || str_contains($filePath, "\0")) {
            return false;
        }

        // Normalize and check the path starts with the allowed prefix
        $normalizedPath = ltrim($filePath, '/\\');

        return str_starts_with($normalizedPath, self::ALLOWED_IMPORT_PATH_PREFIX);
    }

    /**
     * Display the entity import wizard page.
     */
    public function index(): View
    {
        return view('web.admin.entity.import.index');
    }

    /**
     * Handle file upload and return initial analysis.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xls,xlsx|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');

            // Store the file temporarily
            $path = $file->store('imports/entities', 'local');

            // Analyze the file
            $entityImportAction = new EntityImportAction;
            $analysis = $entityImportAction->analyzeFile($file);

            if (! $analysis['success']) {
                Storage::disk('local')->delete($path);

                return response()->json([
                    'success' => false,
                    'error' => $analysis['error'] ?? 'Failed to analyze file',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'file_path' => $path,
                'filename' => $file->getClientOriginalName(),
                'headers' => $analysis['headers'],
                'sample_rows' => $analysis['sample_rows'],
                'row_count' => $analysis['row_count'],
                'suggested_mappings' => $analysis['suggested_mappings'],
                'data_analysis' => $analysis['data_analysis'],
                'supported_fields' => EntityImport::getSupportedFields(),
            ]);

        } catch (\Exception $e) {
            Log::error('Entity import file upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return file analysis for already uploaded file.
     */
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $filePath = $request->input('file_path');

            if (! $this->validateImportFilePath($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file path',
                ], 403);
            }

            if (! Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found',
                ], 404);
            }

            $fullPath = Storage::disk('local')->path($filePath);
            $file = new \Illuminate\Http\UploadedFile($fullPath, basename($filePath));

            $entityImportAction = new EntityImportAction;
            $analysis = $entityImportAction->analyzeFile($file);

            return response()->json([
                'success' => $analysis['success'],
                'headers' => $analysis['headers'] ?? [],
                'sample_rows' => $analysis['sample_rows'] ?? [],
                'row_count' => $analysis['row_count'] ?? 0,
                'suggested_mappings' => $analysis['suggested_mappings'] ?? [],
                'data_analysis' => $analysis['data_analysis'] ?? [],
                'supported_fields' => EntityImport::getSupportedFields(),
            ]);

        } catch (\Exception $e) {
            Log::error('Entity import file analysis failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate field mapping and return preview.
     */
    public function validateMapping(Request $request): JsonResponse
    {
        $request->validate([
            'file_path' => 'required|string',
            'field_mapping' => 'required|array',
        ]);

        try {
            $filePath = $request->input('file_path');

            if (! $this->validateImportFilePath($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file path',
                ], 403);
            }

            if (! Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found',
                ], 404);
            }

            $fieldMapping = $request->input('field_mapping');

            // Check required fields are mapped
            $requiredFields = ['name', 'country_id'];
            $mappedFields = array_values(array_filter($fieldMapping));
            $missingRequired = array_diff($requiredFields, $mappedFields);

            if (! empty($missingRequired)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Missing required field mappings: ' . implode(', ', $missingRequired),
                ], 422);
            }

            $fullPath = Storage::disk('local')->path($filePath);
            $file = new \Illuminate\Http\UploadedFile($fullPath, basename($filePath));

            // Validate entire file
            $validateAction = app(ValidateEntityImportFileAction::class);
            $validationResult = $validateAction->execute($file, $fieldMapping);

            return response()->json([
                'success' => true,
                'validation_results' => $validationResult->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Entity import validation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview import data.
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'file_path' => 'required|string',
            'field_mapping' => 'required|array',
        ]);

        try {
            $filePath = $request->input('file_path');

            if (! $this->validateImportFilePath($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file path',
                ], 403);
            }

            if (! Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found',
                ], 404);
            }

            $fullPath = Storage::disk('local')->path($filePath);
            $fieldMapping = $request->input('field_mapping');

            // Read a sample of rows for preview
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (strtolower($extension) === 'csv') {
                $data = $this->readCSVSample($fullPath);
            } else {
                $data = Excel::toArray(new class {}, $fullPath)[0];
            }

            $headers = array_shift($data);
            $sampleData = array_slice($data, 0, 10);

            // Map sample data according to field mapping
            $mappedSample = [];
            foreach ($sampleData as $row) {
                $mappedRow = [];
                foreach ($fieldMapping as $csvColumn => $systemField) {
                    if (! empty($systemField)) {
                        $columnIndex = array_search($csvColumn, $headers);
                        if ($columnIndex !== false && isset($row[$columnIndex])) {
                            $mappedRow[$systemField] = $row[$columnIndex];
                        }
                    }
                }
                $mappedSample[] = $mappedRow;
            }

            return response()->json([
                'success' => true,
                'preview_data' => $mappedSample,
                'total_rows' => count($data),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start the import process.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file_path' => 'required|string',
            'field_mapping' => 'required|array',
            'options' => 'nullable|array',
        ]);

        try {
            $filePath = $request->input('file_path');

            if (! $this->validateImportFilePath($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file path',
                ], 403);
            }

            if (! Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found',
                ], 404);
            }

            $fieldMapping = $request->input('field_mapping');
            $options = $request->input('options', []);

            // Create import record
            $import = Import::create([
                'filename' => basename($filePath),
                'file_path' => $filePath,
                'import_type' => 'entity',
                'field_mapping' => $fieldMapping,
                'options' => $options,
                'status' => 'processing',
                'total_rows' => 0,
                'processed_rows' => 0,
                'success_count' => 0,
                'error_count' => 0,
                'user_id' => auth()->id(),
            ]);

            // Process the import file
            $processAction = app(ProcessImportFileAction::class);
            $processAction->execute($import);

            return response()->json([
                'success' => true,
                'import_id' => $import->id,
                'message' => 'Import started',
            ]);

        } catch (\Exception $e) {
            Log::error('Entity import failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get import progress.
     */
    public function progress(Request $request): JsonResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
        ]);

        try {
            $import = Import::find($request->input('import_id'));

            if (! $import) {
                return response()->json([
                    'success' => false,
                    'error' => 'Import not found',
                ], 404);
            }

            $progress = 0;
            if ($import->total_rows > 0) {
                $progress = round(($import->processed_rows / $import->total_rows) * 100);
            }

            return response()->json([
                'success' => true,
                'status' => $import->status,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'success_count' => $import->success_count,
                'error_count' => $import->error_count,
                'progress_percentage' => $progress,
                'is_completed' => in_array($import->status, ['completed', 'failed', 'cancelled']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download template file.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $entityImportAction = new EntityImportAction;
        $template = $entityImportAction->generateTemplate();

        $headers = $template['headers'];
        $sampleData = $template['sample_data'];

        return response()->streamDownload(function () use ($headers, $sampleData) {
            $csv = Writer::createFromString('');
            $csv->insertOne($headers);
            $csv->insertOne($sampleData);
            echo $csv->toString();
        }, 'entity_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Download error report.
     */
    public function downloadErrors(Request $request): StreamedResponse
    {
        $request->validate([
            'import_id' => 'required|integer',
        ]);

        $import = Import::with('errorMessages')->findOrFail($request->input('import_id'));

        return response()->streamDownload(function () use ($import) {
            $csv = Writer::createFromString('');
            $csv->insertOne(['Row Number', 'Error Message', 'Severity', 'Row Data']);

            foreach ($import->errorMessages as $error) {
                $csv->insertOne([
                    $error->row_number,
                    $error->error_message,
                    $error->severity,
                    json_encode($error->row_data),
                ]);
            }

            echo $csv->toString();
        }, 'entity_import_errors_' . $import->id . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Read CSV sample with delimiter detection.
     */
    protected function readCSVSample(string $filePath, int $limit = 20): array
    {
        $delimiter = $this->detectCSVDelimiter($filePath);

        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setDelimiter($delimiter);

        $data = [];
        $count = 0;
        foreach ($reader as $row) {
            $data[] = $row;
            $count++;
            if ($count >= $limit) {
                break;
            }
        }

        return $data;
    }

    /**
     * Detect CSV delimiter.
     */
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
}
