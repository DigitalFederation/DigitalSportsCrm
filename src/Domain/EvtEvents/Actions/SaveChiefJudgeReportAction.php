<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\ChiefJudgeReport;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveChiefJudgeReportAction
{
    /**
     * Save or update a Chief Judge report (draft)
     */
    public function execute(Event $event, Individual $submittedBy, array $data): ChiefJudgeReport
    {
        DB::beginTransaction();

        try {
            $report = ChiefJudgeReport::updateOrCreate(
                ['event_id' => $event->id],
                [
                    'submitted_by' => $submittedBy->id,
                    'technical_considerations' => $data['technical_considerations'] ?? null,
                ]
            );

            DB::commit();

            Log::info('Chief Judge report saved', [
                'event_id' => $event->id,
                'report_id' => $report->id,
                'submitted_by' => $submittedBy->id,
            ]);

            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save Chief Judge report', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
