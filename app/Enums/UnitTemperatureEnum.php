<?php

namespace App\Enums;

enum UnitTemperatureEnum: string
{
    case Celsius = 'C';
    case Fahrenheit = 'F';

    public function toString(): string
    {
        return match ($this) {
            self::Celsius => 'Celsius',
            self::Fahrenheit => 'Fahrenheit',
        };
    }
}
