<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\User;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateAthleteEnrollmentStatusAction
{
    /**
     * Update athlete enrollment status with workflow validation.
     *
     * This action enforces the enrollment workflow:
     * - Step 1 (automatic): Registered -> Pending Payment -> Paid -> Discipline Assigned
     * - Step 2 (manual): Requires explicit confirmation via ConfirmAthleteEnrollmentCompletionAction
     *
     * COMPLETED status cannot be set via this action - use ConfirmAthleteEnrollmentCompletionAction instead.
     *
     * @throws InvalidArgumentException if transition is not allowed
     */
    public function execute(
        AthleteEnrollment $athleteEnrollment,
        string $newStatus,
        User $user
    ): void {
        DB::beginTransaction();
        try {
            $oldStatus = $athleteEnrollment->status_class;
            $newStatusEnum = EvtAthleteEnrollmentStatusEnum::from($newStatus);

            // Validate the transition
            $this->validateTransition($oldStatus, $newStatusEnum);

            // Update the status
            $athleteEnrollment->update(['status_class' => $newStatusEnum]);

            // Log the activity
            $this->logActivity(
                subject: $athleteEnrollment,
                user: $user,
                oldStatus: $oldStatus,
                newStatus: $newStatusEnum
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate that the state transition is allowed.
     *
     * @throws InvalidArgumentException if transition is not allowed
     */
    protected function validateTransition(
        EvtAthleteEnrollmentStatusEnum $currentStatus,
        EvtAthleteEnrollmentStatusEnum $newStatus
    ): void {
        // COMPLETED requires explicit confirmation action
        if ($newStatus === EvtAthleteEnrollmentStatusEnum::COMPLETED) {
            throw new InvalidArgumentException(
                __('events.enrollment_completed_requires_confirmation')
            );
        }

        // If it's the same status, no transition needed
        if ($currentStatus === $newStatus) {
            return;
        }

        // Check if transition is allowed
        if (! $currentStatus->canTransitionTo($newStatus)) {
            $currentLabel = EvtAthleteEnrollmentStatusEnum::toString($currentStatus);
            $newLabel = EvtAthleteEnrollmentStatusEnum::toString($newStatus);

            throw new InvalidArgumentException(
                __('events.enrollment_invalid_transition', [
                    'from' => $currentLabel,
                    'to' => $newLabel,
                ])
            );
        }
    }

    protected function logActivity(
        AthleteEnrollment $subject,
        User $user,
        EvtAthleteEnrollmentStatusEnum $oldStatus,
        EvtAthleteEnrollmentStatusEnum $newStatus
    ): void {
        $oldStatusLabel = EvtAthleteEnrollmentStatusEnum::toString($oldStatus);
        $newStatusLabel = EvtAthleteEnrollmentStatusEnum::toString($newStatus);

        activity()
            ->performedOn($subject)
            ->causedBy($user)
            ->withProperties([
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
            ])
            ->log("Athlete enrollment status updated from {$oldStatusLabel} to {$newStatusLabel}");
    }
}
