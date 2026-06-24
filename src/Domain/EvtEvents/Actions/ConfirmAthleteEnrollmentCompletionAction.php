<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\User;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConfirmAthleteEnrollmentCompletionAction
{
    /**
     * Confirm athlete enrollment as completed.
     *
     * This is the only way to set an enrollment to COMPLETED status.
     * The enrollment must be in DISCIPLINE_ASSIGNED state.
     *
     * Workflow:
     * - Step 1 (automatic): Registered -> Pending Payment -> Paid -> Discipline Assigned
     * - Step 2 (manual): Admin confirms -> Completed (THIS ACTION)
     *
     * @throws InvalidArgumentException if enrollment cannot be confirmed
     */
    public function execute(
        AthleteEnrollment $athleteEnrollment,
        User $user
    ): AthleteEnrollment {
        DB::beginTransaction();
        try {
            $currentStatus = $athleteEnrollment->status_class;

            // Validate that enrollment can be confirmed
            if (! $currentStatus->canBeConfirmedAsCompleted()) {
                $currentLabel = EvtAthleteEnrollmentStatusEnum::toString($currentStatus);

                throw new InvalidArgumentException(
                    __('events.enrollment_cannot_confirm_completion', [
                        'status' => $currentLabel,
                    ])
                );
            }

            // Update to COMPLETED status
            $athleteEnrollment->update([
                'status_class' => EvtAthleteEnrollmentStatusEnum::COMPLETED,
            ]);

            // Log the confirmation activity
            $this->logActivity($athleteEnrollment, $user, $currentStatus);

            DB::commit();

            return $athleteEnrollment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Confirm multiple athlete enrollments as completed.
     *
     * @param  \Illuminate\Support\Collection<AthleteEnrollment>  $enrollments
     * @return array{confirmed: int, skipped: int, errors: array}
     */
    public function executeMany(
        iterable $enrollments,
        User $user
    ): array {
        $confirmed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($enrollments as $enrollment) {
            try {
                if ($enrollment->status_class->canBeConfirmedAsCompleted()) {
                    $this->execute($enrollment, $user);
                    $confirmed++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'enrollment_id' => $enrollment->id,
                    'individual' => $enrollment->individual?->full_name ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'confirmed' => $confirmed,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    protected function logActivity(
        AthleteEnrollment $subject,
        User $user,
        EvtAthleteEnrollmentStatusEnum $oldStatus
    ): void {
        $oldStatusLabel = EvtAthleteEnrollmentStatusEnum::toString($oldStatus);
        $newStatusLabel = EvtAthleteEnrollmentStatusEnum::toString(EvtAthleteEnrollmentStatusEnum::COMPLETED);

        activity()
            ->performedOn($subject)
            ->causedBy($user)
            ->withProperties([
                'old_status' => $oldStatus->value,
                'new_status' => EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                'action' => 'admin_confirmation',
            ])
            ->log("Athlete enrollment confirmed as completed by admin (from {$oldStatusLabel} to {$newStatusLabel})");
    }
}
