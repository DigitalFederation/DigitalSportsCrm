<?php

namespace App\Livewire;

use App\Models\Permission;
use Domain\Permissions\Services\RoutePermissionService;
use Livewire\Component;

class RoutePermissionTable extends Component
{
    public $search = '';
    public $module = '';
    public $hasPermission = '';

    public $showEditModal = false;
    public $showAssignModal = false;
    public $editingRoute = null;
    public $editingPermission = '';
    public $editingPermissions = [];
    public $editingIsActive = true;

    protected $queryString = ['search', 'module', 'hasPermission'];

    protected $rules = [
        'editingPermission' => 'required|exists:permissions,name',
        'editingIsActive' => 'boolean',
    ];

    public function mount()
    {
        $this->search = request('search', '');
        $this->module = request('module', '');
        $this->hasPermission = request('has_permission', '');
    }

    public function updatedSearch()
    {
        // Reset any necessary state when search changes
    }

    public function updatedModule()
    {
        // Reset any necessary state when module changes
    }

    public function updatedHasPermission()
    {
        // Reset any necessary state when permission filter changes
    }

    public function editPermission($routeName, $currentPermission)
    {
        $this->editingRoute = $routeName;

        // Get all permissions for this route from database
        $this->editingPermissions = \App\Models\RoutePermission::where('route_pattern', $routeName)
            ->pluck('permission_name')
            ->toArray();

        $this->editingPermission = '';
        $this->editingIsActive = true;
        $this->showEditModal = true;
    }

    public function addPermissionToRoute()
    {
        if (empty($this->editingPermission)) {
            return;
        }

        try {
            app(RoutePermissionService::class)->assignPermissionToRoute(
                $this->editingRoute,
                $this->editingPermission,
                true
            );

            $this->editingPermissions[] = $this->editingPermission;
            $this->editingPermission = '';

            session()->flash('success', __('route_permissions.messages.permission_assigned'));
            $this->dispatch('permission-updated');
        } catch (\Exception $e) {
            session()->flash('error', __('route_permissions.errors.assignment_failed', ['error' => $e->getMessage()]));
        }
    }

    public function removePermissionFromRoute($permission)
    {
        try {
            \App\Models\RoutePermission::where('route_pattern', $this->editingRoute)
                ->where('permission_name', $permission)
                ->delete();

            $this->editingPermissions = array_values(array_filter($this->editingPermissions, fn ($p) => $p !== $permission));

            session()->flash('success', __('route_permissions.messages.permission_removed'));
            $this->dispatch('permission-removed');
        } catch (\Exception $e) {
            session()->flash('error', __('route_permissions.errors.remove_failed', ['error' => $e->getMessage()]));
        }
    }

    public function assignPermission($routeName)
    {
        $this->editingRoute = $routeName;
        $this->editingPermission = '';
        $this->editingIsActive = true;
        $this->showAssignModal = true;
    }

    public function savePermission()
    {
        $this->validate();

        try {
            app(RoutePermissionService::class)->assignPermissionToRoute(
                $this->editingRoute,
                $this->editingPermission,
                $this->editingIsActive
            );

            $this->showAssignModal = false;
            $this->reset(['editingRoute', 'editingPermission', 'editingIsActive']);

            session()->flash('success', __('route_permissions.messages.permission_assigned'));
            $this->dispatch('permission-assigned');
        } catch (\Exception $e) {
            session()->flash('error', __('route_permissions.errors.assignment_failed', ['error' => $e->getMessage()]));
        }
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->reset(['editingRoute', 'editingPermission', 'editingIsActive']);
    }

    public function updatePermission()
    {
        $this->validate();

        try {
            app(RoutePermissionService::class)->assignPermissionToRoute(
                $this->editingRoute,
                $this->editingPermission,
                $this->editingIsActive
            );

            $this->showEditModal = false;
            $this->reset(['editingRoute', 'editingPermission', 'editingIsActive']);

            session()->flash('success', __('route_permissions.messages.permission_updated'));
            $this->dispatch('permission-updated');
        } catch (\Exception $e) {
            session()->flash('error', __('route_permissions.errors.update_failed', ['error' => $e->getMessage()]));
        }
    }

    public function removePermission($routeName)
    {
        if (! confirm(__('route_permissions.confirm_remove_permission'))) {
            return;
        }

        try {
            app(RoutePermissionService::class)->removePermissionFromRoute($routeName);

            session()->flash('success', __('route_permissions.messages.permission_removed'));
            $this->dispatch('permission-removed');
        } catch (\Exception $e) {
            session()->flash('error', __('route_permissions.errors.remove_failed', ['error' => $e->getMessage()]));
        }
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->reset(['editingRoute', 'editingPermission', 'editingIsActive']);
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'module' => $this->module,
            'has_permission' => $this->hasPermission,
        ];

        $routeService = app(RoutePermissionService::class);
        $routes = $routeService->getRoutesWithPermissions($filters);

        // Group routes by module
        $groupedByModule = $routes->groupBy(function ($route) {
            $parts = explode('.', $route['name'] ?? '');

            return $parts[1] ?? 'uncategorized';
        });

        // Transform grouped routes into expected structure
        $groupedRoutes = $groupedByModule->map(function ($moduleRoutes, $module) {
            // Further group by prefix
            $byPrefix = $moduleRoutes->groupBy(function ($route) {
                $parts = explode('.', $route['name'] ?? '');

                return $parts[0] ?? 'no-prefix';
            });

            return [
                'total' => $moduleRoutes->count(),
                'with_permissions' => $moduleRoutes->where('has_permission', true)->count(),
                'without_permissions' => $moduleRoutes->where('has_permission', false)->count(),
                'routes' => $byPrefix,
            ];
        });

        $statistics = [
            'total_routes' => $routes->count(),
            'with_permissions' => $routes->where('has_permission', true)->count(),
            'without_permissions' => $routes->where('has_permission', false)->count(),
        ];
        $statistics['percentage_protected'] = $statistics['total_routes'] > 0
            ? round(($statistics['with_permissions'] / $statistics['total_routes']) * 100, 1)
            : 0;

        $modules = $routes->map(function ($route) {
            $parts = explode('.', $route['name'] ?? '');

            return $parts[1] ?? null;
        })->unique()->filter()->sort()->values();

        $permissions = Permission::orderBy('category')->orderBy('name')->get();

        return view('livewire.route-permission-table', [
            'groupedRoutes' => $groupedRoutes,
            'statistics' => $statistics,
            'modules' => $modules,
            'permissions' => $permissions,
            'permissionsByCategory' => $permissions->groupBy('category'),
        ]);
    }
}
