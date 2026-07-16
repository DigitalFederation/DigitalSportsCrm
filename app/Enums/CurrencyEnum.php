<?php

namespace App\Enums;

enum CurrencyEnum: string
{
    case Eur = 'EUR';
    case Usd = 'USD';
    case Brl = 'BRL';
    case Gbp = 'GBP';
    case Cop = 'COP';
    case Mxn = 'MXN';
    case Ars = 'ARS';
    case Clp = 'CLP';
    case Myr = 'MYR';

    public function symbol(): string
    {
        return match ($this) {
            self::Eur => '€',
            self::Usd, self::Cop, self::Mxn, self::Ars, self::Clp => '$',
            self::Brl => 'R$',
            self::Gbp => '£',
            self::Myr => 'RM',
        };
    }

    /**
     * ISO 4217 minor-unit digits. CLP has no minor unit.
     */
    public function decimals(): int
    {
        return match ($this) {
            self::Clp => 0,
            default => 2,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Eur => 'Euro (EUR)',
            self::Usd => 'US Dollar (USD)',
            self::Brl => 'Real Brasileiro (BRL)',
            self::Gbp => 'Pound Sterling (GBP)',
            self::Cop => 'Peso Colombiano (COP)',
            self::Mxn => 'Peso Mexicano (MXN)',
            self::Ars => 'Peso Argentino (ARS)',
            self::Clp => 'Peso Chileno (CLP)',
            self::Myr => 'Malaysian Ringgit (MYR)',
        };
    }

    public static function current(): self
    {
        return self::tryFrom((string) config('app.currency', 'EUR')) ?? self::Eur;
    }

    /**
     * @return array<string, string> code => label, for select inputs
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
