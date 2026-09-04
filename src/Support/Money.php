<?php

namespace Support;

use InvalidArgumentException;

/**
 * Formats monetary amounts according to config/currency.php.
 *
 * This is presentation only. Amounts are stored as plain decimals with no
 * currency attached; nothing here converts between currencies, and the
 * configured currency is never transmitted to a payment or invoicing provider.
 *
 * See docs/guides/localization-and-geography.md#currency.
 */
final class Money
{
    /**
     * Full string with symbol: "1.234,56 €".
     *
     * @param  int|null  $decimals  Overrides the configured decimal places.
     */
    public static function format(int|float|string|null $amount, bool $withSymbol = true, ?int $decimals = null): string
    {
        $number = self::amount($amount, $decimals);

        if (! $withSymbol) {
            return $number;
        }

        $symbol = self::symbol();
        $position = strtolower(trim((string) config('currency.position', 'after')));
        $space = filter_var(config('currency.space', true), FILTER_VALIDATE_BOOL) ? ' ' : '';

        // The sign belongs outside the symbol: "-1,00 €" and "-$1.00",
        // never "$-1.00".
        $negative = str_starts_with($number, '-');
        $unsigned = $negative ? substr($number, 1) : $number;
        $sign = $negative ? '-' : '';

        return $position === 'before'
            ? $sign.$symbol.$space.$unsigned
            : $sign.$unsigned.$space.$symbol;
    }

    /**
     * Number only, no symbol: "1.234,56".
     *
     * @param  int|null  $decimals  Overrides the configured decimal places.
     *
     * @throws InvalidArgumentException when given a non-numeric string.
     */
    public static function amount(int|float|string|null $amount, ?int $decimals = null): string
    {
        $value = self::toFloat($amount);

        $decimals ??= (int) config('currency.decimals', 2);

        return number_format(
            $value,
            max($decimals, 0),
            (string) (config('currency.decimal_separator') ?? ','),
            (string) (config('currency.thousands_separator') ?? '.'),
        );
    }

    /** The configured symbol: "€". */
    public static function symbol(): string
    {
        return (string) (config('currency.symbol') ?? '€');
    }

    /** The configured ISO 4217 code: "EUR". */
    public static function code(): string
    {
        return (string) (config('currency.code') ?? 'EUR');
    }

    /**
     * Null and empty string mean zero — call sites legitimately render a blank
     * database column as 0,00.
     *
     * Anything else non-numeric is a programming error, most often an amount
     * that was already run through number_format() and is being formatted a
     * second time. Casting that to 0.0 would silently report every total as
     * zero, so it fails loudly instead.
     *
     * @throws InvalidArgumentException
     */
    private static function toFloat(int|float|string|null $amount): float
    {
        if ($amount === null || $amount === '') {
            return 0.0;
        }

        if (! is_numeric($amount)) {
            throw new InvalidArgumentException(sprintf(
                'Money received the non-numeric value "%s". Pass a raw amount, not a pre-formatted string.',
                (string) $amount,
            ));
        }

        return (float) $amount;
    }
}
