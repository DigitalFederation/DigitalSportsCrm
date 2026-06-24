---
title: Navigation & Menus
description: How the sidebar menu works, where it comes from, and how to customize it
---

# Navigation & Menus

Each user group (admin, federation, entity, individual) has its own sidebar menu. The menu is
**stored in the database** so it can be managed per deployment, but its content is **sourced
from configuration** so a fresh install — and any committee you add — gets a correct sidebar
without hand-editing the database.

## How it fits together

```
config/committees.php ─┐
                       ├─► config/menu.php ──► MenuSeeder ──► menu_items table ──► sidebar
App\Support\CommitteeMenu ┘   (per-portal       (route-validated,    (admin-editable)
  (generates committee          menu trees)      committee_id set)
   sections from committees)
```

- **`config/menu.php`** defines each portal's menu tree (items, icons, routes, `active`
  patterns, and `can` permissions). The per-committee sections and child links are **not
  hardcoded** there — they are generated from `config/committees.php` via
  `App\Support\CommitteeMenu` (see [Configuring Committees](/guides/configuring-committees)).
- **`MenuSeeder`** walks `config('menu')` and writes it into the `menu_items` table. It is
  **route-validated** — links whose route does not exist in the install are skipped (and
  reported) — and it resolves committee codes to `committee_id` for committee-aware
  highlighting. Item `name`s are translation keys, resolved through `__()` at render time.
- The rendered sidebar reads from the database, so an admin can adjust it after seeding.

## Database menu vs. config menu

A feature flag selects which menu the app renders (`config/features.php`):

| `DYNAMIC_MENU_ENABLED` | Behaviour |
|------------------------|-----------|
| `true` (default in `.env.example`) | The **database** menu is rendered (seeded by `MenuSeeder` from config, then admin-editable). Per-portal flags `DYNAMIC_MENU_ADMIN` / `_FEDERATION` / `_ENTITY` / `_INDIVIDUAL` enable it per group. |
| `false` | The app renders **`config/menu.php` directly** (no database menu). |

Recommended setup is the default (database menu) so the menu can be managed in-app.

## Customizing the sidebar

You generally edit **configuration, then re-seed** (the database menu is rebuilt from config):

- **Add/remove a committee's section** → edit its `menu` block in `config/committees.php`
  (label, icon, order, professional-role breakdown). See
  [Configuring Committees](/guides/configuring-committees).
- **Add/change a non-committee item** (dashboard, events, files, payments, …) → edit the
  relevant portal array in `config/menu.php`. Each item supports:
  - `name` — a translation key (define it in `lang/{locale}/menu.php`)
  - `icon`, `route` (a route name, or `[name, params]`), `active` (URL patterns for highlighting)
  - `can` — a permission, and `children` for nested items
- **Apply changes**:

  ```bash
  php artisan db:seed --class=MenuSeeder       # rebuild the database menu from config
  # or, in development:
  php artisan migrate:fresh --seed
  ```

  Re-seeding rebuilds the menu from config; any in-database admin edits are replaced, so make
  structural changes in config and use the in-app editor only for deployment-specific tweaks.

Only links to routes that exist are kept, so removing a feature's routes automatically prunes
its menu entries on the next seed.

## See also

- [Configuring Committees](/guides/configuring-committees) — committee sidebar sections
- [Access Control](/access-control/permission-management) — the `can` permissions used to gate items
- [Getting Started](/guides/getting-started) — install and configuration
