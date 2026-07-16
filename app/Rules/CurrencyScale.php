<?php

namespace App\Rules;

use App\Enums\CurrencyEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects fractional amounts when the installation currency has no
 * minor unit (e.g. CLP). No-op for 2-decimal currencies.
 */
class CurrencyScale implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return;
        }

        $currency = CurrencyEnum::current();

        if ($currency->decimals() > 0) {
            return;
        }

        if ((float) $value !== floor((float) $value)) {
            $fail(__('validation.currency_no_decimals', [
                'attribute' => $attribute,
                'currency' => $currency->value,
            ]));
        }
    }
}
