<?php

namespace App\Enums;

enum EvtEventTypeEnum: string
{
    case CLUBS_COMPETITION = 'Clubs Competition';
    case NATIONAL_TEAM_COMPETITION = 'National Team Competition';
    case OPEN_COMPETITION = 'Open Competition';

    public static function toString($type): string
    {
        return match ($type) {
            self::CLUBS_COMPETITION => 'Clubs Competition',
            self::NATIONAL_TEAM_COMPETITION => 'National Team Competition',
            self::OPEN_COMPETITION => 'Open Competition',
            default => 'Other',
        };
    }
}
