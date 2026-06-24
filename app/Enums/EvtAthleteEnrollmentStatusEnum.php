<?php

namespace App\Enums;

enum EvtAthleteEnrollmentStatusEnum: string
{
    case REGISTERED = 'registered';          // Initial selection
    case PENDING_PAYMENT = 'pending_payment'; // Waiting for payment
    case PAID = 'paid';                     // Payment confirmed
    case DISCIPLINE_ASSIGNED = 'discipline_assigned'; // Discipline selected
    case COMPLETED = 'completed';           // Full process complete (requires admin confirmation)
    case CANCELED = 'canceled';             // Enrollment canceled by admin (terminal state)

    case ACTIVE = 'ACTIVE'; // ONLY FOR HISTORIC REASONS

    public static function toString($status): string
    {
        $statusValue = $status instanceof self ? $status->value : $status;

        return match ($statusValue) {
            self::REGISTERED->value => __('events.registered'),
            self::PENDING_PAYMENT->value => __('events.enrollment_status_pending_payment'),
            self::PAID->value => __('events.enrollment_status_paid'),
            self::DISCIPLINE_ASSIGNED->value => __('events.enrollment_status_enrolled'),
            self::COMPLETED->value => __('events.enrollment_status_confirmed'),
            self::CANCELED->value => __('events.canceled'),
            default => __('events.unknown'),
        };
    }

    /**
     * Get allowed transitions from current state.
     * COMPLETED is not in this list - it requires explicit admin confirmation action.
     *
     * Workflow:
     * Step 1 (automatic): Registered -> Pending Payment -> Paid -> Discipline Assigned
     * Step 2 (manual): Admin confirms -> Completed
     */
    public function getAllowedTransitions(): array
    {
        return match ($this) {
            self::REGISTERED => [self::PENDING_PAYMENT, self::PAID, self::DISCIPLINE_ASSIGNED],
            self::PENDING_PAYMENT => [self::PAID, self::DISCIPLINE_ASSIGNED],
            self::PAID => [self::DISCIPLINE_ASSIGNED],
            self::DISCIPLINE_ASSIGNED => [], // COMPLETED requires explicit admin confirmation
            self::COMPLETED => [], // Final state
            self::CANCELED => [], // Terminal state
            self::ACTIVE => [self::DISCIPLINE_ASSIGNED, self::COMPLETED], // Legacy support
        };
    }

    /**
     * Check if transition to target state is allowed via dropdown.
     * COMPLETED is never allowed via dropdown - use confirmCompletion action instead.
     */
    public function canTransitionTo(self $target): bool
    {
        // COMPLETED always requires explicit confirmation action
        if ($target === self::COMPLETED) {
            return false;
        }

        return in_array($target, $this->getAllowedTransitions());
    }

    /**
     * Check if this state can be confirmed as completed.
     * Only DISCIPLINE_ASSIGNED can be confirmed.
     */
    public function canBeConfirmedAsCompleted(): bool
    {
        return $this === self::DISCIPLINE_ASSIGNED;
    }

    /**
     * Get states that can be shown in admin dropdown (excludes COMPLETED and ACTIVE).
     */
    public static function getDropdownStates(): array
    {
        return [
            self::REGISTERED,
            self::PENDING_PAYMENT,
            self::PAID,
            self::DISCIPLINE_ASSIGNED,
        ];
    }

    /**
     * Get the badge/color class for this status.
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::REGISTERED => 'bg-blue-100 text-blue-800',
            self::PENDING_PAYMENT => 'bg-yellow-100 text-yellow-800',
            self::PAID => 'bg-green-100 text-green-800',
            self::DISCIPLINE_ASSIGNED => 'bg-purple-100 text-purple-800',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800',
            self::CANCELED => 'bg-red-100 text-red-800',
            self::ACTIVE => 'bg-gray-100 text-gray-800',
        };
    }

    public function isCanceled(): bool
    {
        return $this === self::CANCELED;
    }
}
