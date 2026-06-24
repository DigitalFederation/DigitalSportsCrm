<?php

namespace App\Enums;

enum EvtEventGeographicalCoverageEnum: string
{
    case national = 'National';
    case international = 'International';

    public static function toString($scope): string
    {
        return match ($scope) {
            'national' => self::national->value,
            'international' => self::international->value,
            default => ''
        };
    }
}
