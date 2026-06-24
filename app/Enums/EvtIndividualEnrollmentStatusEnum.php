<?php

namespace App\Enums;

enum EvtIndividualEnrollmentStatusEnum: string
{
    case PENDING = 'pending';           // Initial selection, waiting for payment
    case REGISTERED = 'registered';     // Used for legacy compatibility
    case PAID = 'paid';                 // Payment confirmed or free event
    case COMPLETED = 'completed';       // Full process complete

    public static function toString($status): string
    {
        return match ($status) {
            self::PENDING->value => __('Pending Payment'),
            self::REGISTERED->value => __('Registered'),
            self::PAID->value => __('Paid'),
            self::COMPLETED->value => __('Completed'),
            default => __('Unknown'),
        };
    }
}
