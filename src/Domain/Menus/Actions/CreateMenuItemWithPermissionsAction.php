<?php

namespace Domain\Menus\Actions;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Permission;
use Domain\Permissions\Actions\AssignPermissionToMenuItemAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CreateMenuItemWithPermissionsAction
{
    /**
     * Create a new menu item with permissions
     */
    public static function execute(array $data): MenuItem
    {
        // Extract permissions before creating menu item
        $permissions = Arr::pull($data, 'permissions', []);

        // Ensure permissions is an array
        if (! is_array($permissions) && ! ($permissions instanceof Collection)) {
            $permissions = [$permissions];
        }

        // Validate menu exists
        if (isset($data['menu_id'])) {
            $menu = Menu::findOrFail($data['menu_id']);
        }

        // Validate parent menu item exists if provided
        if (isset($data['parent_id']) && $data['parent_id']) {
            MenuItem::findOrFail($data['parent_id']);
        }

        // Create the menu item without permissions first
        $menuItem = MenuItem::create($data);

        // Assign permissions if provided
        if (! empty($permissions)) {
            AssignPermissionToMenuItemAction::execute($menuItem, $permissions);
        }

        // Clear menu cache
        if ($menuItem->menu) {
            $menuItem->menu->clearCache();
        }

        return $menuItem->fresh();
    }

    /**
     * Create menu item from route with automatic permission detection
     */
    public static function createFromRoute(string $routeName, array $additionalData = []): MenuItem
    {
        // Try to detect permissions from route
        $route = app('router')->getRoutes()->getByName($routeName);
        $permissions = [];

        if ($route) {
            // Extract permissions from route middleware
            $middleware = $route->gatherMiddleware();
            foreach ($middleware as $mw) {
                if (str_starts_with($mw, 'permission:')) {
                    $permission = str_replace('permission:', '', $mw);
                    // Handle multiple permissions separated by |
                    $permissions = array_merge($permissions, explode('|', $permission));
                } elseif (str_starts_with($mw, 'can:')) {
                    $permission = str_replace('can:', '', $mw);
                    $permissions[] = $permission;
                }
            }
        }

        // Build menu item data
        $data = array_merge([
            'route_name' => $routeName,
            'permissions' => array_unique($permissions),
            'active_patterns' => [parse_url(route($routeName), PHP_URL_PATH)],
        ], $additionalData);

        return self::execute($data);
    }

    /**
     * Bulk create menu items with permissions
     *
     * @param  array  $items  Array of menu item data
     * @param  int|null  $menuId  Default menu ID for all items
     * @param  int|null  $parentId  Default parent ID for all items
     */
    public static function bulkCreate(array $items, ?int $menuId = null, ?int $parentId = null): Collection
    {
        $createdItems = collect();

        foreach ($items as $itemData) {
            // Apply defaults if not set
            if ($menuId && ! isset($itemData['menu_id'])) {
                $itemData['menu_id'] = $menuId;
            }
            if ($parentId && ! isset($itemData['parent_id'])) {
                $itemData['parent_id'] = $parentId;
            }

            $createdItems->push(self::execute($itemData));
        }

        // Clear menu cache once after all items created
        if ($menuId) {
            Menu::find($menuId)?->clearCache();
        }

        return $createdItems;
    }

    /**
     * Create a menu item hierarchy with permissions
     *
     * @param  array  $data  Parent menu item data
     * @param  array  $children  Array of child menu items data
     */
    public static function createWithChildren(array $data, array $children = []): MenuItem
    {
        // Create parent item
        $parent = self::execute($data);

        // Create children
        if (! empty($children)) {
            self::bulkCreate($children, $parent->menu_id, $parent->id);
        }

        return $parent->fresh()->load('children');
    }
}
