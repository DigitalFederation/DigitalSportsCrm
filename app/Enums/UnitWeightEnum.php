<?php

namespace App\Enums;

enum UnitWeightEnum: string
{
    case Kilogram = 'kg';
    case Pound = 'lb';

    public function toString(): string
    {
        return match ($this) {
            self::Kilogram => 'Kg',
            self::Pound => 'Lb',
        };
    }
}
