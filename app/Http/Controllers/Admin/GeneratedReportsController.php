<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\ReportGeneratedNotification;
use App\Reports\ReportTemplate;
use App\Services\ReportGeneratorService;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GeneratedReportsController extends Controller
{
    protected ReportGeneratorService $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    public function index()
    {
        return view('web.admin.reports.index');
    }

    public function store(Request $request, ReportGeneratorService $reportGeneratorService)
    {

        $startTime = microtime(true);

        try {
            $reportType = $request->input('report_type');
            $reportClass = "App\\Reports\\{$reportType}";

            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            // Check if the class exists and is a valid report type
            if (! class_exists($reportClass) || ! in_array(ReportTemplate::class, class_implements($reportClass))) {
                \Log::error('Invalid report type', ['report_type' => $reportType]);

                return back()->withError('Invalid report type selected');
            }

            $userId = \Auth::id();

            // Generate the report in chunks
            $generationStart = microtime(true);
            $filePath = $reportGeneratorService->generateAndStore(new $reportClass, $filters, $reportType);

            $report_name = method_exists($reportClass, 'getDisplayName') ? $reportClass::getDisplayName() : $reportType;

            // Create or update the report record with new fields
            $storageStart = microtime(true);
            $report = GeneratedReport::updateOrCreate(
                [
                    'generated_by' => $userId,
                    'name' => $report_name,
                ],
                [
                    'file_path' => $filePath,
                    'generated_on' => now(),
                    'status' => 'ready',
                    'filters' => $filters,
                ]
            );

            // Ensure the file exists before setting the path and calculating size
            if (File::exists(storage_path('app/' . $filePath))) {
                \Log::debug('File exists before setting path', [
                    'file_path' => $filePath,
                    'full_path' => storage_path('app/' . $filePath),
                    'size' => filesize(storage_path('app/' . $filePath)),
                ]);

                $report->setFilePath($filePath);
                $report->save();

                \Log::debug('After setting path', [
                    'report_id' => $report->id,
                    'saved_size' => $report->file_size,
                ]);
            }

            return back()->withSuccess(__('Your report has finished processing. Find the link in the table below.'));
        } catch (\Exception $e) {
            \Log::error('Report generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'total_time' => microtime(true) - $startTime,
            ]);

            return back()->withError(__('Error while generating the report: ' . $e->getMessage()));
        } finally {
            \Log::info('Completed report generation process', [
                'total_time' => microtime(true) - $startTime,
            ]);
        }
    }

    public function download(GeneratedReport $report)
    {
        if (! auth()->user()->can('download reports')) {
            abort(403, 'You do not have permission to download reports.');
        }

        if (! $report->isDownloadable) {
            abort(404, 'Report is not available for download.');
        }

        $filePath = storage_path('app/' . $report->file_path);

        if (! File::exists($filePath)) {
            abort(404, 'File not found');
        }

        // Update last downloaded timestamp if you want to track this
        $report->touch();

        return response()->download($filePath);
    }

    public function checkStatus()
    {
        $user = \Auth::user();
        $notification = $user->notifications()->whereNull('read_at')->latest()->first();

        if ($notification && $notification->type == ReportGeneratedNotification::class) {
            // Mark the notification as read
            $notification->markAsRead();

            return response()->json([
                'newReportAvailable' => true,
                'file_path' => $notification->data['url'],
            ]);
        }

        return response()->json(['newReportAvailable' => false]);
    }

    // Delete
    public function destroy(GeneratedReport $report)
    {
        // Delete the physical file
        $filePath = storage_path('app/' . $report->file_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $report->delete();

        return back()->withSuccess('Report deleted successfully');
    }

    public function statistics()
    {
        return view('web.admin.reports.stats');
    }
}
