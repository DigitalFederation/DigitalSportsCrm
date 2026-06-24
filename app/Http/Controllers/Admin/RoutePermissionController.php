<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RoutePermission;
use Domain\Permissions\Actions\QuickAssignPermissionAction;
use Domain\Permissions\Services\PermissionService;
use Domain\Permissions\Services\RoutePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoutePermissionController extends Controller
{
    public function __construct(
        protected RoutePermissionService $routeService,
        protected PermissionService $permissionService
    ) {}

    /**
     * Display a listing of routes and their permissions
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['prefix', 'module', 'has_permission', 'search']);
        $routes = $this->routeService->getRoutesWithPermissions($filters);

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

        $prefixes = $routes->pluck('uri')->map(fn ($uri) => explode('/', $uri)[0])->unique()->filter()->sort()->values();
        $modules = $routes->map(function ($route) {
            $parts = explode('.', $route['name'] ?? '');

            return $parts[1] ?? null;
        })->unique()->filter()->sort()->values();

        return view('admin.route-permissions.index', compact(
            'groupedRoutes',
            'statistics',
            'prefixes',
            'modules',
            'filters'
        ));
    }

    /**
     * Quick assign a permission to a single route (AJAX)
     */
    public function quickAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'route_name' => 'required|string',
            'permission' => 'required|string|exists:permissions,name',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = QuickAssignPermissionAction::execute(
                $request->get('route_name'),
                $request->get('permission'),
                $request->get('is_active', true)
            );

            $message = $result['action'] === 'created'
                ? __('route_permissions.messages.permission_assigned')
                : __('route_permissions.messages.permission_updated');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('route_permissions.errors.assignment_failed', ['error' => $e->getMessage()]),
            ], 500);
        }
    }

    /**
     * Update a single route permission
     */
    public function update(Request $request, $routeName)
    {
        $validator = Validator::make($request->all(), [
            'permission_name' => 'required|string|exists:permissions,name',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $routePermission = $this->routeService->assignPermissionToRoute(
                $routeName,
                $request->get('permission_name'),
                $request->get('is_active', true)
            );

            return redirect()
                ->route('admin.route-permissions.index')
                ->with('success', __('route_permissions.messages.permission_updated'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('route_permissions.errors.update_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Remove route permission
     */
    public function destroy($routeName)
    {
        try {
            $result = $this->routeService->removePermissionFromRoute($routeName);

            if ($result) {
                return redirect()
                    ->route('admin.route-permissions.index')
                    ->with('success', __('route_permissions.messages.permission_removed'));
            }

            return back()->with('error', __('route_permissions.messages.permission_not_found'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get permission suggestions for a route via AJAX
     */
    public function suggest(Request $request)
    {
        $routeName = $request->get('route_name');

        if (! $routeName) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = $this->routeService->suggestPermissions($routeName);

        // Check which suggestions already exist
        $existingPermissions = Permission::whereIn('name', $suggestions)
            ->pluck('name')
            ->toArray();

        $suggestionsWithStatus = collect($suggestions)->map(function ($permission) use ($existingPermissions) {
            return [
                'name' => $permission,
                'exists' => in_array($permission, $existingPermissions),
            ];
        });

        return response()->json([
            'suggestions' => $suggestionsWithStatus,
        ]);
    }

    /**
     * Preview impact of permission changes
     */
    public function preview(Request $request): View
    {
        $changes = collect($request->get('changes', []));

        $impact = [
            'new_mappings' => $changes->where('action', 'create')->count(),
            'updated_mappings' => $changes->where('action', 'update')->count(),
            'removed_mappings' => $changes->where('action', 'delete')->count(),
            'affected_routes' => $changes->pluck('route_name')->unique()->count(),
            'affected_permissions' => $changes->pluck('permission')->unique()->count(),
        ];

        return view('admin.route-permissions.preview', compact('changes', 'impact'));
    }

    /**
     * Export route permissions mapping
     */
    public function export(Request $request)
    {
        $routes = $this->routeService->scanRoutes();

        $csvData = [];
        $csvData[] = ['Route Name', 'URI', 'Methods', 'Current Permission', 'Dynamic Permission', 'Status'];

        foreach ($routes as $route) {
            $routePermission = RoutePermission::where('route_pattern', $route['name'])->first();
            $dynamicPermission = $routePermission ? $routePermission->permission_name : '';
            $status = $routePermission ? ($routePermission->is_active ? 'Active' : 'Inactive') : 'Not Set';

            $csvData[] = [
                $route['name'] ?? '',
                $route['uri'],
                implode('|', $route['methods']),
                implode('|', $route['middleware']),
                $dynamicPermission,
                $status,
            ];
        }

        $filename = 'route_permissions_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename);
    }
}
