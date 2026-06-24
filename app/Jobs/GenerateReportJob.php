<?php

namespace App\Jobs;

use App\Reports\ReportTemplate;
use App\Services\ReportGeneratorService;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportClass;
    protected $filters;
    protected $reportId;

    public function __construct($reportClass, $filters, $reportId)
    {
        $this->reportClass = $reportClass;
        $this->filters = $filters;
        $this->reportId = $reportId;
    }

    public function handle(ReportGeneratorService $reportGeneratorService)
    {
        Log::info('Starting report generation', [
            'reportClass' => $this->reportClass,
            'reportId' => $this->reportId,
            'queue' => $this->queue,
            'connection' => $this->connection,
        ]);

        try {
            // Verify report class exists and implements the interface
            if (! class_exists($this->reportClass) || ! in_array(ReportTemplate::class, class_implements($this->reportClass))) {
                throw new \Exception("Invalid report class: {$this->reportClass}");
            }

            $reportInstance = new $this->reportClass;
            Log::info('Report instance created', ['reportClass' => get_class($reportInstance)]);

            // Add memory usage logging
            Log::debug('Memory usage before generation', ['memory' => memory_get_usage(true)]);

            // Generate and store the report
            $filePath = $reportGeneratorService->generateAndStore($reportInstance, $this->filters, $reportInstance->getDisplayName());

            Log::info('Report generated and stored', ['filePath' => $filePath]);

            // Get the report record
            $report = GeneratedReport::find($this->reportId);

            // Update the report record
            $report->update([
                'file_path' => $filePath,
                'generated_on' => now(),
                'status' => 'ready',
            ]);

            // Set file size after the file is confirmed to be stored
            if (File::exists(storage_path('app/' . $filePath))) {
                $report->setFilePath($filePath);
            } else {
                Log::warning('File not found after generation', [
                    'file_path' => $filePath,
                    'full_path' => storage_path('app/' . $filePath),
                ]);
            }

            Log::info('Report record updated', ['reportId' => $this->reportId]);
        } catch (\Exception $e) {
            Log::error('Error generating report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reportId' => $this->reportId,
            ]);

            // Update the report status to 'failed'
            GeneratedReport::where('id', $this->reportId)->update([
                'status' => 'failed',
            ]);

            throw $e; // Re-throw to mark job as failed
        }
    }
}
