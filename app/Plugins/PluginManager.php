<?php

namespace App\Plugins;

/**
 * Registry of installed plugins.
 *
 * Each plugin registers itself here from its service provider's boot(). The
 * registry is the single place the rest of the app (and the plugins:* console
 * commands) looks to discover what is installed, which permissions plugins
 * declare, and which menu items they contribute.
 *
 * Nothing here writes to the database or renders anything at request time —
 * menu items and permissions are materialised once, on install, by
 * `php artisan plugins:sync`. This keeps the hot render path untouched.
 */
class PluginManager
{
    /** @var array<string, PluginServiceProvider> */
    protected array $plugins = [];

    public function register(PluginServiceProvider $plugin): void
    {
        $this->plugins[$plugin->manifest()->id] = $plugin;
    }

    /**
     * @return array<string, PluginServiceProvider>
     */
    public function all(): array
    {
        return $this->plugins;
    }

    public function get(string $id): ?PluginServiceProvider
    {
        return $this->plugins[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->plugins[$id]);
    }

    /**
     * Every permission declared by every installed plugin (de-duplicated).
     *
     * @return array<int, string>
     */
    public function permissions(): array
    {
        $permissions = [];

        foreach ($this->plugins as $plugin) {
            $permissions = array_merge($permissions, $plugin->permissions());
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Menu definitions declared by installed plugins, each tagged with the
     * owning plugin id so `plugins:sync` can re-sync idempotently.
     *
     * @return array<int, array<string, mixed>>
     */
    public function menu(): array
    {
        $items = [];

        foreach ($this->plugins as $plugin) {
            foreach ($plugin->menu() as $definition) {
                $definition['plugin'] = $plugin->manifest()->id;
                $items[] = $definition;
            }
        }

        return $items;
    }
}
