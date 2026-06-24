<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function __construct(
        protected RoleManagementService $roleService
    ) {}

    public function index(Request $request): View
    {
        // Use the service to get all data instead of direct queries
        $roles = $this->roleService->getAllRoles($request->all());
        $scopes = $this->roleService->getAvailableScopes();
        $protectionLevels = $this->roleService->getProtectionLevels();
        $statistics = $this->roleService->getRoleStatistics();

        return view('admin.role-management.index', compact(
            'roles',
            'scopes',
            'protectionLevels',
            'statistics'
        ));
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'users', 'createdBy', 'updatedBy']);

        $protectionInfo = $this->roleService->getProtectedRoleInfo($role);
        $impact = $this->roleService->getRoleDeletionImpact($role);
        // Note: Activity summary now available through Spatie ActivityLog
        $auditSummary = [];

        return view('admin.role-management.show', compact(
            'role',
            'protectionInfo',
            'impact',
            'auditSummary'
        ));
    }

    public function create(): View
    {
        $permissions = $this->roleService->getAllPermissions();
        $categories = $this->roleService->getAvailableCategories();
        $templates = $this->roleService->getActiveTemplates();
        $scopes = $this->roleService->getAvailableScopes();

        return view('admin.role-management.create', compact(
            'permissions',
            'categories',
            'templates',
            'scopes'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name|regex:/^[a-z0-9-_]+$/',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'scope' => 'nullable|in:system,federation,entity,individual',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'is_protected' => 'nullable|boolean',
            'protection_level' => 'nullable|in:system,admin,user',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $role = $this->roleService->createRole($validator->validated());

            return redirect()
                ->route('admin.role-management.show', $role)
                ->with('success', __('role_management.messages.role_created_successfully'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('role_management.errors.role_creation_failed', ['error' => $e->getMessage()]));
        }
    }

    public function edit(Role $role): View
    {
        if (! $this->roleService->canModifyRole($role)) {
            abort(403, __('role_management.errors.cannot_modify_protected_role'));
        }

        $role->load('permissions');
        $permissions = $this->roleService->getAllPermissions();
        $categories = $this->roleService->getAvailableCategories();
        $scopes = $this->roleService->getAvailableScopes();
        $protectionInfo = $this->roleService->getProtectedRoleInfo($role);

        return view('admin.role-management.edit', compact(
            'role',
            'permissions',
            'categories',
            'scopes',
            'protectionInfo'
        ));
    }

    public function update(Request $request, Role $role)
    {
        if (! $this->roleService->canModifyRole($role)) {
            abort(403, __('role_management.errors.cannot_modify_protected_role'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id . '|regex:/^[a-z0-9-_]+$/',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'scope' => 'nullable|in:system,federation,entity,individual',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $updatedRole = $this->roleService->updateRole($role, $validator->validated());

            return redirect()
                ->route('admin.role-management.show', $updatedRole)
                ->with('success', __('role_management.messages.role_updated_successfully'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('role_management.errors.role_update_failed', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(Role $role)
    {
        if (! $this->roleService->canDeleteRole($role)) {
            return back()->with('error', __('role_management.errors.cannot_delete_protected_role'));
        }

        $issues = $this->roleService->validateRoleDeletion($role);
        if (! empty($issues)) {
            return back()->with('error', implode(' ', $issues));
        }

        try {
            $this->roleService->deleteRole($role);

            return redirect()
                ->route('admin.role-management.index')
                ->with('success', __('role_management.messages.role_deleted_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('role_management.errors.role_deletion_failed', ['error' => $e->getMessage()]));
        }
    }

    public function duplicate(Role $role)
    {
        $newName = $this->roleService->generateRoleName($role->name . '-copy');

        try {
            $newRole = $this->roleService->duplicateRole($role, $newName);

            return redirect()
                ->route('admin.role-management.edit', $newRole)
                ->with('success', __('role_management.messages.role_duplicated_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('role_management.errors.role_duplication_failed', ['error' => $e->getMessage()]));
        }
    }

    public function permissions(Role $role): View
    {
        if (! $this->roleService->canModifyRole($role)) {
            abort(403, __('role_management.errors.cannot_modify_protected_role'));
        }

        $role->load('permissions');
        $permissions = $this->roleService->getAllPermissions();
        $allPermissions = $this->roleService->getAllPermissionsGrouped();
        $protectionInfo = $this->roleService->getProtectedRoleInfo($role);
        $categories = Permission::distinct('category')
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return view('admin.role-management.permissions', compact(
            'role',
            'permissions',
            'allPermissions',
            'protectionInfo',
            'categories'
        ));
    }

    public function assignPermissions(Request $request, Role $role)
    {
        if (! $this->roleService->canModifyRole($role)) {
            abort(403, __('role_management.errors.cannot_modify_protected_role'));
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $this->roleService->assignPermissionsToRole($role, $request->get('permissions'));

            return back()->with('success', __('role_management.messages.permissions_assigned_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('role_management.errors.permission_assignment_failed', ['error' => $e->getMessage()]));
        }
    }

    public function syncPermissions(Request $request, Role $role)
    {
        if (! $this->roleService->canModifyRole($role)) {
            abort(403, __('role_management.errors.cannot_modify_protected_role'));
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $this->roleService->syncRolePermissions($role, $request->get('permissions', []));

            return back()->with('success', __('role_management.messages.permissions_synced_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('role_management.errors.permission_sync_failed', ['error' => $e->getMessage()]));
        }
    }

    public function searchRoles(Request $request)
    {
        $query = $request->get('q', '');
        $category = $request->get('category');

        $roles = $this->roleService->searchRoles($query, $category);

        return response()->json([
            'roles' => $roles->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'category' => $role->category,
                    'description' => $role->description,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                    'is_protected' => $role->is_protected,
                    'protection_level' => $role->protection_level,
                ];
            }),
        ]);
    }

    public function getStatistics()
    {
        $statistics = $this->roleService->getRoleStatistics();

        return response()->json($statistics);
    }
}
