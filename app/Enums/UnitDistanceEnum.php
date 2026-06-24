<?php

namespace App\Enums;

enum UnitDistanceEnum: string
{
    case Meter = 'm';
    case Feet = 'ft';

    public function toString(): string
    {
        return match ($this) {
            self::Meter => 'Meter',
            self::Feet => 'Feet',
        };
    }
}
