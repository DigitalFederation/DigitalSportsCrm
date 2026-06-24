<?php

namespace App\Console\Commands;

use App\Jobs\GenerateReportJob;
use App\Reports\EntityInsurancesListReport;
use App\Reports\IndividualInsurancesListReport;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyIndividualInsuranceReport extends Command
{
    protected $signature = 'reports:generate-weekly-insurance';

    protected $description = 'Generate weekly Individual and Entity Insurance reports (runs every Sunday at 23:59)';

    public function handle(): int
    {
        $reportClasses = [
            IndividualInsurancesListReport::class,
            EntityInsurancesListReport::class,
        ];

        $filters = [
            'start_date' => null,
            'end_date' => null,
        ];

        foreach ($reportClasses as $reportClass) {
            $reportInstance = new $reportClass;

            $report = GeneratedReport::create([
                'generated_by' => null,
                'name' => $reportInstance->getDisplayName(),
                'status' => 'processing',
                'insurer_status' => 'pending',
                'filters' => $filters,
            ]);

            GenerateReportJob::dispatch($reportClass, $filters, $report->id);

            Log::info('Weekly Insurance Report generation dispatched', [
                'reportId' => $report->id,
                'type' => class_basename($reportClass),
            ]);

            $this->info('Report dispatched: ' . $reportInstance->getDisplayName() . ' (ID: ' . $report->id . ')');
        }

        return self::SUCCESS;
    }
}
