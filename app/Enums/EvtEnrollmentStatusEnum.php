<?php

namespace App\Enums;

enum EvtEnrollmentStatusEnum: string
{
    case PRE_ENROLLED = 'pre_enrolled';  // Initial registration
    case PAID = 'paid';                  // Payment confirmed
    case PENDING = 'pending';            // During discipline assignment
    case ACTIVE = 'active';              // Fully enrolled with disciplines

    public static function toString($type): string
    {
        return match ($type) {
            self::PRE_ENROLLED->value => 'Pre-enrolled',
            self::PAID->value => 'Paid',
            self::PENDING->value => 'Pending',
            self::ACTIVE->value => 'Active',
            default => 'Unknown',
        };
    }

    public static function toArray(): array
    {
        return array_map(fn ($status) => [
            'name' => $status->name,
            'value' => $status->value,
        ], self::cases());
    }
}
