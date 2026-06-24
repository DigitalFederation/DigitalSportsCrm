<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\TechnicalDelegateReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmitTechnicalDelegateReportAction
{
    /**
     * Submit (finalize) a Technical Delegate report
     */
    public function execute(TechnicalDelegateReport $report): TechnicalDelegateReport
    {
        if ($report->is_submitted) {
            throw new \Exception('Report has already been submitted.');
        }

        DB::beginTransaction();

        try {
            $report->update([
                'is_submitted' => true,
                'submitted_at' => now(),
            ]);

            DB::commit();

            Log::info('Technical Delegate report submitted', [
                'event_id' => $report->event_id,
                'report_id' => $report->id,
            ]);

            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit Technical Delegate report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
