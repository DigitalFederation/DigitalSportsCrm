<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\RefereeEnrollment;
use Illuminate\Support\Facades\Log;

class SaveRefereeEvaluationAction
{
    /**
     * Save an evaluation score and notes on a referee enrollment.
     * Activates the enrollment if it is still pending.
     *
     * @param  array  $data  Contains: evaluation (int|null), evaluation_notes (string|null)
     */
    public function execute(RefereeEnrollment $refereeEnrollment, array $data): void
    {
        $refereeEnrollment->update([
            'evaluation' => $data['evaluation'] ?? null,
            'evaluation_notes' => $data['evaluation_notes'] ?? null,
        ]);

        if ($refereeEnrollment->state->isPending()) {
            $refereeEnrollment->activate();
        }

        Log::info('Referee evaluation saved', [
            'referee_enrollment_id' => $refereeEnrollment->id,
            'evaluation' => $data['evaluation'] ?? null,
        ]);
    }
}
