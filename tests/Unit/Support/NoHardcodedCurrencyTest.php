<?php

/**
 * Regression fence: nothing under resources/, app/, src/, or lang/ may hardcode
 * a currency. Amounts must render through money()/Support\Money so an operator's
 * CURRENCY_* config actually controls every call site.
 *
 * The euro was written four different ways in this codebase before the sweep —
 * the literal €, the ISO code, and the HTML entities &euro; and &#8364; — and
 * a pattern that missed any one of them let 26 occurrences through. Keep all
 * spellings here.
 *
 * See docs/superpowers/specs/2026-09-04-configurable-currency-design.md.
 */
const NO_HARDCODED_CURRENCY_ALLOWLIST = [
    // Documents the default value of the config key itself.
    'config/currency.php',
    // The formatter: holds the default symbol and code in its fallbacks.
    'src/Support/Money.php',
    // Deliberate: reports what Moloni (a Portuguese e-invoicing provider) invoiced,
    // not this install's configured currency. See the comment at the call site.
    'app/Console/Commands/ReconcileMoloniInvoices.php',
    // Gateway boundary. The configured currency is never transmitted to a payment
    // provider (see the spec's "Payment and invoicing edge"); this default describes
    // what the bundled Euro-only gateways operate in.
    'src/Domain/Payments/DataTransferObject/PaymentResponseData.php',
];

/**
 * Every way this codebase has spelled a hardcoded currency.
 *
 * \bEUR\b rather than a substring search: EUROPEAN_GAMES in EvtCompetitionTypeEnum
 * and DIRECTEUR/ENTRAÎNEUR in the French translations are not currencies.
 */
const NO_HARDCODED_CURRENCY_PATTERNS = [
    '/€/u',
    '/\bEUR\b/',
    '/&euro;/i',
    '/&#8364;/',
    '/&#x20AC;/i',
];

function scanForHardcodedCurrency(string $directory): array
{
    $base = base_path();
    $violations = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base.'/'.$directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $relativePath = ltrim(str_replace($base, '', $file->getPathname()), '/');

        if (in_array($relativePath, NO_HARDCODED_CURRENCY_ALLOWLIST, true)) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        foreach (NO_HARDCODED_CURRENCY_PATTERNS as $pattern) {
            if (preg_match($pattern, $contents) === 1) {
                $violations[] = $relativePath;
                break;
            }
        }
    }

    return $violations;
}

it('has no hardcoded currency in resources', function () {
    $violations = scanForHardcodedCurrency('resources');

    expect($violations)->toBe([], "Hardcoded currency found in:\n".implode("\n", $violations));
});

it('has no hardcoded currency in app', function () {
    $violations = scanForHardcodedCurrency('app');

    expect($violations)->toBe([], "Hardcoded currency found in:\n".implode("\n", $violations));
});

it('has no hardcoded currency in src', function () {
    $violations = scanForHardcodedCurrency('src');

    expect($violations)->toBe([], "Hardcoded currency found in:\n".implode("\n", $violations));
});

it('has no hardcoded currency in lang', function () {
    $violations = scanForHardcodedCurrency('lang');

    expect($violations)->toBe([], "Hardcoded currency found in:\n".implode("\n", $violations));
});

it('detects every spelling of a hardcoded euro', function () {
    // Guards the fence itself: a pattern list that silently stopped matching one
    // of these spellings is how 26 occurrences survived the original sweep.
    foreach (['€', 'Total: 5 EUR', '&euro;', '&#8364;', '&#x20AC;'] as $spelling) {
        $matched = false;

        foreach (NO_HARDCODED_CURRENCY_PATTERNS as $pattern) {
            if (preg_match($pattern, $spelling) === 1) {
                $matched = true;
                break;
            }
        }

        expect($matched)->toBeTrue("Fence failed to detect the spelling: {$spelling}");
    }

    // ...and does not fire on words that merely contain the letters.
    foreach (['EUROPEAN_GAMES', 'DIRECTEUR DE COURS', 'ENTRAÎNEUR'] as $innocent) {
        foreach (NO_HARDCODED_CURRENCY_PATTERNS as $pattern) {
            expect(preg_match($pattern, $innocent))->toBe(0, "Fence false-positived on: {$innocent}");
        }
    }
});
