<?php

namespace App\Livewire\Reports;

use App\Jobs\GenerateReportJob;
use App\Reports\EntityInsurancesListReport;
use App\Reports\IndividualInsurancesListReport;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class IndividualInsuranceReportManager extends Component
{
    use WithPagination;

    public string $reportType = 'individual';

    public string $startDate = '';

    public string $endDate = '';

    public bool $generatingReport = false;

    public ?int $generatedReportId = null;

    public int $progress = 0;

    protected array $rules = [
        'reportType' => 'required|in:individual,entity',
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
    ];

    public function generateReport(): void
    {
        $this->validate();

        $this->generatingReport = true;
        $this->progress = 0;
        $this->generatedReportId = null;

        $reportClass = $this->reportType === 'entity'
            ? EntityInsurancesListReport::class
            : IndividualInsurancesListReport::class;

        $reportInstance = new $reportClass;

        $filters = [
            'start_date' => $this->startDate ?: null,
            'end_date' => $this->endDate ?: null,
        ];

        $report = GeneratedReport::create([
            'generated_by' => Auth::id(),
            'name' => $reportInstance->getDisplayName(),
            'status' => 'processing',
            'insurer_status' => 'pending',
            'filters' => $filters,
        ]);

        Log::info('Dispatching Insurance Report', [
            'reportId' => $report->id,
            'type' => $this->reportType,
            'filters' => $filters,
        ]);

        GenerateReportJob::dispatch($reportClass, $filters, $report->id);

        $this->generatedReportId = $report->id;
    }

    public function getReportStatus(): void
    {
        if (! $this->generatedReportId) {
            return;
        }

        $report = GeneratedReport::find($this->generatedReportId);

        if (! $report) {
            $this->generatingReport = false;

            return;
        }

        if ($report->status === 'processing') {
            $this->progress += 10;
            if ($this->progress > 90) {
                $this->progress = 90;
            }
        } elseif ($report->status === 'ready') {
            $this->generatingReport = false;
            $this->progress = 100;
        } elseif ($report->status === 'failed') {
            $this->generatingReport = false;
            $this->progress = 0;
            $this->addError('reportGeneration', __('reports.generation_failed'));
        }
    }

    public function updateInsurerStatus(int $reportId, string $status): void
    {
        $report = GeneratedReport::findOrFail($reportId);
        $report->update(['insurer_status' => $status]);
    }

    public function deleteReport(int $reportId): void
    {
        $report = GeneratedReport::findOrFail($reportId);

        if ($report->file_path) {
            \Storage::delete($report->file_path);
        }

        $report->delete();
    }

    public function render()
    {
        $reportNames = [
            IndividualInsurancesListReport::getDisplayName(),
            EntityInsurancesListReport::getDisplayName(),
        ];

        return view('livewire.reports.individual-insurance-report-manager', [
            'reports' => GeneratedReport::with('user')
                ->whereIn('name', $reportNames)
                ->orderBy('id', 'desc')
                ->paginate(15),
            'individualReportName' => $reportNames[0],
            'entityReportName' => $reportNames[1],
        ]);
    }
}
