<?php

if (! function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}

if (! function_exists('categorizePermissions')) {
    function categorizePermissions($permissions)
    {
        $categories = [
            'diving' => [],
            'sport' => [],
            'scientific' => [],
            'certification' => [],
            'license' => [],
            'membership' => [],
            'coach' => [],
            'instructor' => [],
            'individual' => [],
            'other' => [],
        ];

        foreach ($permissions as $permission) {
            $found = false;
            foreach ($categories as $category => $perms) {
                if (str_contains($permission->name, $category)) {
                    $categories[$category][] = $permission;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $categories['other'][] = $permission;
            }
        }

        return $categories;
    }
}

if (! function_exists('getSimplifiedFileType')) {
    function getSimplifiedFileType($mimeType)
    {
        $types = [
            'application/pdf' => 'PDF',
            'application/zip' => 'Zip File',
            'image/jpeg' => 'Image',
            'image/png' => 'Image',
            'image/gif' => 'Image',
            'application/msword' => 'Document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Document',
            'application/vnd.ms-excel' => 'Spreadsheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Spreadsheet',
            // Add more mime types and their simplified versions as needed
        ];

        return $types[$mimeType] ?? 'Other';
    }
}

if (! function_exists('money')) {
    /**
     * Format a monetary amount in the given (or installation) currency,
     * localized to the given (or active) locale.
     */
    function money(int|float|string|null $amount, ?string $currency = null, ?string $locale = null): string
    {
        $currencyEnum = \App\Enums\CurrencyEnum::tryFrom((string) ($currency ?? config('app.currency', 'EUR')))
            ?? \App\Enums\CurrencyEnum::Eur;

        if (is_string($amount) && preg_match('/^-?\d+,\d+$/', $amount)) {
            // Comma-decimal input ("12,50") would be truncated by the float cast.
            $amount = str_replace(',', '.', $amount);
        }
        $amount = (float) ($amount ?? 0);
        $locale ??= app()->getLocale();

        if (extension_loaded('intl')) {
            $formatted = \Illuminate\Support\Number::currency($amount, in: $currencyEnum->value, locale: $locale);
            if ($formatted !== false) {
                return $formatted;
            }
        }

        return $currencyEnum->symbol() . ' ' . number_format($amount, $currencyEnum->decimals(), ',', '.');
    }
}

if (! function_exists('currency_code')) {
    /**
     * ISO 4217 code of the installation currency.
     */
    function currency_code(): string
    {
        return \App\Enums\CurrencyEnum::current()->value;
    }
}

if (! function_exists('currency_symbol')) {
    /**
     * Symbol of the installation currency (€, R$, £, ...).
     */
    function currency_symbol(): string
    {
        return \App\Enums\CurrencyEnum::current()->symbol();
    }
}
