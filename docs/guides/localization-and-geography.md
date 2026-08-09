---
title: Localization and Geography
description: Configure locale, timezone, default country, geographic terminology, and install-time geography data
---

# Localization and Geography

Digital Sports CRM uses one shared codebase for every deployment. Country-specific behavior belongs
in environment configuration, translation files, reference-data packages, and explicit domain
policies—not in long-lived country forks.

## Installation defaults

Set these values in `.env` before caching configuration or running install seeders:

```ini
APP_LOCALE=pt_PT
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=UTC
DEFAULT_COUNTRY_CODE=PT
GEOGRAPHY_DATASET=portugal
```

| Variable | Purpose |
|----------|---------|
| `APP_LOCALE` | Default interface locale when neither the user nor the session has selected one. |
| `APP_FALLBACK_LOCALE` | Locale used when a translation is missing. |
| `APP_TIMEZONE` | Application timezone used by Laravel. Use an IANA identifier such as `Europe/Lisbon` or `America/Sao_Paulo`, or retain `UTC`. |
| `DEFAULT_COUNTRY_CODE` | ISO 3166-1 alpha-2 country code used when a country cannot be inferred from the submitted district. |
| `GEOGRAPHY_DATASET` | Reference-data package selected by `FreshInstallSeeder`. Only registered datasets are accepted. |

The default country is resolved by `country.iso`, not by its database ID. The configured ISO code
must identify exactly one country; missing or ambiguous configuration fails explicitly instead of
silently selecting an arbitrary record.

After changing any of these values in an existing deployment, clear and rebuild Laravel's cached
configuration:

```bash
php artisan optimize:clear
php artisan config:cache
```

## Locales and terminology

`pt_PT` and `pt_BR` are separate locales. They share internal domain concepts while presenting the
terminology appropriate to each installation:

| Internal concept | `pt_PT` | `pt_BR` |
|------------------|---------|---------|
| `Zone` | Zona | Estado |
| `District` | Distrito | Município |

Internal class, relationship, table, and request-field names remain `Zone`/`District`. Translation
does not change the database model or provide country reference data by itself.

Users can change to any locale registered in `config/app.php`. An authenticated user's selection is
stored on the user record and takes precedence over the session and application default.

## Geography datasets

Geographic reference data is selected through `config/geography.php`. A dataset is a seeder class
that installs the districts and zones for one deployment profile. The currently bundled dataset is:

| Dataset | Seeder | Status |
|---------|--------|--------|
| `portugal` | `PortugalGeographySeeder` | Supported: 20 districts and 5 operational zones |
| `brazil` | `BrazilGeographySeeder` | Supported: 27 federation units and 5,571 municipality-level localities |

For a Brazilian installation, configure the deployment before running the seeder:

```ini
APP_LOCALE=pt_BR
APP_TIMEZONE=America/Sao_Paulo
DEFAULT_COUNTRY_CODE=BR
GEOGRAPHY_DATASET=brazil
```

The dataset selector and default country are intentionally independent. `FreshInstallSeeder`
rejects an unsupported dataset, while each country seeder fails if its expected ISO country is not
present. This prevents a partially configured installation from silently receiving data for the
wrong country.

For a new database that should use the minimal, country-aware installation seeder, run:

```bash
php artisan migrate
php artisan db:seed --class=FreshInstallSeeder
```

`php artisan migrate --seed` invokes the broader `DatabaseSeeder`. It does not currently select a
country geography package. Choose one installation path; do not run both seeders over the same fresh
database.

The dataset selector is install-time behavior. Changing `GEOGRAPHY_DATASET` does not migrate,
replace, or delete geographic records in an existing database.

### Brazilian administrative geography

The existing `zones` table continues to support deployment-defined operational groupings. Brazilian
states use the separate `administrative_level_1` zone kind and carry both a namespaced application
code such as `BR-SP` and the official two-digit IBGE external code. Each Brazilian municipality
record has one direct `administrative_zone_id` parent. The existing `district_zone` many-to-many
relationship is not repurposed or constrained, preserving compatibility with operational zones.

The browser selector follows State → Municipality and only queries municipalities for the selected
state. Server-side validation verifies that the submitted municipality belongs to the configured
default country and, when applicable, to the submitted state. The nationality field remains
independent from the address geography.

### IBGE source and updates

The checked-in files under `database/data/brazil/` are a snapshot retrieved on 2026-08-09 from the
official IBGE Localidades API. Installation never calls the remote API. The snapshot contains the
5,569 municipalities in the current territorial structure plus Brasília and Fernando de Noronha,
which the IBGE includes as municipality-level records for statistical dissemination.

IBGE assigns seven-digit municipality codes whose first two digits identify the federation unit.
These official codes are stored in `districts.code`; the official state codes are stored in
`zones.external_code`. See the dataset README for endpoints, scope, and refresh instructions.

To propose an update after an IBGE territorial change:

```bash
php scripts/update-brazil-geography.php
```

Review the upstream change, adjust the seeder's expected record count, run the geography tests, and
commit the regenerated CSV files with the code change. Do not update the snapshot without recording
the new retrieval date and explaining additions, removals, or renames in the PR.

## Territory not listed

Forms use the country-neutral request value `territory_not_listed` when no configured district or
municipality applies. The application stores `district_id = null`; the sentinel itself is never
stored in the database.

API clients, browser automation, or external integrations that previously submitted
`outside_portugal` must change to `territory_not_listed`. The legacy request value is rejected.

## Updating an existing installation

1. Add `DEFAULT_COUNTRY_CODE` with the ISO code matching the existing installation.
2. Set `APP_LOCALE`, `APP_FALLBACK_LOCALE`, and `APP_TIMEZONE` explicitly so the deployment keeps its
   intended behavior.
3. Set `GEOGRAPHY_DATASET=portugal` only when that package describes the installation. This value
   has no automatic effect on existing geographic rows.
4. Update integrations to send `territory_not_listed` instead of `outside_portugal`.
5. Clear configuration caches and run the relevant test suite.

`DEFAULT_COUNTRY_ID` remains available only as a deprecated transition fallback when
`DEFAULT_COUNTRY_CODE` is absent. Migrate to the ISO code and then remove the legacy variable from
the deployment environment.

No database backfill is required for the territory sentinel because it was a form value, not stored
data. This release also performs no automatic conversion of existing districts, zones, or federation
territory mappings.

## Current limits and next steps

The geography layer does not yet:

- provide an address-country switcher in the public registration form (it uses the configured
  installation country);
- migrate existing custom records into administrative parents automatically;
- provide a bulk operator command for reconciling historical territorial memberships;
- infer assignment provenance for historical `individual_federation` rows.

Territorial federation resolution for new and actively reconciled relationships is documented in
[Territorial Federation Assignment](/guides/territorial-federation-assignment).

Those changes require schema, data-migration, UI, and affiliation-policy work and should be delivered
in subsequent focused pull requests.
