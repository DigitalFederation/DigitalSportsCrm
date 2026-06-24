<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Permission;
use App\Plugins\PluginManager;
use App\Services\MenuBuilderService;
use Domain\Menus\Actions\CreateMenuItemWithPermissionsAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Materialise the permissions and menu items declared by installed plugins.
 *
 * Run this once after installing or updating a plugin (after `composer require`
 * and `php artisan migrate`). It is idempotent: plugin-owned menu items are
 * tagged with metadata->plugin and rebuilt from scratch on every run, and
 * permissions use firstOrCreate, so re-running never duplicates anything.
 */
class PluginsSyncCommand extends Command
{
    protected $signature = 'plugins:sync';

    protected $description = 'Sync permissions and menu items declared by installed plugins';

    public function handle(PluginManager $plugins): int
    {
        $this->syncPermissions($plugins);
        $this->syncMenu($plugins);

        app(MenuBuilderService::class)->clearAllMenuCache();

        $this->info('Plugins synced.');

        return self::SUCCESS;
    }

    protected function syncPermissions(PluginManager $plugins): void
    {
        $permissions = $plugins->permissions();

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $this->line(sprintf('Permissions: %d ensured.', count($permissions)));
    }

    protected function syncMenu(PluginManager $plugins): void
    {
        DB::transaction(function () use ($plugins) {
            // Remove anything a plugin created previously, then rebuild — this is
            // what makes re-running safe.
            MenuItem::whereNotNull('metadata->plugin')->get()->each->delete();

            foreach ($plugins->menu() as $definition) {
                $menu = Menu::findByMachineName($definition['menu'] ?? '');

                if (! $menu) {
                    $this->warn(sprintf(
                        "Skipped menu items for plugin '%s': menu '%s' not found.",
                        $definition['plugin'] ?? '?',
                        $definition['menu'] ?? '?',
                    ));

                    continue;
                }

                $this->createItem($definition, $menu->id, null);
            }
        });

        $this->line(sprintf('Menu items: %d top-level definitions synced.', count($plugins->menu())));
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    protected function createItem(array $definition, int $menuId, ?int $parentId): void
    {
        $item = CreateMenuItemWithPermissionsAction::execute([
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'name' => $definition['name'],
            'icon' => $definition['icon'] ?? null,
            'order' => $definition['order'] ?? 0,
            'route_name' => $definition['route_name'] ?? null,
            'route_parameters' => $definition['route_parameters'] ?? [],
            'active_patterns' => $definition['active_patterns'] ?? [],
            'permissions' => $definition['permissions'] ?? [],
            'selected_roles' => $definition['selected_roles'] ?? [],
            'visible' => $definition['visible'] ?? true,
            'metadata' => ['plugin' => $definition['plugin']],
        ]);

        foreach ($definition['children'] ?? [] as $child) {
            $child['plugin'] = $definition['plugin'];
            $this->createItem($child, $menuId, $item->id);
        }
    }
}
