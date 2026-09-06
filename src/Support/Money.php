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
     * Read a human-typed amount back into a float.
     *
     * The inverse of format(): it accepts what the application displays, so an
     * operator can copy a fee off the screen and type it back. Returns null for
     * null, an empty string, or anything that is not an amount.
     *
     * Separators are resolved by position rather than by configuration, because
     * the two cannot be told apart from configuration alone: an installation
     * configured with "." for thousands would otherwise read the "1234.56" its
     * own earlier forms produced as 123456 — a hundredfold error on a fee.
     *
     * The rule: a "." or "," followed by exactly one or two digits at the end of
     * the string is the decimal separator. Every other "." and "," is a
     * thousands separator and is discarded.
     *
     *   "1234.56"   -> 1234.56      "1.234,56" -> 1234.56
     *   "1,234.56"  -> 1234.56      "R$ 1.234,56" -> 1234.56
     *   "1.234"     -> 1234.0       "250"      -> 250.0
     */
    public static function parse(int|float|string|null $input): ?float
    {
        if ($input === null) {
            return null;
        }

        if (is_int($input) || is_float($input)) {
            return (float) $input;
        }

        // Drop the currency symbol, spaces (including the non-breaking kind) and
        // anything else that is not part of a number.
        $value = preg_replace('/[^0-9.,\-]/u', '', $input) ?? '';

        if ($value === '' || $value === '-') {
            return null;
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');

        if (preg_match('/^(.*)([.,])(\d{1,2})$/', $value, $m) === 1) {
            $whole = str_replace(['.', ','], '', $m[1]);
            $value = ($whole === '' ? '0' : $whole).'.'.$m[3];
        } else {
            $value = str_replace(['.', ','], '', $value);
        }

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) ($negative ? '-'.$value : $value);
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
