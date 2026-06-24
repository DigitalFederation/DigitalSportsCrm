<?php

namespace App\Enums;

enum EvtEventPaymentStatusEnum: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';

    public static function toString($type): string
    {
        return match ($type) {
            'PENDING' => __('events.payment_status.pending'),
            'PAID' => __('events.payment_status.paid'),
            'CANCELED' => __('events.payment_status.canceled'),
            default => __('events.payment_status.pending'),
        };
    }
}
