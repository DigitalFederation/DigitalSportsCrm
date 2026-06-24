<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Domain\Permissions\Exceptions\PermissionAlreadyExistsException;
use Domain\Permissions\Exceptions\SystemPermissionException;
use Domain\Permissions\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PermissionManagementController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Display a listing of permissions
     */
    public function index(Request $request): View
    {
        // Get paginated permissions
        $query = Permission::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $permissions = $query->orderBy('category')->orderBy('name')->paginate(20);

        // Get all permissions for statistics (without pagination)
        $allPermissions = $this->permissionService->getAllPermissions();
        $categories = $this->permissionService->getCategories();

        // Calculate statistics for the view
        $permissionsWithRoles = $allPermissions->filter(function ($permission) {
            return $permission->roles()->count() > 0;
        });

        $unusedPermissions = $allPermissions->filter(function ($permission) {
            return $permission->roles()->count() === 0;
        });

        $statistics = [
            'total_permissions' => $allPermissions->count(),
            'system_permissions' => $allPermissions->filter(fn (Permission $permission) => $this->permissionService->isSystemPermission($permission->name))->count(),
            'permissions_with_roles' => $permissionsWithRoles->count(),
            'unused_permissions' => $unusedPermissions->count(),
            'by_category' => $allPermissions->groupBy('category')->map->count(),
        ];

        return view('admin.permission-management.index', compact(
            'permissions',
            'categories',
            'statistics'
        ));
    }

    /**
     * Show the form for creating a new permission
     */
    public function create(): View
    {
        $categories = $this->permissionService->getCategories();
        $guards = ['web', 'api']; // Available guards
        $scopes = Permission::getAvailableScopes();

        return view('admin.permission-management.create', compact(
            'categories',
            'guards',
            'scopes'
        ));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|in:system,federation,entity,individual',
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $permission = $this->permissionService->createPermission($validator->validated());

            return redirect()
                ->route('admin.permission-management.show', $permission)
                ->with('success', __('permission_management.messages.permission_created_successfully'));
        } catch (PermissionAlreadyExistsException $e) {
            return back()
                ->withInput()
                ->with('error', __('permission_management.errors.permission_already_exists'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('permission_management.errors.permission_creation_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission): View
    {
        $permission->load(['roles.users']);

        // Count routes that use this permission
        $affectedRoutes = \App\Models\RoutePermission::where('permission_name', $permission->name)->count();

        // Count routes that have this permission in middleware
        $routesWithMiddleware = 0;
        foreach (app('router')->getRoutes()->getRoutes() as $route) {
            $middleware = $route->gatherMiddleware();
            foreach ($middleware as $mw) {
                // Skip if middleware is not a string (could be a Closure)
                if (! is_string($mw)) {
                    continue;
                }

                if (str_contains($mw, "permission:{$permission->name}") ||
                    (str_contains($mw, 'permission:') && str_contains($mw, $permission->name))) {
                    $routesWithMiddleware++;
                    break;
                }
            }
        }

        $totalAffectedRoutes = $affectedRoutes + $routesWithMiddleware;

        $impact = [
            'affected_roles' => $permission->roles->count(),
            'affected_users' => $permission->roles->sum(fn ($role) => $role->users->count()),
            'affected_routes' => $totalAffectedRoutes,
            'roles_count' => $permission->roles->count(),
            'users_count' => $permission->roles->sum(fn ($role) => $role->users->count()),
        ];
        $isSystemPermission = $this->permissionService->isSystemPermission($permission->name);

        // Get roles that have this permission
        $rolesWithPermission = $permission->roles()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.permission-management.show', compact(
            'permission',
            'impact',
            'isSystemPermission',
            'rolesWithPermission'
        ));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit(Permission $permission): View
    {
        if ($this->permissionService->isSystemPermission($permission->name)) {
            abort(403, __('permission_management.errors.cannot_modify_system_permission'));
        }

        $categories = $this->permissionService->getCategories();
        $guards = ['web', 'api'];
        $scopes = Permission::getAvailableScopes();

        return view('admin.permission-management.edit', compact(
            'permission',
            'categories',
            'guards',
            'scopes'
        ));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        if ($this->permissionService->isSystemPermission($permission->name)) {
            abort(403, __('permission_management.errors.cannot_modify_system_permission'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|in:system,federation,entity,individual',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $updatedPermission = $this->permissionService->updatePermission($permission, $validator->validated());

            return redirect()
                ->route('admin.permission-management.show', $updatedPermission)
                ->with('success', __('permission_management.messages.permission_updated_successfully'));
        } catch (SystemPermissionException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('permission_management.errors.permission_update_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        try {
            $this->permissionService->deletePermission($permission);

            return redirect()
                ->route('admin.permission-management.index')
                ->with('success', __('permission_management.messages.permission_deleted_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Bulk create permissions
     */
    public function bulkCreate(): View
    {
        $categories = $this->permissionService->getCategories();

        return view('admin.permission-management.bulk-create', compact('categories'));
    }

    /**
     * Store bulk permissions
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array|min:1',
            'permissions.*.name' => 'required|string|max:255',
            'permissions.*.description' => 'nullable|string|max:1000',
            'permissions.*.category' => 'nullable|string|max:100',
            'default_category' => 'nullable|string|max:100',
            'default_guard' => 'nullable|string|in:web,api',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $permissions = $request->get('permissions');
            $defaultCategory = $request->get('default_category');
            $defaultGuard = $request->get('default_guard', 'web');

            // Apply defaults
            foreach ($permissions as &$permission) {
                if (empty($permission['category']) && $defaultCategory) {
                    $permission['category'] = $defaultCategory;
                }
                $permission['guard_name'] = $defaultGuard;
            }

            $result = $this->permissionService->bulkCreatePermissions($permissions);

            if (! empty($result['errors'])) {
                return back()
                    ->withInput()
                    ->with('warning', __('permission_management.messages.bulk_create_partial', [
                        'created' => count($result['created']),
                        'failed' => count($result['errors']),
                    ]))
                    ->with('bulk_errors', $result['errors']);
            }

            return redirect()
                ->route('admin.permission-management.index')
                ->with('success', __('permission_management.messages.bulk_create_success', [
                    'count' => count($result['created']),
                ]));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('permission_management.errors.bulk_create_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Search permissions via AJAX
     */
    public function search(Request $request)
    {
        $filters = [
            'search' => $request->get('q', ''),
            'category' => $request->get('category'),
        ];

        $permissions = $this->permissionService->getAllPermissions($filters);

        return response()->json([
            'permissions' => $permissions->map(function (Permission $permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'category' => $permission->category,
                    'description' => $permission->description,
                    'roles_count' => $permission->roles_count,
                ];
            }),
        ]);
    }

    /**
     * Get permission statistics via AJAX
     */
    public function statistics()
    {
        $permissions = $this->permissionService->getAllPermissions();
        $statistics = [
            'total' => $permissions->count(),
            'by_category' => $permissions->groupBy('category')->map->count(),
            'system' => $permissions->filter(fn (Permission $permission) => $this->permissionService->isSystemPermission($permission->name))->count(),
        ];

        return response()->json($statistics);
    }

    /**
     * Export permissions to CSV
     */
    public function export(Request $request)
    {
        $permissions = Permission::with('roles')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $csvData = [];
        $csvData[] = ['Name', 'Category', 'Description', 'Guard', 'Roles Count', 'Roles'];

        foreach ($permissions as $permission) {
            $csvData[] = [
                $permission->name,
                $permission->category ?? '',
                $permission->description ?? '',
                $permission->guard_name,
                $permission->roles->count(),
                $permission->roles->pluck('name')->implode(', '),
            ];
        }

        $filename = 'permissions_export_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename);
    }

    /**
     * Import permissions from CSV
     */
    public function import(): View
    {
        return view('admin.permission-management.import');
    }

    /**
     * Process permission import
     */
    public function processImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $file = $request->file('file');
            $result = $this->permissionService->importFromCsv($file->getPathname());

            return redirect()
                ->route('admin.permission-management.index')
                ->with('success', __('permission_management.messages.import_success', [
                    'created' => $result['created'],
                    'skipped' => $result['skipped'],
                ]));
        } catch (\Exception $e) {
            return back()->with('error', __('permission_management.errors.import_failed', ['error' => $e->getMessage()]));
        }
    }
}
