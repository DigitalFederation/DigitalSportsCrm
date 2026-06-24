<?php

namespace App\Plugins;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Base service provider for plugins.
 *
 * A plugin is an ordinary Composer package whose service provider extends this
 * class and is auto-discovered by Laravel (via the package's
 * composer.json "extra.laravel.providers"). Extending this base wires up the
 * usual Laravel resources by convention so a plugin author does not repeat the
 * boilerplate:
 *
 *   <plugin root>/
 *     routes/web.php          → loaded as web routes
 *     routes/api.php          → loaded as api routes (prefix "api")
 *     database/migrations/    → loaded as migrations
 *     resources/views/        → registered under the "<id>::" view namespace
 *     lang/                   → registered under the "<id>::" translation namespace
 *
 * Menu items and permissions are declared (not side-effecting) via menu() and
 * permissions(); they are written to the database once by `php artisan
 * plugins:sync`.
 *
 * Minimal plugin provider:
 *
 *   class DivingLogbookServiceProvider extends PluginServiceProvider
 *   {
 *       public function manifest(): PluginManifest
 *       {
 *           return new PluginManifest('diving-logbook', 'Diving Logbook', '1.0.0');
 *       }
 *   }
 */
abstract class PluginServiceProvider extends ServiceProvider
{
    abstract public function manifest(): PluginManifest;

    /**
     * Permissions this plugin needs. Created by `plugins:sync`.
     *
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * Menu items this plugin contributes. Materialised by `plugins:sync`.
     *
     * Each entry is an array understood by the sync command, e.g.:
     *   [
     *     'menu'        => 'individual',          // target menu machine_name
     *     'name'        => 'Logbook',
     *     'icon'        => 'book-open',
     *     'order'       => 15,
     *     'permissions' => ['access diving logbook'],
     *     'children'    => [
     *       ['name' => 'My dives', 'route_name' => 'plugin.diving-logbook.index', 'order' => 1],
     *     ],
     *   ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function menu(): array
    {
        return [];
    }

    /**
     * Absolute path to the plugin package root.
     *
     * Convention: the provider lives in "<root>/src/…"; override this method if
     * your package is laid out differently.
     */
    public function basePath(string $path = ''): string
    {
        $root = dirname((new ReflectionClass($this))->getFileName(), 2);

        return $path === '' ? $root : $root . '/' . ltrim($path, '/');
    }

    public function boot(): void
    {
        $id = $this->manifest()->id;

        if (is_file($web = $this->basePath('routes/web.php'))) {
            $this->loadRoutesFrom($web);
        }

        if (is_file($api = $this->basePath('routes/api.php'))) {
            $this->loadRoutesFrom($api);
        }

        if (is_dir($migrations = $this->basePath('database/migrations'))) {
            $this->loadMigrationsFrom($migrations);
        }

        if (is_dir($views = $this->basePath('resources/views'))) {
            $this->loadViewsFrom($views, $id);
        }

        if (is_dir($lang = $this->basePath('lang'))) {
            $this->loadTranslationsFrom($lang, $id);
        }

        app(PluginManager::class)->register($this);
    }
}
