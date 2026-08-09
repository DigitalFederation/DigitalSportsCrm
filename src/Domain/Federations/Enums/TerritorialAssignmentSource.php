<?php

namespace Domain\Federations\Enums;

enum TerritorialAssignmentSource: string
{
    case CLUB = 'club';
    case RESIDENCE = 'residence';
    case MANUAL = 'manual';
    case IMPORT = 'import';

    /** @return list<string> */
    public static function automaticValues(): array
    {
        return [self::CLUB->value, self::RESIDENCE->value, self::IMPORT->value];
    }
}
