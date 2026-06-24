# Creating a Plugin

The core platform is a generic federation CRM. Domain-specific verticals — a
sponsors directory, a merchandise store, a sport-specific module — live
**outside** the main repository as **plugins** so the core stays small and reusable.

A plugin is an ordinary **Composer package**. Laravel auto-discovers its service
provider, so once it is installed it can add routes, migrations, views,
translations, menu items, and permissions without any change to the core code.

## TL;DR

```bash
# 1. Add the package (a path repo while developing, a VCS/Packagist repo in prod)
composer require acme/sponsors

# 2. Run its migrations
php artisan migrate

# 3. Materialise the permissions & menu items it declares
php artisan plugins:sync

# Inspect what is installed
php artisan plugins:list
```

Everything else is convention.

## Anatomy of a plugin

```
acme/sponsors/
├── composer.json
├── src/
│   ├── SponsorsServiceProvider.php   # extends PluginServiceProvider
│   └── …                                  # models, controllers, actions…
├── routes/
│   ├── web.php                            # auto-loaded as web routes
│   └── api.php                            # auto-loaded as api routes (prefix /api)
├── database/
│   └── migrations/                        # auto-loaded
├── resources/
│   └── views/                             # registered under "sponsors::"
└── lang/
    ├── en/…                               # registered under "sponsors::"
    └── pt/…
```

The directory layout is the contract. `PluginServiceProvider` (provided by the
core, in `App\Plugins`) wires each of these up automatically from the package
root, so a plugin author writes almost no boilerplate.

## 1. `composer.json`

```json
{
    "name": "acme/sponsors",
    "description": "Sponsors directory for Digital Sports CRM",
    "type": "library",
    "require": {
        "php": "^8.2"
    },
    "autoload": {
        "psr-4": {
            "Acme\\Sponsors\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Acme\\Sponsors\\SponsorsServiceProvider"
            ]
        }
    }
}
```

The `extra.laravel.providers` entry is what makes Laravel discover and boot the
provider automatically — no manual registration in `config/app.php`.

## 2. The service provider

Extend `App\Plugins\PluginServiceProvider` and return a manifest. That is the
minimum required to have a working plugin:

```php
<?php

namespace Acme\Sponsors;

use App\Plugins\PluginManifest;
use App\Plugins\PluginServiceProvider;

class SponsorsServiceProvider extends PluginServiceProvider
{
    public function manifest(): PluginManifest
    {
        return new PluginManifest(
            id: 'sponsors',
            name: 'Sponsors',
            version: '1.0.0',
            description: 'Manages a public directory of club and event sponsors.',
        );
    }
}
```

With just this, the base provider's `boot()` already:

- loads `routes/web.php` and `routes/api.php` (if present);
- loads migrations from `database/migrations/`;
- registers views under the `sponsors::` namespace
  (`view('sponsors::index')`);
- registers translations under the `sponsors::` namespace
  (`__('sponsors::messages.saved')`);
- registers the plugin in the `PluginManager` so `plugins:list` / `plugins:sync`
  can see it.

> **Package root resolution.** `basePath()` assumes the provider lives in
> `<root>/src/`. If your package is laid out differently, override `basePath()`.

## 3. Declaring permissions

Override `permissions()` to return the permission names the plugin needs. They
are created (idempotently) by `php artisan plugins:sync`:

```php
public function permissions(): array
{
    return [
        'access sponsors',
        'manage sponsors',
    ];
}
```

Use them like any core permission — in route middleware (`->middleware('can:access sponsors')`),
in policies, or as the `permissions` of a menu item (below).

## 4. Declaring menu items

The sidebar is database-driven. A plugin declares the items it wants and
`plugins:sync` writes them into the right menu, tagged as plugin-owned so a
re-sync rebuilds them cleanly (never duplicates).

```php
public function menu(): array
{
    return [
        [
            'menu'        => 'entity',              // target menu machine_name
            'name'        => 'Sponsors',
            'icon'        => 'building-office',
            'order'       => 15,
            'permissions' => ['access sponsors'],
            'children'    => [
                [
                    'name'           => 'All sponsors',
                    'route_name'     => 'plugin.sponsors.index',
                    'active_patterns' => ['sponsors'],
                    'order'          => 1,
                ],
            ],
        ],
    ];
}
```

Supported keys per item: `menu` (top level only), `name`, `icon`, `order`,
`route_name`, `route_parameters`, `active_patterns`, `permissions`,
`selected_roles`, `visible`, and `children`. The available menu machine names
are the core menus (`individual`, `entity`, `federation`, `admin`, …).

## 5. Routes, controllers, views

Namespace your routes so they never collide with core (convention:
`plugin.<id>.*`):

```php
// routes/web.php
use Acme\Sponsors\Http\Controllers\SponsorsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('plugins/sponsors')
    ->group(function () {
        Route::get('/', [SponsorsController::class, 'index'])
            ->name('plugin.sponsors.index');
    });
```

Reference plugin views and translations through the plugin namespace:

```php
return view('sponsors::index', [
    'title' => __('sponsors::messages.title'),
]);
```

## 6. Installing during development

Use a Composer **path repository** so you can develop the plugin alongside the
app without publishing it. In the app's `composer.json`:

```json
{
    "repositories": [
        { "type": "path", "url": "../plugins/sponsors" }
    ]
}
```

```bash
composer require acme/sponsors:@dev
php artisan migrate
php artisan plugins:sync
```

In production, point a `vcs` repository at the plugin's Git repo (or publish it
to a private Packagist) and `composer require acme/sponsors:^1.0`.

## 7. Updating and removing

- **After any install/update:** run `php artisan migrate` then
  `php artisan plugins:sync`. The sync is safe to run repeatedly.
- **To remove a plugin:** `composer remove acme/sponsors`, then
  `php artisan plugins:sync` (its menu items disappear because they are no longer
  declared) and roll back its migrations if you want its tables gone.

## Console commands reference

| Command | What it does |
|---------|--------------|
| `php artisan plugins:list` | Lists installed, auto-discovered plugins. |
| `php artisan plugins:sync` | Creates declared permissions and rebuilds plugin-owned menu items. Idempotent. |

## Conventions checklist

- Package autoloads under a vendor namespace (`Acme\Sponsors\`), not `App\`
  or `Domain\`.
- Provider extends `App\Plugins\PluginServiceProvider` and is listed in
  `extra.laravel.providers`.
- Route names and view/lang namespaces are prefixed with the plugin id to avoid
  clashes with core or other plugins.
- The plugin owns its own tables; it does not modify core tables in place.
- Permissions and menu items are **declared** (via `permissions()` / `menu()`),
  never written directly — let `plugins:sync` materialise them.
