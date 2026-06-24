<?php

namespace App\Livewire\Admin;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Services\MenuBuilderService;
use Domain\Menus\Actions\CreateMenuGroupAction;
use Domain\Menus\Actions\DeleteMenuGroupAction;
use Domain\Menus\Actions\UpdateMenuGroupAction;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenuManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $selectedMenuType = null;
    public ?int $selectedMenuId = null;
    public ?int $editingItemId = null;
    public bool $showItemModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingItemId = null;
    public bool $reorderMode = false;
    public bool $showCacheInfo = false;

    // Tab management
    public string $activeTab = 'items'; // 'items' or 'groups'

    // Group management
    public bool $showGroupModal = false;
    public ?int $editingGroupId = null;
    public bool $showDeleteGroupModal = false;
    public ?int $deletingGroupId = null;

    // Group form fields
    public string $groupName = '';
    public string $groupMachineName = '';
    public string $groupDescription = '';
    public string $groupIcon = '';
    public int $groupOrder = 0;
    public bool $groupIsDefault = false;
    public bool $groupActive = true;
    public string $groupVisibilityType = 'all';
    public array $groupRequiredRoles = [];

    // Form fields for menu item
    public string $name = '';
    public string $icon = '';
    public ?string $route_name = null;
    public array $route_parameters = [];
    public array $permissions = [];
    public array $active_patterns = [];
    public bool $visible = true;
    public int $order = 0;
    public ?int $parent_id = null;
    public ?int $committee_id = null;
    public ?int $menu_group_id = null;

    // Role-based selection
    public bool $useRoleSelection = true; // Default to role selection for simplicity
    public array $selectedRoles = [];

    // Route parameters input as JSON string
    public string $route_parameters_json = '';
    public string $active_patterns_text = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedMenuType' => ['except' => null],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'icon' => 'nullable|string|max:255',
        'route_name' => 'nullable|string|max:255',
        'route_parameters_json' => 'nullable|string',
        'permissions' => 'array',
        'active_patterns_text' => 'nullable|string',
        'visible' => 'boolean',
        'order' => 'integer|min:0',
        'parent_id' => 'nullable|integer|exists:menu_items,id',
        'committee_id' => 'nullable|integer',
    ];

    public function mount()
    {
        // Check if user has permission to manage menus
        if (! auth()->user()->can('manage_menus')) {
            abort(403, __('menu.dynamic.admin.access_denied'));
        }

        // Set default menu type if available
        $availableMenus = $this->getAvailableMenus();
        if ($availableMenus->isNotEmpty() && ! $this->selectedMenuType) {
            $this->selectedMenuType = $availableMenus->first()->machine_name;
            $this->selectedMenuId = $availableMenus->first()->id;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedMenuType()
    {
        $menu = Menu::where('machine_name', $this->selectedMenuType)->first();
        $this->selectedMenuId = $menu?->id;
        $this->resetPage();
    }

    public function updatedReorderMode()
    {
        if ($this->reorderMode) {
            // Dispatch browser event to reinitialize sortable after DOM update
            $this->dispatch('sortable-init');
        }
    }

    public function render()
    {
        $availableMenus = $this->getAvailableMenus();
        $menuItems = $this->getMenuItems();
        $menuGroups = $this->getMenuGroups();
        $allPermissions = Permission::orderBy('category')->orderBy('name')->get();
        $groupedPermissions = $allPermissions->groupBy('category');
        $availableRoutes = $this->getAvailableRoutes();
        $parentItems = $this->getParentItems();
        $availableGroups = $this->getAvailableGroups();

        return view('livewire.admin.menu-management', [
            'availableMenus' => $availableMenus,
            'menuItems' => $menuItems,
            'menuGroups' => $menuGroups,
            'allPermissions' => $allPermissions,
            'groupedPermissions' => $groupedPermissions,
            'allRoles' => Role::all(),
            'availableRoutes' => $availableRoutes,
            'parentItems' => $parentItems,
            'availableGroups' => $availableGroups,
        ]);
    }

    public function addItem()
    {
        $this->resetForm();
        $this->showItemModal = true;
    }

    public function editItem(int $itemId)
    {
        $item = MenuItem::findOrFail($itemId);

        $this->editingItemId = $itemId;
        $this->name = $item->name;
        $this->icon = $item->icon ?? '';
        $this->route_name = $item->route_name;
        $this->route_parameters_json = json_encode($item->route_parameters ?? []);
        $this->permissions = $item->permissions ?? [];
        $this->active_patterns_text = implode(', ', $item->active_patterns ?? []);
        $this->visible = $item->visible;
        $this->order = $item->order;
        $this->parent_id = $item->parent_id;
        $this->committee_id = $item->committee_id;
        $this->menu_group_id = $item->menu_group_id;

        // Load selected roles directly from the item (if they were saved)
        // Otherwise fall back to reverse-engineering from permissions
        if (! empty($item->selected_roles)) {
            $this->selectedRoles = $item->selected_roles;
            $this->useRoleSelection = true;
        } elseif (! empty($item->permissions)) {
            // Fallback for items created before selected_roles was added
            $this->loadRolesFromPermissions();
            $this->useRoleSelection = ! empty($this->selectedRoles);
        } else {
            $this->selectedRoles = [];
            $this->useRoleSelection = true;
        }

        $this->showItemModal = true;
    }

    public function saveItem()
    {
        $this->validate();

        // Validate icon if provided
        if (! empty($this->icon) && ! $this->isValidHeroicon($this->icon)) {
            $this->addError('icon', __('menu.dynamic.admin.invalid_icon', ['icon' => $this->icon]));

            return;
        }

        // Parse JSON fields
        $routeParameters = [];
        if (! empty($this->route_parameters_json)) {
            $routeParameters = json_decode($this->route_parameters_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('route_parameters_json', __('menu.dynamic.admin.invalid_json'));

                return;
            }
        }

        $activePatterns = [];
        if (! empty($this->active_patterns_text)) {
            $activePatterns = array_map('trim', explode(',', $this->active_patterns_text));
        }

        // When using role selection, use selected_roles directly (not converted to permissions)
        // This provides simpler, more predictable access control
        $finalPermissions = [];
        $finalSelectedRoles = null;

        if ($this->useRoleSelection) {
            // Role-based access: store roles, clear permissions
            $finalSelectedRoles = ! empty($this->selectedRoles) ? $this->selectedRoles : null;
            $finalPermissions = [];
        } else {
            // Permission-based access: store permissions, clear roles
            $finalPermissions = $this->permissions;
            $finalSelectedRoles = null;
        }

        $data = [
            'menu_id' => $this->selectedMenuId,
            'name' => $this->name,
            'icon' => $this->icon ?: null,
            'route_name' => $this->route_name ?: null,
            'route_parameters' => $routeParameters,
            'permissions' => $finalPermissions,
            'selected_roles' => $finalSelectedRoles,
            'active_patterns' => $activePatterns,
            'visible' => $this->visible,
            'order' => $this->order,
            'parent_id' => $this->parent_id ?: null,
            'committee_id' => $this->committee_id ?: null,
            'menu_group_id' => $this->menu_group_id ?: null,
        ];

        if ($this->editingItemId) {
            MenuItem::findOrFail($this->editingItemId)->update($data);
            session()->flash('success', __('menu.dynamic.admin.item_updated'));
        } else {
            MenuItem::create($data);
            session()->flash('success', __('menu.dynamic.admin.item_created'));
        }

        // Clear menu cache
        app(MenuBuilderService::class)->clearAllMenuCache();

        $this->closeModal();
    }

    public function confirmDelete(int $itemId)
    {
        $this->deletingItemId = $itemId;
        $this->showDeleteModal = true;
    }

    public function deleteItem()
    {
        if ($this->deletingItemId) {
            $item = MenuItem::findOrFail($this->deletingItemId);

            // Check if item has children
            if ($item->children()->count() > 0) {
                session()->flash('error', __('menu.dynamic.admin.cannot_delete_parent'));
                $this->closeDeleteModal();

                return;
            }

            $item->delete();

            // Clear menu cache
            app(MenuBuilderService::class)->clearAllMenuCache();

            session()->flash('success', __('menu.dynamic.admin.item_deleted'));
        }

        $this->closeDeleteModal();
    }

    public function updateOrder(array $orderedIds)
    {
        foreach ($orderedIds as $index => $id) {
            MenuItem::where('id', $id)->update(['order' => $index]);
        }

        // Clear menu cache
        app(MenuBuilderService::class)->clearAllMenuCache();

        session()->flash('success', __('menu.dynamic.admin.order_updated'));
    }

    public function moveUp(int $itemId)
    {
        $item = MenuItem::findOrFail($itemId);
        $menuItems = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->orderBy('order')
            ->get();

        $currentIndex = $menuItems->search(function ($menuItem) use ($itemId) {
            return $menuItem->id === $itemId;
        });

        if ($currentIndex > 0) {
            // Swap with previous item
            $previousItem = $menuItems[$currentIndex - 1];
            $currentOrder = $item->order;

            $item->update(['order' => $previousItem->order]);
            $previousItem->update(['order' => $currentOrder]);

            // Clear menu cache
            app(MenuBuilderService::class)->clearAllMenuCache();
        }
    }

    public function moveDown(int $itemId)
    {
        $item = MenuItem::findOrFail($itemId);
        $menuItems = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->orderBy('order')
            ->get();

        $currentIndex = $menuItems->search(function ($menuItem) use ($itemId) {
            return $menuItem->id === $itemId;
        });

        if ($currentIndex < $menuItems->count() - 1) {
            // Swap with next item
            $nextItem = $menuItems[$currentIndex + 1];
            $currentOrder = $item->order;

            $item->update(['order' => $nextItem->order]);
            $nextItem->update(['order' => $currentOrder]);

            // Clear menu cache
            app(MenuBuilderService::class)->clearAllMenuCache();
        }
    }

    public function closeModal()
    {
        $this->showItemModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingItemId = null;
    }

    private function resetForm()
    {
        $this->editingItemId = null;
        $this->name = '';
        $this->icon = '';
        $this->route_name = null;
        $this->route_parameters_json = '';
        $this->permissions = [];
        $this->active_patterns_text = '';
        $this->visible = true;
        $this->order = 0;
        $this->parent_id = null;
        $this->committee_id = null;
        $this->menu_group_id = null;
        $this->selectedRoles = [];
        $this->useRoleSelection = true;
        $this->resetErrorBag();
    }

    private function getAvailableMenus(): Collection
    {
        return Menu::active()->orderBy('name')->get();
    }

    private function getMenuItems()
    {
        if (! $this->selectedMenuId) {
            return collect();
        }

        $query = MenuItem::where('menu_id', $this->selectedMenuId);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('route_name', 'like', '%' . $this->search . '%');
            });
        }

        // Improve ordering: group by parent first, then by order
        return $query->with(['parent', 'children'])
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate($this->reorderMode ? 50 : 20);
    }

    private function getAvailableRoutes(): array
    {
        $routes = [];
        foreach (app('router')->getRoutes() as $route) {
            if ($route->getName()) {
                // Only include routes that are likely to be used in menus
                $routeName = $route->getName();

                // Filter out API routes, debugbar, etc.
                if (! str_starts_with($routeName, 'debugbar.') &&
                    ! str_starts_with($routeName, 'horizon.') &&
                    ! str_starts_with($routeName, 'api.') &&
                    ! str_contains($routeName, '.api.')) {

                    // Verify the route can be generated (no required parameters)
                    try {
                        route($routeName);
                        $routes[] = $routeName;
                    } catch (\Exception $e) {
                        // Route requires parameters, check if it's a common pattern
                        if (str_contains($routeName, '.index') ||
                            str_contains($routeName, '.create') ||
                            str_contains($routeName, 'dashboard')) {
                            $routes[] = $routeName;
                        }
                    }
                }
            }
        }
        sort($routes);

        return $routes;
    }

    private function getParentItems(): Collection
    {
        if (! $this->selectedMenuId) {
            return collect();
        }

        $query = MenuItem::where('menu_id', $this->selectedMenuId)
            ->whereNull('parent_id');

        if ($this->editingItemId) {
            $query->where('id', '!=', $this->editingItemId);
        }

        return $query->orderBy('order')->orderBy('name')->get();
    }

    public function clearMenuCache()
    {
        $menuService = app(MenuBuilderService::class);

        if ($this->selectedMenuType) {
            $menuService->clearCache($this->selectedMenuType);
            session()->flash('success', __('menu.dynamic.admin.cache_cleared_specific', ['menu' => $this->selectedMenuType]));
        } else {
            $menuService->clearAllMenuCache();
            session()->flash('success', __('menu.dynamic.admin.cache_cleared_all'));
        }
    }

    public function rebuildMenuCache()
    {
        $menuService = app(MenuBuilderService::class);

        if ($this->selectedMenuType) {
            $menuService->clearCache($this->selectedMenuType);
            $menuService->build($this->selectedMenuType);
            session()->flash('success', __('menu.dynamic.admin.cache_rebuilt_specific', ['menu' => $this->selectedMenuType]));
        } else {
            $menuService->rebuildAllCaches();
            session()->flash('success', __('menu.dynamic.admin.cache_rebuilt_all'));
        }
    }

    public function toggleCacheInfo()
    {
        $this->showCacheInfo = ! $this->showCacheInfo;
    }

    // Group Management Methods
    public function addGroup()
    {
        $this->resetGroupForm();
        $this->showGroupModal = true;
    }

    public function editGroup(int $groupId)
    {
        $group = MenuGroup::findOrFail($groupId);

        $this->editingGroupId = $groupId;
        $this->groupName = $group->name;
        $this->groupMachineName = $group->machine_name;
        $this->groupDescription = $group->description ?? '';
        $this->groupIcon = $group->icon ?? '';
        $this->groupOrder = $group->order;
        $this->groupIsDefault = $group->is_default;
        $this->groupActive = $group->active;
        $this->groupVisibilityType = $group->visibility_type ?? 'all';

        // Ensure required_roles is always an array
        $requiredRoles = $group->required_roles;
        if (is_string($requiredRoles)) {
            $requiredRoles = json_decode($requiredRoles, true);
        }
        $this->groupRequiredRoles = is_array($requiredRoles) ? $requiredRoles : [];

        $this->showGroupModal = true;
    }

    public function saveGroup()
    {
        $rules = [
            'groupName' => 'required|string|max:255',
            'groupMachineName' => 'nullable|string|max:255',
            'groupDescription' => 'nullable|string',
            'groupIcon' => 'nullable|string|max:255',
            'groupOrder' => 'integer|min:0',
            'groupIsDefault' => 'boolean',
            'groupActive' => 'boolean',
            'groupRequiredRoles' => 'nullable|array',
        ];

        $this->validate($rules);

        // Automatically set visibility type based on roles selection
        $visibilityType = empty($this->groupRequiredRoles) ? 'all' : 'roles';

        $data = [
            'menu_id' => $this->selectedMenuId,
            'name' => $this->groupName,
            'machine_name' => $this->groupMachineName,
            'description' => $this->groupDescription,
            'icon' => $this->groupIcon,
            'order' => $this->groupOrder,
            'is_default' => $this->groupIsDefault,
            'active' => $this->groupActive,
            'visibility_type' => $visibilityType,
            'required_roles' => $visibilityType === 'roles' ? $this->groupRequiredRoles : null,
        ];

        if ($this->editingGroupId) {
            $group = MenuGroup::findOrFail($this->editingGroupId);
            app(UpdateMenuGroupAction::class)->execute($group, $data);
            session()->flash('success', __('menu.dynamic.admin.group_updated'));
        } else {
            app(CreateMenuGroupAction::class)->execute($data);
            session()->flash('success', __('menu.dynamic.admin.group_created'));
        }

        $this->closeGroupModal();
    }

    public function confirmDeleteGroup(int $groupId)
    {
        $this->deletingGroupId = $groupId;
        $this->showDeleteGroupModal = true;
    }

    public function deleteGroup()
    {
        if ($this->deletingGroupId) {
            $group = MenuGroup::findOrFail($this->deletingGroupId);
            app(DeleteMenuGroupAction::class)->execute($group);

            session()->flash('success', __('menu.dynamic.admin.group_deleted'));
        }

        $this->closeDeleteGroupModal();
    }

    public function moveGroupUp(int $groupId)
    {
        $group = MenuGroup::findOrFail($groupId);
        $groups = MenuGroup::where('menu_id', $group->menu_id)
            ->orderBy('order')
            ->get();

        $currentIndex = $groups->search(function ($g) use ($groupId) {
            return $g->id === $groupId;
        });

        if ($currentIndex > 0) {
            $previousGroup = $groups[$currentIndex - 1];
            $currentOrder = $group->order;

            $group->update(['order' => $previousGroup->order]);
            $previousGroup->update(['order' => $currentOrder]);
        }
    }

    public function moveGroupDown(int $groupId)
    {
        $group = MenuGroup::findOrFail($groupId);
        $groups = MenuGroup::where('menu_id', $group->menu_id)
            ->orderBy('order')
            ->get();

        $currentIndex = $groups->search(function ($g) use ($groupId) {
            return $g->id === $groupId;
        });

        if ($currentIndex < $groups->count() - 1) {
            $nextGroup = $groups[$currentIndex + 1];
            $currentOrder = $group->order;

            $group->update(['order' => $nextGroup->order]);
            $nextGroup->update(['order' => $currentOrder]);
        }
    }

    public function closeGroupModal()
    {
        $this->showGroupModal = false;
        $this->resetGroupForm();
    }

    public function closeDeleteGroupModal()
    {
        $this->showDeleteGroupModal = false;
        $this->deletingGroupId = null;
    }

    private function resetGroupForm()
    {
        $this->editingGroupId = null;
        $this->groupName = '';
        $this->groupMachineName = '';
        $this->groupDescription = '';
        $this->groupIcon = '';
        $this->groupOrder = 0;
        $this->groupIsDefault = false;
        $this->groupActive = true;
        $this->groupVisibilityType = 'all';
        $this->groupRequiredRoles = [];
        $this->resetErrorBag();
    }

    private function getMenuGroups()
    {
        if (! $this->selectedMenuId || $this->activeTab !== 'groups') {
            return collect();
        }

        $query = MenuGroup::where('menu_id', $this->selectedMenuId);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('machine_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('order')
            ->orderBy('name')
            ->paginate(20);
    }

    private function getAvailableGroups(): Collection
    {
        if (! $this->selectedMenuId) {
            return collect();
        }

        return MenuGroup::where('menu_id', $this->selectedMenuId)
            ->active()
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Convert selected roles to their associated permissions
     */
    private function convertRolesToPermissions(): array
    {
        if (empty($this->selectedRoles)) {
            return [];
        }

        $permissions = collect();

        foreach ($this->selectedRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissions = $permissions->merge($role->permissions->pluck('name'));
            }
        }

        return $permissions->unique()->values()->toArray();
    }

    /**
     * Load roles that have all the current permissions
     */
    private function loadRolesFromPermissions(): void
    {
        $this->selectedRoles = [];

        if (empty($this->permissions)) {
            return;
        }

        // Find roles that have at least one of the current permissions
        // This is a simplified approach - you might want to find roles that have ALL permissions
        $roles = Role::whereHas('permissions', function ($query) {
            $query->whereIn('name', $this->permissions);
        })->with('permissions')->get();

        // Check which roles have ALL the permissions
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $hasAllPermissions = ! array_diff($this->permissions, $rolePermissions);

            if ($hasAllPermissions) {
                $this->selectedRoles[] = $role->name;
            }
        }
    }

    /**
     * Toggle between role and permission selection
     */
    public function toggleSelectionMode(): void
    {
        $this->useRoleSelection = ! $this->useRoleSelection;

        // If switching to role selection and we have permissions, try to match roles
        if ($this->useRoleSelection && ! empty($this->permissions)) {
            $this->loadRolesFromPermissions();
        }
        // If switching to permission selection and we have roles, convert to permissions
        elseif (! $this->useRoleSelection && ! empty($this->selectedRoles)) {
            $this->permissions = $this->convertRolesToPermissions();
        }
    }

    /**
     * Handle role selection update
     * Note: We no longer convert roles to permissions - roles are used directly for access control
     */
    public function updatedSelectedRoles(): void
    {
        // No conversion needed - selected_roles are used directly by MenuBuilderService
    }

    /**
     * Check if a given icon name is a valid Heroicon
     */
    private function isValidHeroicon(string $iconName): bool
    {
        try {
            // Try to get the icon from the factory
            $factory = app(\BladeUI\Icons\Factory::class);
            $factory->svg('heroicon-o-' . $iconName);

            return true;
        } catch (\BladeUI\Icons\Exceptions\SvgNotFound $e) {
            return false;
        }
    }

    /**
     * Validate icon when it's updated
     */
    public function updatedIcon(): void
    {
        if (! empty($this->icon) && ! $this->isValidHeroicon($this->icon)) {
            $this->addError('icon', __('menu.dynamic.admin.invalid_icon', ['icon' => $this->icon]));
        } else {
            $this->resetErrorBag('icon');
        }
    }
}
