<?php

namespace App\Enums;

enum EvtCompetitionStatusEnum: string
{
    case APPROVED = 'Approved';
    case CANCELLED = 'Cancelled';
    case FINISHED = 'Finished';
    case REMOVED = 'Removed';
    case TO_BE_APPROVED = 'To be approved';
    case WAITING_INFO = 'Waiting info';

    public static function toString($type): string
    {
        return match ($type) {
            'APPROVED' => self::APPROVED->value,
            'CANCELLED' => self::CANCELLED->value,
            'FINISHED' => self::FINISHED->value,
            'REMOVED' => self::REMOVED->value,
            'TO_BE_APPROVED' => self::TO_BE_APPROVED->value,
            'WAITING_INFO' => self::WAITING_INFO->value,
            default => ''
        };
    }
}
