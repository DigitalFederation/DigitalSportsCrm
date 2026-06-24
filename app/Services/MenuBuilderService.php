<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MenuBuilderService
{
    protected const CACHE_TTL = 3600; // 1 hour
    protected const CACHE_PREFIX = 'menu.structure.';

    /**
     * Build menu structure for a given menu machine name
     */
    public function build(string $machineName, array $options = []): Collection
    {
        $cacheKey = $this->getCacheKey($machineName, $options);
        $invalidationKey = 'menu.invalidation.' . $machineName;

        // Check if cache was invalidated
        $lastInvalidation = Cache::get($invalidationKey, 0);
        $cacheTime = Cache::get($cacheKey . '.time', 0);

        // If cache was invalidated after it was stored, rebuild it
        if ($lastInvalidation > $cacheTime) {
            Cache::forget($cacheKey);
        }

        // Additional check for file cache timestamp
        if (config('cache.default') === 'file') {
            $timestampFile = storage_path('framework/cache/menu_timestamps/' . $machineName);
            if (file_exists($timestampFile)) {
                $fileTime = filemtime($timestampFile);
                if ($fileTime > $cacheTime) {
                    Cache::forget($cacheKey);
                }
            }
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($machineName, $options, $cacheKey) {
            // Store the cache time
            Cache::put($cacheKey . '.time', time(), self::CACHE_TTL);

            return $this->buildMenuStructure($machineName, $options);
        });
    }

    /**
     * Build menu for a specific group
     */
    public function buildForGroup(string $machineName, ?int $groupId = null, array $options = []): Collection
    {
        $options['group_id'] = $groupId;

        return $this->build($machineName, $options);
    }

    /**
     * Build menu structure without caching (for admin purposes)
     */
    public function buildFresh(string $machineName, array $options = []): Collection
    {
        return $this->buildMenuStructure($machineName, $options);
    }

    /**
     * Clear cache for a specific menu
     */
    public function clearCache(string $machineName): void
    {
        // Store a flag to indicate this menu's cache should be refreshed
        $invalidationKey = 'menu.invalidation.' . $machineName;
        Cache::put($invalidationKey, time(), now()->addHours(1));

        // Clear the basic cache
        Cache::forget(self::CACHE_PREFIX . $machineName);

        // Clear all possible cache key variations
        $patterns = [
            self::CACHE_PREFIX . $machineName,
            self::CACHE_PREFIX . $machineName . '.*',
            $this->getCacheKey($machineName, []),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Clear menu items cache for this menu
        $menuItems = MenuItem::whereHas('menu', function ($query) use ($machineName) {
            $query->where('machine_name', $machineName);
        })->pluck('id');

        foreach ($menuItems as $itemId) {
            Cache::forget("menu.item.{$itemId}");
        }

        // Force clear file cache by touching a timestamp file
        if (config('cache.default') === 'file') {
            $timestampFile = storage_path('framework/cache/menu_timestamps/' . $machineName);
            if (! file_exists(dirname($timestampFile))) {
                mkdir(dirname($timestampFile), 0755, true);
            }
            touch($timestampFile);
        }
    }

    /**
     * Clear cache for all menus
     */
    public function clearAllMenuCache(): void
    {
        // Get all menu machine names
        $menuNames = Menu::pluck('machine_name');

        foreach ($menuNames as $machineName) {
            $this->clearCache($machineName);
        }

        // Clear feature flag cache as well since menu changes might affect flags
        FeatureFlagService::clearCache();
    }

    /**
     * Get filtered menu items for a user
     *
     * @param  mixed  $user
     */
    public function buildForUser(string $machineName, $user = null, array $options = []): Collection
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return collect();
        }

        $options['user_id'] = $user->id;
        $menuItems = $this->build($machineName, $options);

        return $this->filterMenuItemsForUser($menuItems, $user);
    }

    /**
     * Build the actual menu structure
     */
    protected function buildMenuStructure(string $machineName, array $options = []): Collection
    {
        try {
            $menu = Menu::findByMachineName($machineName);

            if (! $menu) {
                Log::warning("Menu not found: {$machineName}");

                return collect();
            }

            // Get all menu items for this menu
            $query = MenuItem::where('menu_id', $menu->id)
                ->visible()
                ->with(['children' => function ($q) {
                    $q->visible()->orderBy('order');
                }])
                ->orderBy('order');

            // Apply committee filter if specified
            if (isset($options['committee_id'])) {
                $query->forCommittee($options['committee_id']);
            }

            // Apply group filter if specified
            if (isset($options['group_id'])) {
                $query->inGroup($options['group_id']);
            }

            $allItems = $query->get();

            // Build hierarchical structure
            return $this->buildHierarchy($allItems);
        } catch (\Exception $e) {
            Log::error('Error building menu structure', [
                'menu' => $machineName,
                'options' => $options,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return collect();
        }
    }

    /**
     * Build hierarchical menu structure from flat collection
     */
    protected function buildHierarchy(Collection $items): Collection
    {
        // Group items by parent_id
        $grouped = $items->groupBy('parent_id');

        // Get root items (parent_id is null)
        $rootItems = $grouped->get(null, collect());

        // Recursively attach children
        return $rootItems->map(function ($item) use ($grouped) {
            return $this->attachChildren($item, $grouped);
        });
    }

    /**
     * Recursively attach children to menu items
     */
    protected function attachChildren(MenuItem $item, Collection $grouped): MenuItem
    {
        // Always set `children` (even when empty) so every node carries it
        // explicitly. filterMenuItemsForUser() and the menu views then never need
        // to lazy-load the relation, which throws under preventLazyLoading.
        $item->children = $grouped->get($item->id, collect())
            ->map(function ($child) use ($grouped) {
                return $this->attachChildren($child, $grouped);
            });

        return $item;
    }

    /**
     * Filter menu items based on user permissions
     *
     * @param  mixed  $user
     */
    protected function filterMenuItemsForUser(Collection $menuItems, $user): Collection
    {
        return $menuItems->filter(function ($item) use ($user) {
            // Check if user can access this item
            if (! $this->userCanAccessItem($item, $user)) {
                return false;
            }

            // Recursively filter children, resolving them WITHOUT triggering a lazy
            // load (attachChildren stores them as an attribute; structures cached
            // before this fix may carry them as an eager-loaded relation instead).
            $children = $this->resolveChildrenWithoutLazyLoad($item);
            if ($children instanceof Collection) {
                $children = $this->filterMenuItemsForUser($children, $user);
                $item->children = $children;

                // If item has no children and no route, hide it
                if ($children->isEmpty() && ! $item->route_name) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Resolve an item's already-known children without triggering a lazy load.
     *
     * attachChildren() stores children as a plain attribute; structures cached
     * before that change may carry them as an eager-loaded relation. Returns null
     * when neither is present so the caller skips recursion instead of lazy-loading
     * the relation (which throws under preventLazyLoading).
     */
    protected function resolveChildrenWithoutLazyLoad(MenuItem $item): ?Collection
    {
        $children = array_key_exists('children', $item->getAttributes())
            ? $item->getAttributes()['children']
            : ($item->relationLoaded('children') ? $item->getRelation('children') : null);

        return $children instanceof Collection ? $children : null;
    }

    /**
     * Check if user can access a specific menu item
     *
     * @param  mixed  $user
     */
    protected function userCanAccessItem(MenuItem $item, $user): bool
    {

        // Allow access for users with admin roles or manage_menus permission
        if ($user->can('manage_menus') || $user->hasRole(['admin', 'super_admin', 'cmas_admin'])) {
            return $item->visible;
        }

        // Check basic visibility
        if (! $item->visible) {
            return false;
        }

        // Check visibility conditions
        if (! $item->evaluateVisibilityConditions()) {
            return false;
        }

        // Check selected_roles first (simpler, role-based access control)
        if ($item->selected_roles && ! empty($item->selected_roles)) {
            return $user->hasAnyRole($item->selected_roles);
        }

        // Fallback to permissions check (legacy behavior)
        if ($item->permissions && ! empty($item->permissions)) {
            foreach ($item->permissions as $permission) {
                if ($user->can($permission)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Generate cache key for menu
     */
    protected function getCacheKey(string $machineName, array $options = []): string
    {
        $key = self::CACHE_PREFIX . $machineName;

        if (! empty($options)) {
            $key .= '.' . md5(serialize($options));
        }

        return $key;
    }

    /**
     * Get menu item by ID with caching
     */
    public function getMenuItem(int $itemId): ?MenuItem
    {
        $cacheKey = "menu.item.{$itemId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($itemId) {
            return MenuItem::with(['menu', 'parent', 'children', 'committee'])->find($itemId);
        });
    }

    /**
     * Validate menu structure for a given menu
     */
    public function validateMenuStructure(string $machineName): array
    {
        $errors = [];

        try {
            $menu = Menu::findByMachineName($machineName);

            if (! $menu) {
                return ['Menu not found'];
            }

            $items = MenuItem::where('menu_id', $menu->id)->get();

            foreach ($items as $item) {
                // Validate route exists
                if ($item->route_name && ! $item->validateRoute()) {
                    $errors[] = "Item '{$item->name}' has invalid route: {$item->route_name}";
                }

                // Validate parent exists
                if ($item->parent_id && ! $items->contains('id', $item->parent_id)) {
                    $errors[] = "Item '{$item->name}' has invalid parent_id: {$item->parent_id}";
                }

                // Validate permissions exist
                if ($item->permissions) {
                    foreach ($item->permissions as $permission) {
                        if (! \Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
                            $errors[] = "Item '{$item->name}' has invalid permission: {$permission}";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Validation error: ' . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Get all available menus
     */
    public function getAllMenus(): Collection
    {
        return Menu::active()->orderBy('name')->get();
    }

    /**
     * Rebuild all menu caches
     */
    public function rebuildAllCaches(): void
    {
        $menus = $this->getAllMenus();

        foreach ($menus as $menu) {
            $this->clearCache($menu->machine_name);
            $this->build($menu->machine_name);
        }
    }

    /**
     * Get active groups for a menu
     */
    public function getMenuGroups(string $machineName, $user = null): Collection
    {
        $menu = Menu::findByMachineName($machineName);

        if (! $menu) {
            return collect();
        }

        // Get active groups that the user can access
        return $menu->activeGroups()
            ->accessibleBy($user)
            ->get();
    }

    /**
     * Get default group for a menu
     */
    public function getDefaultGroup(string $machineName): ?MenuGroup
    {
        $menu = Menu::findByMachineName($machineName);

        if (! $menu) {
            return null;
        }

        return MenuGroup::getDefaultForMenu($menu->id);
    }
}
