<?php

namespace App\Enums;

enum UnitVolumeEnum: string
{
    case Liter = 'l';
    case Cuft = 'cuft';

    public function toString(): string
    {
        return match ($this) {
            self::Liter => 'Liter',
            self::Cuft => 'Cuft',
        };
    }
}
