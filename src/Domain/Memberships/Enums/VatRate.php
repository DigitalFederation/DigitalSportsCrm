<?php

namespace Domain\Memberships\Enums;

enum VatRate: int
{
    case ZERO = 0;
    case REDUCED = 6;
    case INTERMEDIATE = 13;
    case NORMAL = 23;

    public function label(): string
    {
        return match ($this) {
            self::ZERO => '0% (Isento)',
            self::REDUCED => '6% (Taxa Reduzida)',
            self::INTERMEDIATE => '13% (Taxa Intermédia)',
            self::NORMAL => '23% (Taxa Normal)',
        };
    }

    public function percentage(): int
    {
        return $this->value;
    }

    public static function default(): self
    {
        return self::NORMAL;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
