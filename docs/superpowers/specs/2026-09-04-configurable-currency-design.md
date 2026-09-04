# Configurable currency — design

**Date:** 2026-09-04
**Status:** Approved, ready for implementation
**Scope:** Presentation-layer currency configuration (EUR / USD / BRL and anything else)

## Problem

The platform hardcodes the Euro everywhere it renders money. There is no currency
setting of any kind:

- No `currency` key in any file under `config/`, and no `CURRENCY` variable in
  `.env.example`.
- **212 literal `€` characters across 93 Blade templates**, written inline next to
  a raw value (`{{ $document->total_value }}€`) or in front of one
  (`€{{ number_format($price, 2) }}`).
- **9 PHP files with a literal `'EUR'`**: eight dashboard revenue charts use it as
  a y-axis label, `app/Reports/AffiliationsListReport.php:178` appends it to a
  formatted number, and `app/Console/Commands/ReconcileMoloniInvoices.php:74`
  prints it in console output.
- **6 translation keys per locale (36 strings)** bake the symbol or the code into
  the translated text itself — including the pt_BR file, so a Brazilian install
  today renders Portuguese text advertising Euros.

Number formatting is inconsistent on top of that: nearly every call site uses the
`number_format($x, 2)` default (`1,234.56` — US separators), while
`AffiliationsListReport` uses `number_format($fee, 2, ',', '.')` (`1.234,56`).
No Eurozone locale writes the former.

There is no single place where an amount becomes a string, so there is nothing to
change and no way for an operator to change it.

## Goals

1. One operator-facing setting that controls how every monetary amount renders.
2. One code path that turns an amount into a string, usable from Blade templates,
   PHP classes, reports, PDFs, and console output alike.
3. Consistent, locale-correct number formatting — separators and symbol position,
   not just the symbol.
4. A regression fence so the next hardcoded symbol fails a test instead of
   quietly shipping.

## Non-goals

Explicitly out of scope, and the spec should be read as forbidding them:

- **Per-record currency.** Amounts remain plain decimals. No `currency` column,
  no migration, no backfill.
- **Currency conversion or exchange rates.** One currency per install.
- **Multi-currency reporting or totals.**
- **Gateway and invoicing enforcement.** See "Payment and invoicing edge" below.
- **Any database change whatsoever.**

## Decisions

These were settled during design. They are recorded with their reasoning so the
implementation does not relitigate them.

| # | Decision | Rejected alternatives |
|---|---|---|
| 1 | **Env/config only, one currency per install.** | A DB-backed `SiteSetting` with an admin screen; per-record currency. A federation bills its members in its national currency, decided at deploy time. The code routes every read through one resolver, so a later move to `SiteSetting` is a one-file change. |
| 2 | **Explicit config keys over `ext-intl`.** | `NumberFormatter::formatCurrency()` driven by the app locale. ICU couples presentation to *the viewer's* locale, so the same amount would render differently per user rather than per federation — wrong for a self-hosted product where a deployer must read a config file and know what renders. It also adds an extension dependency and emits a non-breaking space that breaks string assertions. |
| 3 | **Document the EUR-only integrations; do not enforce.** | Warning at boot; hard guard. See below. |
| 4 | **A global `money()` helper backed by a class.** | A Blade component (views only — reports, charts, and console output would still need a PHP-callable); a bare static call (`use` statement or FQN in every template). |
| 5 | **Normalize EUR formatting to `1.234,56 €`.** | Preserving today's `1,234.56€` byte-for-byte. Existing installs see separators swap and a space appear on upgrade. This fixes a display bug rather than introducing one, and an operator who wants the old rendering can set the six env vars. Goes in the CHANGELOG under `Changed`. |

## Design

### 1. Configuration

New `config/currency.php`. Every key is env-driven; there is no database read.

```php
return [
    'code'                => env('CURRENCY_CODE', 'EUR'),
    'symbol'              => env('CURRENCY_SYMBOL', '€'),
    'position'            => env('CURRENCY_SYMBOL_POSITION', 'after'),  // before|after
    'space'               => env('CURRENCY_SYMBOL_SPACE', true),        // "1,00 €" vs "1,00€"
    'decimals'            => env('CURRENCY_DECIMALS', 2),
    'decimal_separator'   => env('CURRENCY_DECIMAL_SEPARATOR', ','),
    'thousands_separator' => env('CURRENCY_THOUSANDS_SEPARATOR', '.'),
];
```

