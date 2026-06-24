---
title: Configuring Committees
description: Define your federation's committees and the features around them in config/committees.php
---

# Configuring Committees

A **committee** groups the licenses, certifications, membership plans, and professional roles
that belong to one area of a federation's activity. Committees are the platform's main
deployment-customization point: the default install ships a diving-federation example
(`SPORT`, `SCIENTIFIC`, `DIVING`, `DIVINGSERVICES`), but a deployment defines its **own**
committee set in [`config/committees.php`](https://github.com) and gets working license
purchase, licenses-attributed screens, validation queues, and sidebar menus **without editing
application code**.

> The committee *taxonomy* is the source of truth in config; `CommitteeSeeder` seeds it into the
> `committee` table, and `App\Models\Committee` / `App\Support\Committees` /
> `App\Support\CommitteeMenu` read from it at runtime. Re-run the seeder after changing the
> committee list.

## The committee list

Each entry in `committees.list` describes one committee:

```php
'list' => [
    [
        'code' => 'SPORT',                       // stable identifier used throughout the app
        'name' => 'Sport Committee',             // internal/admin display name
        'is_international' => false,              // shared through the international portal?
        'individual_display_name' => 'Underwater Sports', // optional public-facing label
        'slug' => 'sport',                       // URL/route base for this committee's screens
        'title_slug' => 'sport',                 // translation-key base for page titles
        // ... per-committee feature wiring (see below) ...
    ],
],
```

| Key | Required | Purpose |
|-----|----------|---------|
| `code` | yes | Stable identifier (uppercase). Used in routes, scopes, relations. Changing it is a data migration. |
| `name` | yes | Admin-facing display name. |
| `is_international` | yes | Drives the national/international visibility split (license/certification scopes). |
| `individual_display_name` | no | Public-facing label; falls back to `name`. |
| `slug` | for screens | URL and route-name base, e.g. `sport` → `entity.sport-licenses-attributed.index`. |
| `title_slug` | for screens | Translation-key base for page titles, e.g. `cmas_diving` → `licenses.federation_cmas_diving_entity_licenses_title`. |

### Related committees

Some committees' content is shown together. The `related` map adds extra committee codes
alongside a given one (for example, in the diving deployment a `DIVING` view also lists
`SCIENTIFIC` content):

```php
'related' => [
    'DIVING' => ['SCIENTIFIC'],
],
```

## Per-committee feature wiring

A committee can declare optional blocks that generate its features. Each is read by a small
helper, so adding the block to config is all that's needed.

### `purchase` — license-purchase pages and routes

Declares the purchase pages a committee exposes (`entity`, `members`, and/or `individual`).
The routing layer generates one named route per page and a single generic controller renders
it, deriving the international flag from the committee and the titles from the configured keys.

```php
'purchase' => [
    'entity' => [
        'slug'     => 'sport-license-purchase',          // route: entity.sport-license-purchase.index
        'title'    => 'licenses.Purchase Sport Club License',
        'subtitle' => 'licenses.Purchase a sport license for your club',
    ],
    'members' => [
        'slug'     => 'sport-member-license-purchase',
        'title'    => 'licenses.Purchase Sport Licenses',
        'subtitle' => 'licenses.Select members and purchase sport licenses on their behalf',
        // when a member purchase needs an entity license first, which committee's
        // entity-purchase page to redirect to (defaults to this committee):
        'entity_license_via' => 'DIVING',
    ],
    'individual' => [
        'slug'  => 'sport-license-purchase',             // route: individual.sport-license-purchase.index
        'title' => 'licenses.individual_sport_license_title',
        'subtitle' => 'licenses.individual_sport_license_subtitle',
    ],
    // A committee whose entity flow is a custom screen (e.g. a wizard) rather than a
    // generated page sets `entity_route` to that route name instead of an `entity` block.
    // 'entity_route' => 'entity.diving_licenses.request',
],
```

If a `title`/`subtitle` key is omitted, the page falls back to a generic, committee-label-driven
title (e.g. "Purchase :committee Entity License").

### `attributed` — licenses-attributed screens

Wires the licenses-attributed listing screens per portal (entity / federation / admin /
individual). Route names and most titles are derived by convention from `slug` / `title_slug`:

- entity portal: `entity.{slug}-licenses-attributed.index` (and `…-member-…` for members)
- federation / admin: `{portal}.{slug}-{holder}-licenses-attributed.index`
- individual portal: `individual.{slug}-licenses-attributed.index`

Federation/admin/individual titles use the `{portal}_{title_slug}_..._licenses_title`
translation keys. The entity portal's titles are irregular, so list them (and which holder
screens the entity portal exposes) explicitly:

```php
'attributed' => [
    'entity_portal' => [
        'entity'  => 'licenses.Sport Club Licenses',
        'members' => 'licenses.Sport Licenses',
    ],
],
```

### `menu` — sidebar section

Generates the committee's sidebar section (and its filtered child links) across portals from
config. Add or remove a committee here to add/remove its menu — the database menu is rebuilt
from config by `MenuSeeder` and stays admin-editable afterwards.

```php
'menu' => [
    'label' => 'menu.federation.sport',   // translation key (or literal); defaults to `name`
    'icon'  => 'flag',
    'order' => 4,
    'entities_label' => 'menu.federation.clubs',  // label for the entity-holder license link
    // individual-holder license breakdown by professional role:
    'professionals' => [
        'athlete'      => 'menu.federation.athletes',
        'coach'        => 'menu.federation.coaches',
        'refereejudge' => 'menu.federation.referees_judges',
    ],
],
```

### `instructor_role_code` — instructor certifications

The professional-role code whose certifications a committee's instructors issue, used by
`GetCertificationsFromInstructorAction`:

```php
'instructor_role_code' => 'DIVINGINSTRUCTOR',
```

## Adding a committee — checklist

1. Add the entry to `committees.list` (at minimum `code`, `name`, `is_international`; add `slug`
   / `title_slug` and the `purchase` / `attributed` / `menu` blocks for the features you want).
2. Add any translation keys you referenced to `lang/{locale}/licenses.php` and
   `lang/{locale}/menu.php` (or rely on the generic committee-label fallbacks).
3. Ensure the committee's `access {slug} menu` permission and any professional roles exist (see
   the role/permission seeders).
4. Re-seed: `php artisan migrate:fresh --seed` (dev) or `php artisan db:seed --class=CommitteeSeeder`
   then `php artisan db:seed --class=MenuSeeder` to rebuild the menu from config.

## What stays in code

The example **datasets** (the diving licenses, certifications, membership plans, and professional
roles in the seeders) reference committee codes intentionally — they are sample data, like the
`DIVING_CERTIFICATION_SYSTEMS` examples, and a deployment replaces them with its own. Likewise,
intrinsically domain-specific features (the public registries, the diving-license validation
workflow) are feature modules a deployment provides for itself; they are not generated from the
committee list.

## See also

- [Committee Structure](/architecture/02-committee-structure) — the committee model and
  federation-based access control
- [Getting Started](/guides/getting-started) — install and configuration
- [Creating a Plugin](/guides/creating-a-plugin) — extend the platform without forking core
