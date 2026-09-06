<?php

namespace App\Http\Requests\Concerns;

use Support\Money;

/**
 * Lets an operator type an amount the way the application displays it.
 *
 * Amounts render according to config/currency.php, so an installation
 * configured for euros or reais shows a fee as "1.234,56". Without this, the
 * same string is rejected by a `numeric` rule, and the operator has to work out
 * that the form wants "1234.56" instead.
 *
 * Call normalizeMonetaryInput() from prepareForValidation() with the fields that
 * hold money. Percentages, counts, and identifiers are deliberately not
 * included: a comma means something different in those.
 */
trait NormalizesMonetaryInput
{
    /**
     * @param  array<int, string>  $fields
     */
    protected function normalizeMonetaryInput(array $fields): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '' || ! is_string($value)) {
                continue;
            }

            $parsed = Money::parse($value);

            // Leave anything unparseable alone so validation reports it as the
            // operator typed it, rather than silently turning it into null.
            if ($parsed !== null) {
                $normalized[$field] = (string) $parsed;
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