`.env.example` gains a commented block documenting all seven keys with the three
presets below.

**Presets** (documented, not code — an operator sets the keys):

| Key | EUR (pt, es, fr, de) | USD | BRL |
|---|---|---|---|
| `CURRENCY_CODE` | `EUR` | `USD` | `BRL` |
| `CURRENCY_SYMBOL` | `€` | `$` | `R$` |
| `CURRENCY_SYMBOL_POSITION` | `after` | `before` | `before` |
| `CURRENCY_SYMBOL_SPACE` | `true` | `false` | `true` |
| `CURRENCY_DECIMAL_SEPARATOR` | `,` | `.` | `,` |
| `CURRENCY_THOUSANDS_SEPARATOR` | `.` | `,` | `.` |
| **Renders** | `1.234,56 €` | `$1,234.56` | `R$ 1.234,56` |

### 2. The formatter

`src/Support/Money.php`, in the already-mapped `Support\` PSR-4 namespace. Pure
PHP; its only framework dependency is `config()`.

```php
namespace Support;

final class Money
{
    /** Full string with symbol: "1.234,56 €" */
    public static function format(int|float|string|null $amount, bool $withSymbol = true, ?int $decimals = null): string;

    /** Number only, no symbol: "1.234,56" */
    public static function amount(int|float|string|null $amount, ?int $decimals = null): string;

    /** The configured symbol: "€" */
    public static function symbol(): string;

