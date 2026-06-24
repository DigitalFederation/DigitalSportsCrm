<?php

namespace App\Enums;

enum EvtCompetitionAgeCategoryEnum: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case JUNIORS = 'Juniors';
    case JUNIORS_SENIORS = 'Juniors-Seniors';
    case JUNIORS_SENIORS_MASTERS = 'Juniors-Seniors-Masters';
    case MASTERS = 'Masters';
    case SENIORS = 'Seniors';
    case SENIORS_MASTERS = 'Seniors-Masters';

    public static function toString($scope): string
    {
        return match ($scope) {
            'A' => self::A->value,
            'B' => self::B->value,
            'C' => self::C->value,
            'D' => self::D->value,
            'E' => self::E->value,
            'JUNIORS' => self::JUNIORS->value,
            'JUNIORS_SENIORS' => self::JUNIORS_SENIORS->value,
            'JUNIORS_SENIORS_MASTERS' => self::JUNIORS_SENIORS_MASTERS->value,
            'MASTERS' => self::MASTERS->value,
            'SENIORS' => self::SENIORS->value,
            'SENIORS_MASTERS' => self::SENIORS_MASTERS->value,
            default => ''
        };
    }
}
