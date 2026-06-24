<?php

namespace App\Livewire;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class RolePermissionManager extends Component
{
    public $selectedRoleId;
    public $permissions = [];
    public $assignedPermissions = [];

    public function mount()
    {
        if (! auth()->user()->can('manage user roles')) {
            abort(403);
        }

        $this->permissions = Permission::orderBy('name')->get();
    }

    public function updatedSelectedRoleId($roleId)
    {
        $this->assignedPermissions = Role::find($roleId)->permissions->pluck('id')->toArray();
    }

    public function save()
    {
        $this->validate([
            'selectedRoleId' => 'required|exists:roles,id',
            'assignedPermissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::find($this->selectedRoleId);
        $role->syncPermissions($this->assignedPermissions);
        session()->flash('message', 'Permissions updated successfully.');
    }

    public function render()
    {
        return view('livewire.role-permission-manager', [
            'roles' => Role::all(),
        ]);
    }
}
