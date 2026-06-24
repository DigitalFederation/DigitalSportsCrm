<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\TechnicalDelegateReport;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveTechnicalDelegateReportAction
{
    /**
     * Save or update a Technical Delegate report (draft)
     */
    public function execute(Event $event, Individual $submittedBy, array $data): TechnicalDelegateReport
    {
        DB::beginTransaction();

        try {
            $report = TechnicalDelegateReport::updateOrCreate(
                ['event_id' => $event->id],
                [
                    'submitted_by' => $submittedBy->id,
                    'participants_withdrawals' => $data['participants_withdrawals'] ?? null,
                    'incidents_occurrences' => $data['incidents_occurrences'] ?? null,
                    'officials_performance' => $data['officials_performance'] ?? null,
                    'facilities_evaluation' => $data['facilities_evaluation'] ?? null,
                    'safety_first_aid' => $data['safety_first_aid'] ?? null,
                    'anti_doping_control' => $data['anti_doping_control'] ?? null,
                    'sports_protests' => $data['sports_protests'] ?? null,
                    'observations_recommendations' => $data['observations_recommendations'] ?? null,
                ]
            );

            DB::commit();

            Log::info('Technical Delegate report saved', [
                'event_id' => $event->id,
                'report_id' => $report->id,
                'submitted_by' => $submittedBy->id,
            ]);

            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save Technical Delegate report', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
