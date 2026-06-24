<?php

namespace App\Services;

use App\Exports\CollectionExport;
use App\Reports\ReportTemplate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportGeneratorService
{
    public function generateAndDownload(ReportTemplate $report, $filters, string $file_name)
    {
        $data = $report->query($filters)->get();
        $processedData = $report->processData($data); // Process the data
        $columns = $report->columns();

        return Excel::download(new CollectionExport($processedData, $columns), "{$file_name}.xlsx");
    }

    public function generateAndStore(ReportTemplate $report, $filters, string $file_name)
    {

        try {
            $data = $report->query($filters)->get();
            Log::info('Query executed', ['rowCount' => $data->count()]);

            $processedData = $report->processData($data);
            Log::info('Data processed');

            $columns = $report->columns();

            // Generate filename with datetime format
            $uniqueFileName = $file_name . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Store the report
            $filePath = '/reports/' . $uniqueFileName;
            Excel::store(new CollectionExport($processedData, $columns), $filePath, 'local');

            Log::info('Report stored', ['filePath' => $filePath]);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Error in generateAndStore', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function getAvailableReportTypes()
    {
        $reportsPath = app_path('Reports');
        $reportFiles = \File::allFiles($reportsPath);

        $reportTypes = [];
        foreach ($reportFiles as $file) {
            $className = basename($file->getFilename(), '.php');
            $fullClassName = "App\\Reports\\{$className}";

            if (class_exists($fullClassName) && in_array(ReportTemplate::class, class_implements($fullClassName))) {
                $displayName = method_exists($fullClassName, 'getDisplayName') ? $fullClassName::getDisplayName() : $className;
                $reportTypes[$className] = $displayName;
            }
        }

        return $reportTypes;
    }
}
