<?php

namespace App\Enums;

enum EvtDisciplineGenderEnum: string
{
    case MALE = 'Male';
    case FEMALE = 'Female';
    case MIXED = 'Mixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
