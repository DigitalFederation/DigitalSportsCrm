<?php

namespace App\Livewire\Reports;

use App\Jobs\GenerateReportJob;
use App\Reports\ReportTemplate;
use App\Services\ReportGeneratorService;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ReportGenerator extends Component
{
    public $reportType;
    public $startDate;
    public $endDate;
    public $generatingReport = false;
    public $generatedReportId;
    public $reportTypes;
    public $progress = 0;

    protected $rules = [
        'reportType' => 'required',
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
    ];

    public function mount(ReportGeneratorService $reportGeneratorService)
    {
        $reportTypes = $reportGeneratorService->getAvailableReportTypes();

        // Sort by the display names (values) instead of class names (keys)
        $this->reportTypes = collect($reportTypes)
            ->sort()
            ->all();

        if (! empty($this->reportTypes)) {
            $this->reportType = array_key_first($this->reportTypes);
        }
    }

    public function generateReport()
    {
        $this->validate();

        Log::info('Attempting to generate report', ['reportType' => $this->reportType]);

        $this->generatingReport = true;
        $this->progress = 0;
        $this->generatedReportId = null;

        $reportClass = "App\\Reports\\{$this->reportType}";

        if (! class_exists($reportClass) || ! in_array(ReportTemplate::class, class_implements($reportClass))) {
            Log::error('Invalid report type', ['reportType' => $this->reportType]);
            $this->addError('reportType', 'Invalid report type selected');
            $this->generatingReport = false;

            return;
        }

        $reportInstance = new $reportClass;

        $filters = [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];

        $report = GeneratedReport::create([
            'generated_by' => Auth::id(),
            'name' => method_exists($reportInstance, 'getDisplayName')
                ? $reportInstance->getDisplayName()
                : class_basename($reportClass),
            'status' => 'processing',
            'filters' => $filters,
        ]);

        Log::info('Dispatching GenerateReportJob', [
            'reportId' => $report->id,
            'reportClass' => $reportClass,
            'filters' => $filters,
        ]);

        GenerateReportJob::dispatch($reportClass, $filters, $report->id);

        $this->generatedReportId = $report->id;

        Log::info('Report generation initiated', ['reportId' => $this->generatedReportId]);
    }

    public function getReportStatus()
    {
        if (! $this->generatedReportId) {
            return;
        }

        $report = GeneratedReport::find($this->generatedReportId);

        if ($report->status === 'processing') {
            $this->progress += 10; // Increment progress
            if ($this->progress > 90) {
                $this->progress = 90; // Cap at 90% until it's done
            }
        } elseif ($report->status === 'ready') {
            $this->generatingReport = false;
            $this->progress = 100;
            $this->dispatch('reportGenerated', $report->id);
            Log::info('Report generation completed', ['reportId' => $report->id]);
        } elseif ($report->status === 'failed') {
            $this->generatingReport = false;
            $this->progress = 0;
            $this->addError('reportGeneration', 'Report generation failed. Please try again.');
            Log::error('Report generation failed', ['reportId' => $report->id]);
        }
    }

    public function render()
    {
        return view('livewire.reports.report-generator', [
            'reports' => GeneratedReport::with('user')
                ->orderBy('id', 'desc')
                ->paginate(15),
        ]);
    }
}
