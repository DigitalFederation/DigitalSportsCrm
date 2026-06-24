<?php

namespace App\Enums;

enum UnitPressureEnum: string
{
    case Bar = 'bar';
    case Psi = 'psi';

    public function toString(): string
    {
        return match ($this) {
            self::Bar => 'Bar',
            self::Psi => 'Psi',
        };
    }
}