    /** The configured ISO code: "EUR" */
    public static function code(): string;
}
```

Behavioural requirements:

- `null` and `''` format as zero. Call sites already lean on `?? 0`, and a blank
  cell where a price belongs is worse than `0,00 €`.
- Numeric strings are accepted (Eloquent decimal casts hand back strings). Any
  other string throws `InvalidArgumentException` — almost always an amount that
  has already been through `number_format()`, which would otherwise render as
  `0,00 €` and silently misreport a total as zero.
- `$decimals` overrides the configured decimal places for the few call sites that
  deliberately show whole units (licence revenue analytics, insurance coverage
  ceilings).
- **Negative amounts keep the sign outside the symbol**: `-1,00 €` and `-$1.00`,
  never `1,00- €` or `$-1.00`.
- `decimals => 0` produces no separator and no trailing zeros.
- The symbol is joined with a normal space (U+0020) when `space` is true. Not a
  non-breaking space — it must survive `assertSee()` and PDF rendering unchanged.

`app/Helpers/CurrencyHelper.php` defines exactly one global function, guarded by
`function_exists()` in the style of `GlobalHelper.php`:

```php
if (! function_exists('money')) {
    function money(int|float|string|null $amount): string
    {
        return \Support\Money::format($amount);
    }
}
```

It is registered in `composer.json` under `autoload.files`, alongside the two
helper files already listed there. `money()` is the only global function added;
`amount()`, `symbol()`, and `code()` are called on the class at their handful of
call sites.

### 3. Call-site migration

**Blade templates (93 files, 212 occurrences).** Every `€` becomes a `money()`
call:

```blade
{{ $document->total_value }}€            →  {{ money($document->total_value) }}
€{{ number_format($price, 2) }}          →  {{ money($price) }}
```

Both shapes exist in the codebase, so edits are reviewed individually rather than
applied as a blind global replace. Where a template already wraps the value in
`number_format()`, that call is removed — `money()` does the formatting.

**PDF templates.** `resources/views/web/common/documents/document-invoice-pdf.blade.php`
and the `document/download` views render through DomPDF. The implementation must
render one such PDF and confirm the symbol appears, not a tofu box — DomPDF's
default font does not cover every glyph, and `R$` in particular must be verified.

**PHP classes (9 files).**

- The eight dashboard charts under `app/Livewire/{Admin,Federation}/Dashboard/`
  use `'text' => 'EUR'` as an axis label → `Money::code()`.
- `app/Reports/AffiliationsListReport.php:178`:
  `number_format($fee, 2, ',', '.') . ' EUR'` → `money($fee)`.
- **`app/Console/Commands/ReconcileMoloniInvoices.php:74` keeps its literal
  `'EUR'`, with a comment explaining why.** That line reports what Moloni
  invoiced, and Moloni is a Portuguese e-invoicing provider that issues in Euro.
  It describes an external fact, not this install's configuration.

**Translations (6 keys × 6 locales = 36 strings).** The currency leaves the
translated text and becomes a placeholder the caller fills:

| File | Before | After |
|---|---|---|
| `dashboard.php` | `'Total (EUR)'` | `'Total (:currency)'` |
| `dashboard.php` | `'Revenue (EUR)'` | `'Revenue (:currency)'` |
| `events.php` | `'…for a total of €:total?'` | `'…for a total of :total?'` |
| `licenses.php` | `'Purchase for €:amount'` | `'Purchase for :amount'` |
| `memberships.php` | `'…(set fees to €0)'` | `'…(set fees to :amount)'` |
| `main.php` | `'This is a free plan (set fees to €0)'` | same treatment |

Callers pass `money(...)` (or `Money::code()` for `:currency`) as the placeholder
value instead of a bare `number_format()`.

Two snags the implementation must handle:

1. **`licenses.Purchase for €:amount` is a translation key containing the
   symbol.** Renaming the key requires updating its call site in
   `resources/views/livewire/entity/license-purchase-form.blade.php:477` in the
   same change, or the lookup silently falls through to the raw key.
2. **`lang/*/main.php` uses the full English sentence as its key.** No `__()`
   call site references it (the live key is `memberships.free_plan_option`), so
   it is a stale duplicate — update the string in place for consistency and do
   not add a new call site.

**Plugins are out of scope.** `app/Plugins/` is a Composer package loader; a
plugin ships its own views and adopts `money()` on its own schedule. The helper
is globally autoloaded, so it is available to plugins with no further work.

### 4. Payment and invoicing edge

EasyPay (`config/payment.php`) is a Portugal-specific gateway and Moloni
(`config/invoicing.php`, `api.moloni.pt`) is a Portuguese e-invoicing provider.
Both operate in Euro.

Setting `CURRENCY_CODE=BRL` while either is enabled produces an install that
**displays `R$ 250,00` and invoices €250**. Per decision 3 this is documented,
not enforced: both integrations are opt-in and disabled by default
(`EASYPAY_ENABLED=false`, `MOLONI_ENABLED=false`), so the combination only arises
when an operator deliberately enables a Portugal-specific provider.

`docs/features/payments.md` must state this consequence explicitly.

### 5. Tests

**`tests/Unit/Support/MoneyTest.php`** — the formatting matrix across all three
presets, driven by `config()` overrides:

- zero, `null`, negative, a thousands boundary (`1000` → `1.000,00 €`), a value
  below one (`0.5` → `0,50 €`), a numeric string input
- `decimals => 0`
- `position` before and after; `space` true and false
- `Money::amount()` returns no symbol; `Money::code()` and `Money::symbol()`
  return the configured values

**One feature test** overrides `config(['currency.*' => …])` to BRL, renders a
page that displays money, and asserts `R$` appears — proving the config actually
reaches a template rather than only the unit under test.

**`tests/Unit/Support/NoHardcodedCurrencyTest.php`** — the regression fence.
Scans `resources/views/` and `app/` for a literal `€` or `' EUR'` and fails on
any hit, with a narrow allowlist:

- `config/currency.php` (the default value)
- `app/Console/Commands/ReconcileMoloniInvoices.php` (deliberate, see above)

This is what stops the sweep from rotting the first time someone adds a price to
a template.

## Documentation

| File | Change |
|---|---|
| `docs/guides/localization-and-geography.md` | New "Currency" section: the seven config keys, the preset table, and an explicit statement that this is presentation-only — it does not convert, and it does not change what a gateway charges. Extending this guide rather than adding a page keeps the VitePress sidebar untouched, and currency is a localization concern. |
| `docs/features/payments.md` | The EUR-only constraint on EasyPay and Moloni, and its consequence. |
| `docs/guides/development-style-guide.md` | One rule: never write a currency symbol into a template or a translated string; use `money()`. Makes the regression fence explainable rather than mysterious. |
| `docs/guides/getting-started.md` | Currency named in the initial `.env` walkthrough. |
| `.env.example` | The commented seven-key block with presets. |
| `CHANGELOG.md` | Under `[Unreleased]`: `Added` for the configuration, `Changed` for the EUR formatting normalization (decision 5). Per the project's versioning process, a config addition is a **minor**, not a major. |

## Corrections found during implementation

The survey behind this spec undercounted the affected surface in four ways. All are
fixed in the implementation; they are recorded here because the original numbers
above are quoted in the CHANGELOG and pull request.

1. **`src/` was never scanned.** The survey covered `app/` and `resources/views/`
   only. The Domain layer hardcoded the currency in four more places: three
   payment-notification actions building `number_format(...) . ' EUR'`
   (`ManuallyMarkDocumentAsPaidAction`, `MarkAsPaidAction`,
   `RegisterDocumentPaymentAction`) and `PaymentResponseData`, whose `currency`
   parameter defaults to `'EUR'`. The three actions now use `money()`.
   `PaymentResponseData` is allowlisted: it sits at the gateway boundary, where the
   configured currency is deliberately not transmitted.
2. **PHP was only grepped for `'EUR'`, never for `€`.** `IndividualEventRegistration`
   built a dropdown label with a literal `€`. Converted.
3. **Views were only grepped for `€`, never for `EUR`.** Nine further templates —
   including the event-application PDF, the Moloni settings screen, and a JavaScript
   `Intl.NumberFormat` pinned to `currency: 'EUR'` — wrote the ISO code instead of the
   symbol. That is **21 additional occurrences**, so the true total is 233, not 212.
4. **A stale `squidflex.currency_symbol` config key.** Seven templates read a config
   key belonging to an unrelated project; no `config/squidflex.php` exists. Four passed
   `'€'` as a fallback and rendered correctly by accident. The other three — both club
   subscriptions views — passed no default and have been rendering amounts with **no
   currency symbol at all**. All replaced with `money()`; the dead key is gone.
5. **Four spellings of the euro, not one.** The sweep and the first version of the
   fence matched only the literal `€` and the ISO code. The templates also spell it
   `&euro;` and `&#8364;`, which left **26 further occurrences** across 10 files —
   including the event checkout page and both individual licence purchase forms — so a
   BRL install would show `R$` on one screen and `€` two clicks later. The fence now
   matches all four spellings and has a test guarding that pattern list.
6. **A hardcoded US dollar.** `ExportLicensesAction` printed revenue statistics with a
   literal `$`. The fence cannot catch non-euro symbols, so this was found only by
   review.
7. **`Money` silently zeroed pre-formatted input.** `is_numeric()` returning false fell
   through to `0.0`, so passing an already-formatted `"1.234,56"` rendered `0,00 €`.
   `CalculateInvoiceAccountSummaryAction` returned exactly such strings.
   `Money::amount()` now throws `InvalidArgumentException` instead, and that action
   returns raw floats.

The regression fence now scans `resources/`, `app/`, `src/`, and `lang/`, matches all
four spellings of the euro, and matches `EUR` on a word boundary — a substring search
flags `EUROPEAN_GAMES` in `EvtCompetitionTypeEnum` and `DIRECTEUR` in the French
translations. A further test asserts the pattern list itself still detects every
spelling and still ignores those words.

One test deviates from this spec. The feature test renders Blade snippets through
`Blade::render()` rather than a full application page, because rendering a real
money-bearing page requires authentication and substantial fixture setup. It proves
the configuration reaches the template layer and that `money()` is autoloaded in
Blade; it does not prove any particular production page is wired correctly.

## Risks

| Risk | Mitigation |
|---|---|
| The EUR rendering change surprises existing operators on upgrade. | CHANGELOG `Changed` entry; the guide documents how to restore the old rendering via env vars. |
| A `€` is missed in the 212-occurrence sweep. | `NoHardcodedCurrencyTest` fails the build on any survivor. |
| `R$` renders as a tofu box in DomPDF. | Explicitly verified during implementation by rendering an invoice PDF under a BRL config. |
| Renaming `licenses.Purchase for €:amount` breaks the lookup. | Key and its single call site change in the same commit; covered by the feature test. |
| Some template passes an already-formatted string into `money()`. | `Money` accepts numeric strings but must not double-format; the sweep removes the inner `number_format()` at each site it touches. |
