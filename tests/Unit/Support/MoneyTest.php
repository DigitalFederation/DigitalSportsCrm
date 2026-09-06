<?php

use Support\Money;

function useEurConfig(): void
{
    config([
        'currency.code' => 'EUR',
        'currency.symbol' => '€',
        'currency.position' => 'after',
        'currency.space' => true,
        'currency.decimals' => 2,
        'currency.decimal_separator' => ',',
        'currency.thousands_separator' => '.',
    ]);
}

function useUsdConfig(): void
{
    config([
        'currency.code' => 'USD',
        'currency.symbol' => '$',
        'currency.position' => 'before',
        'currency.space' => false,
        'currency.decimals' => 2,
        'currency.decimal_separator' => '.',
        'currency.thousands_separator' => ',',
    ]);
}

function useBrlConfig(): void
{
    config([
        'currency.code' => 'BRL',
        'currency.symbol' => 'R$',
        'currency.position' => 'before',
        'currency.space' => true,
        'currency.decimals' => 2,
        'currency.decimal_separator' => ',',
        'currency.thousands_separator' => '.',
    ]);
}

describe('Money::format with EUR preset', function () {
    beforeEach(fn () => useEurConfig());

    it('formats zero', function () {
        expect(Money::format(0))->toBe('0,00 €');
    });

    it('formats null as zero', function () {
        expect(Money::format(null))->toBe('0,00 €');
    });

    it('keeps the negative sign outside the symbol', function () {
        expect(Money::format(-1))->toBe('-1,00 €');
    });

    it('formats a thousands boundary', function () {
        expect(Money::format(1000))->toBe('1.000,00 €');
    });

    it('formats a value below one', function () {
        expect(Money::format(0.5))->toBe('0,50 €');
    });

    it('accepts a numeric string', function () {
        expect(Money::format('1234.56'))->toBe('1.234,56 €');
    });
});

describe('Money::format with USD preset', function () {
    beforeEach(fn () => useUsdConfig());

    it('renders the symbol before the amount with no space', function () {
        expect(Money::format(1234.56))->toBe('$1,234.56');
    });

    it('keeps the negative sign outside the symbol', function () {
        expect(Money::format(-1))->toBe('-$1.00');
    });
});

describe('Money::format with BRL preset', function () {
    beforeEach(fn () => useBrlConfig());

    it('renders the symbol before the amount with a space', function () {
        expect(Money::format(1234.56))->toBe('R$ 1.234,56');
    });
});

describe('Money::format decimals => 0', function () {
    beforeEach(function () {
        useEurConfig();
        config(['currency.decimals' => 0]);
    });

    it('produces no separator and no trailing zeros', function () {
        expect(Money::format(1234.56))->toBe('1.235 €');
        expect(Money::format(1000))->toBe('1.000 €');
    });
});

describe('Money::format symbol position', function () {
    beforeEach(fn () => useEurConfig());

    it('renders the symbol before the amount when configured', function () {
        config(['currency.position' => 'before']);

        expect(Money::format(1))->toBe('€ 1,00');
    });

    it('renders the symbol after the amount when configured', function () {
        config(['currency.position' => 'after']);

        expect(Money::format(1))->toBe('1,00 €');
    });
});

describe('Money::format symbol space', function () {
    beforeEach(fn () => useEurConfig());

    it('adds a normal space (U+0020) when space is true', function () {
        config(['currency.space' => true]);

        $formatted = Money::format(1);

        expect($formatted)->toBe('1,00 €');
        // The separator must be a normal space, not a non-breaking space (U+00A0),
        // so it survives assertSee() and PDF rendering unchanged.
        expect(mb_strpos($formatted, "\u{00A0}"))->toBeFalse();
        expect($formatted)->toContain('1,00' . "\u{0020}" . '€');
    });

    it('adds no space when space is false', function () {
        config(['currency.space' => false]);

        expect(Money::format(1))->toBe('1,00€');
    });
});

describe('Money::amount', function () {
    beforeEach(fn () => useEurConfig());

    it('returns no symbol', function () {
        expect(Money::amount(1234.56))->toBe('1.234,56');
    });

    it('formats null as zero', function () {
        expect(Money::amount(null))->toBe('0,00');
    });
});

describe('Money::symbol and Money::code', function () {
    it('return the configured EUR values', function () {
        useEurConfig();

        expect(Money::symbol())->toBe('€');
        expect(Money::code())->toBe('EUR');
    });

    it('return the configured USD values', function () {
        useUsdConfig();

        expect(Money::symbol())->toBe('$');
        expect(Money::code())->toBe('USD');
    });

    it('return the configured BRL values', function () {
        useBrlConfig();

        expect(Money::symbol())->toBe('R$');
        expect(Money::code())->toBe('BRL');
    });
});

describe('Money rejects pre-formatted input', function () {
    beforeEach(fn () => useEurConfig());

    it('throws when given an already-formatted string', function () {
        // Casting this to 0.0 would silently report a 1.234,56 total as zero.
        Money::format('1.234,56');
    })->throws(InvalidArgumentException::class);

    it('throws on any non-numeric string', function () {
        Money::amount('not a number');
    })->throws(InvalidArgumentException::class);

    it('treats an empty string as zero', function () {
        expect(Money::format(''))->toBe('0,00 €');
    });

    it('accepts a negative numeric string', function () {
        expect(Money::format('-12.5'))->toBe('-12,50 €');
    });
});

describe('Money decimals override', function () {
    beforeEach(fn () => useEurConfig());

    it('formats whole units when asked for zero decimals', function () {
        expect(Money::format(1234.56, true, 0))->toBe('1.235 €')
            ->and(money(1000000, 0))->toBe('1.000.000 €');
    });

    it('leaves the configured decimals untouched when not overridden', function () {
        expect(money(1234.56))->toBe('1.234,56 €');
    });
});

describe('Money tolerates loose configuration', function () {
    it('accepts a capitalised position', function () {
        useEurConfig();
        config(['currency.position' => 'Before']);

        expect(Money::format(1))->toBe('€ 1,00');
    });

    it('treats the string "false" as no space', function () {
        useEurConfig();
        config(['currency.space' => 'false']);

        expect(Money::format(1))->toBe('1,00€');
    });

    it('falls back to sane separators when configured null', function () {
        useEurConfig();
        config(['currency.decimal_separator' => null, 'currency.thousands_separator' => null]);

        expect(Money::format(1000))->toBe('1.000,00 €');
    });
});
