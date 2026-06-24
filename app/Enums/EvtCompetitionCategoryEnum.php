<?php

namespace App\Enums;

enum EvtCompetitionCategoryEnum: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';

    public static function toString($scope): string
    {
        return match ($scope) {
            'A' => self::A->value,
            'B' => self::B->value,
            'C' => self::C->value,
            'D' => self::D->value,
            'E' => self::E->value,
            default => ''
        };
    }
}
