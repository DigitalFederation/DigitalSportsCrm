<?php

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuBuilderService;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the "Legal Pages" sidebar entry to installations that already have a menu.
 *
 * Deliberately NOT done by re-running MenuSeeder: that seeder deletes every
 * MenuItem and rebuilds from config, which would discard any customization made
 * through the dynamic menu admin. A new settings screen is not a good enough reason
 * to reset an installation's navigation, so this migration is purely additive.
 *
 * Fresh installs get the same entry from config/menu.php via MenuSeeder; the
 * route_name guard below keeps the two paths from producing a duplicate.
 */
return new class extends Migration
{
    private const ROUTE = 'admin.legal-pages.index';

    public function up(): void
    {
        $menu = Menu::where('machine_name', 'admin')->first();

        // No admin menu yet (fresh install, migrates before seeding): MenuSeeder
        // will create the entry from config.
        if (! $menu || ! MenuItem::where('menu_id', $menu->id)->exists()) {
            return;
        }

        if (MenuItem::where('route_name', self::ROUTE)->exists()) {
            return;
        }

        $parentId = $this->resolveParentId($menu->id);

        $order = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $parentId)
            ->max('order');

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'name' => 'menu.admin.legal_pages',
            'icon' => $parentId ? null : 'document',
            'order' => (int) $order + 1,
            'route_name' => self::ROUTE,
            'route_parameters' => null,
            'active_patterns' => ['legal-pages'],
            'permissions' => ['access settings'],
            'visible' => true,
        ]);

        // Menus are cached for an hour per user; without this the new entry would
        // simply not appear after deploy, and the documented remedy
        // (db:seed --class=MenuSeeder) is the destructive rebuild this migration
        // exists to avoid.
        app(MenuBuilderService::class)->clearAllMenuCache();
    }

    /**
     * Find the settings/system section to nest under.
     *
     * Menu rows on a long-lived install are not guaranteed to match
     * config/menu.php: names may be literal strings rather than translation keys
     * (renamed through the menu admin, or seeded from an older config), so matching
     * on `menu.admin.settings` alone finds nothing on real deployments. Anchoring on
     * the *route* of a neighbouring settings screen is stable across renames.
     *
     * Returns null to place the item at the root of the admin menu, which is a
     * valid outcome — never a reason to fail the deploy.
     */
    private function resolveParentId(int $menuId): ?int
    {
        $byName = MenuItem::where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->where('name', 'menu.admin.settings')
            ->value('id');

        if ($byName) {
            return $byName;
        }

        $siblingRoutes = [
            'admin.homepage-settings.index',
            'admin.member-number-settings.index',
            'admin.districts.index',
            'admin.menu-management.index',
            'admin.role-management.index',
        ];

        foreach ($siblingRoutes as $route) {
            $parentId = MenuItem::where('menu_id', $menuId)
                ->where('route_name', $route)
                ->value('parent_id');

            if ($parentId) {
                return (int) $parentId;
            }
        }

        return null;
    }

    public function down(): void
    {
        MenuItem::where('route_name', self::ROUTE)->delete();

        app(MenuBuilderService::class)->clearAllMenuCache();
    }
};
