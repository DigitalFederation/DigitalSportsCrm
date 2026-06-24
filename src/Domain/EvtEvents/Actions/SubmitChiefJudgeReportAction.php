<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\ChiefJudgeReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmitChiefJudgeReportAction
{
    /**
     * Submit (finalize) a Chief Judge report
     */
    public function execute(ChiefJudgeReport $report): ChiefJudgeReport
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

            Log::info('Chief Judge report submitted', [
                'event_id' => $report->event_id,
                'report_id' => $report->id,
            ]);

            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit Chief Judge report', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
