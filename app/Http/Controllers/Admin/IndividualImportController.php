<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndividualImportRequest;
use App\Imports\IndividualImport;
use App\Jobs\ProcessIndividualImportJob;
use Domain\Federations\Models\Federation;
use Domain\Imports\Models\Import;
use Domain\Individuals\Actions\IndividualImportAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class IndividualImportController extends Controller
{
    protected IndividualImportAction $importAction;

    public function __construct(IndividualImportAction $importAction)
    {
        $this->importAction = $importAction;
    }

    /**
     * Display the import wizard interface.
     */
    public function index(): View
    {
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $supportedFields = IndividualImport::getSupportedFields();

        return view('web.admin.individual.import.index', compact('federations', 'supportedFields'));
    }

    /**
     * Handle file upload and analysis.
     */
    public function upload(IndividualImportRequest $request): JsonResponse
    {
        try {
            if (! $request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No file uploaded',
                ], 400);
            }

            $file = $request->file('file');

            // Store file temporarily
            $filePath = $file->store('imports', 'local');
            Session::put('import_file_path', $filePath);
            Session::put('import_file_name', $file->getClientOriginalName());

            // Analyze file
            $analysis = $this->importAction->analyzeFile($file);

            if (! $analysis['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $analysis['error'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $analysis,
                'next_step' => 'mapping',
            ]);

        } catch (Exception $e) {
            Log::error('Import upload failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate field mapping and show preview.
     */
    public function validateMapping(IndividualImportRequest $request): JsonResponse
    {
        try {
            $filePath = Session::get('import_file_path');
            if (! $filePath || ! file_exists(storage_path('app/' . $filePath))) {
                return response()->json([
                    'success' => false,
                    'error' => 'Import file not found. Please upload the file again.',
                ], 400);
            }

            // Create a temporary uploaded file instance
            $file = new \Illuminate\Http\UploadedFile(
                storage_path('app/' . $filePath),
                Session::get('import_file_name'),
                null,
                null,
                true
            );

            $fieldMapping = $request->getFieldMapping();

            // Validate mapping
            $validation = $this->importAction->validateMapping($file, $fieldMapping);

            if (! $validation['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $validation['error'],
                ], 400);
            }

            // Store mapping in session
            Session::put('import_field_mapping', $fieldMapping);

            return response()->json([
                'success' => true,
                'data' => $validation,
                'next_step' => $validation['has_errors'] ? 'mapping' : 'preview',
            ]);

        } catch (Exception $e) {
            Log::error('Import mapping validation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute the import process.
     */
    public function import(IndividualImportRequest $request): JsonResponse
    {
        try {
            $filePath = Session::get('import_file_path');
            $fieldMapping = Session::get('import_field_mapping');

            if (! $filePath || ! $fieldMapping) {
                return response()->json([
                    'success' => false,
                    'error' => 'Import session expired. Please start over.',
                ], 400);
            }

            if (! Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Import file not found. Please upload the file again.',
                ], 400);
            }

            $options = $request->getImportOptions();

            // Create import record
            $import = Import::create([
                'user_id' => Auth::id(),
                'type' => 'individual',
                'filename' => Session::get('import_file_name'),
                'file_path' => $filePath,
                'status' => 'pending',
                'field_mapping' => $fieldMapping,
                'options' => $options,
            ]);

            // Clean up session
            Session::forget(['import_file_path', 'import_file_name', 'import_field_mapping']);

            // Dispatch job to process import
            ProcessIndividualImportJob::dispatch($import->id);

            return response()->json([
                'success' => true,
                'import_id' => $import->id,
                'message' => 'Import has been queued for processing.',
            ]);

        } catch (Exception $e) {
            Log::error('Import execution failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download import template.
     */
    public function downloadTemplate(): Response
    {
        try {
            $template = $this->importAction->generateTemplate();

            // Create CSV content
            $csvContent = [];
            $csvContent[] = $template['headers'];
            $csvContent[] = $template['sample_data'];

            // Add additional sample rows
            $csvContent[] = [
                'Sample', 'Member', 'sample.member@example.test', '1985-08-22', 'Example Country',
                'female', 'Sample Member', 'Example Street 2', 'Example City', '0000-001',
                'ID Card', 'EXAMPLE-DOC-002', '2028-06-15', 'https://social.example.test/sample-member',
                '', 'https://social.example.test/sample-member', '',
            ];

            $csvContent[] = [
                'Demo', 'Participant', 'demo.participant@example.test', '1992-12-03', 'Example Country',
                'male', '', 'Example Avenue 3', 'Example City', '0000-002',
                'ID Card', 'EXAMPLE-DOC-003', '2029-03-20', '',
                'https://social.example.test/demo-participant', '', 'https://social.example.test/demo-participant',
            ];

            // Generate CSV
            $output = fopen('php://temp', 'r+');
            foreach ($csvContent as $row) {
                fputcsv($output, $row);
            }
            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="individual_import_template.csv"');

        } catch (Exception $e) {
            Log::error('Template download failed: ' . $e->getMessage());

            return response('Template generation failed', 500);
        }
    }

    /**
     * Download error report from last import.
     */
    public function downloadErrorReport(): JsonResponse
    {
        try {
            $errors = Session::get('import_errors', []);
            $warnings = Session::get('import_warnings', []);

            if (empty($errors) && empty($warnings)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No errors or warnings to download',
                ], 400);
            }

            $report = [
                'Import Error Report',
                'Generated: ' . now()->format('Y-m-d H:i:s'),
                '',
                'ERRORS:',
                '-------',
            ];

            foreach ($errors as $rowNumber => $rowErrors) {
                $report[] = "Row {$rowNumber}: " . implode('; ', $rowErrors);
            }

            if (! empty($warnings)) {
                $report[] = '';
                $report[] = 'WARNINGS:';
                $report[] = '---------';

                foreach ($warnings as $rowNumber => $rowWarnings) {
                    $report[] = "Row {$rowNumber}: " . implode('; ', $rowWarnings);
                }
            }

            $content = implode("\n", $report);

            return response()->json([
                'success' => true,
                'content' => $content,
                'filename' => 'import_errors_' . now()->format('Y-m-d_H-i-s') . '.txt',
            ]);

        } catch (Exception $e) {
            Log::error('Error report download failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error report generation failed',
            ], 500);
        }
    }

    /**
     * Get import progress (for AJAX updates).
     */
    public function getProgress($importId): JsonResponse
    {
        try {
            $import = Import::where('user_id', Auth::id())
                ->findOrFail($importId);

            $errors = $import->errorMessages()
                ->latest()
                ->limit(10)
                ->get(['row_number', 'error_message', 'created_at']);

            $warnings = $import->warnings()
                ->latest()
                ->limit(10)
                ->get(['row_number', 'error_message', 'created_at']);

            return response()->json([
                'success' => true,
                'status' => $import->status,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'percentage' => $import->progress_percentage,
                'success_count' => $import->success_count,
                'error_count' => $import->error_count,
                'warning_count' => $import->warning_count,
                'processing_rate' => $import->processing_rate,
                'estimated_time_remaining' => $import->estimated_time_remaining,
                'errors' => $errors,
                'warnings' => $warnings,
                'started_at' => $import->started_at?->toISOString(),
                'completed_at' => $import->completed_at?->toISOString(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Import not found or access denied.',
            ], 404);
        }
    }

    /**
     * Cancel ongoing import.
     */
    public function cancel($importId): JsonResponse
    {
        try {
            $import = Import::where('user_id', Auth::id())
                ->findOrFail($importId);

            if (! $import->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This import cannot be cancelled.',
                ], 400);
            }

            $import->markAsCancelled();

            // Clean up session
            Session::forget(['import_file_path', 'import_file_name', 'import_field_mapping']);

            return response()->json([
                'success' => true,
                'message' => 'Import cancelled successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel import',
            ], 500);
        }
    }
}
