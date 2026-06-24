<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class FederationRoleMappingTable extends Component
{
    use WithPagination;

    public $search = '';
    public $showEditModal = false;
    public $editingFederationId = null;
    public $selectedRoles = [];
    public $requiresActiveMembership = [];
    public $showGlobalModal = false;
    public $globalRoles = [];
    public $globalRequiresActiveMembership = [];

    // Use default Tailwind pagination theme

    protected $listeners = ['refreshTable' => '$refresh'];

    public function mount()
    {
        if (! auth()->user()->can('access users')) {
            abort(403);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function editRoles($federationId)
    {
        $this->editingFederationId = $federationId;

        // Get current roles for this federation
        $roles = DB::table('federation_roles')
            ->where('federation_id', $federationId)
            ->get();

        $this->selectedRoles = $roles->pluck('role_id')->toArray();

        // Build requires_active_membership array
        $this->requiresActiveMembership = [];
        foreach ($roles as $role) {
            $this->requiresActiveMembership[$role->role_id] = (bool) $role->requires_active_membership;
        }

        $this->showEditModal = true;
    }

    public function editGlobalRoles()
    {
        // Get current global roles (federation_id is null)
        $roles = DB::table('federation_roles')
            ->whereNull('federation_id')
            ->get();

        $this->globalRoles = $roles->pluck('role_id')->toArray();

        // Build requires_active_membership array
        $this->globalRequiresActiveMembership = [];
        foreach ($roles as $role) {
            $this->globalRequiresActiveMembership[$role->role_id] = (bool) $role->requires_active_membership;
        }

        $this->showGlobalModal = true;
    }

    public function saveRoles()
    {
        $this->validate([
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () {
            // Delete existing roles
            DB::table('federation_roles')
                ->where('federation_id', $this->editingFederationId)
                ->delete();

            // Insert new roles
            $data = [];
            foreach ($this->selectedRoles as $roleId) {
                $data[] = [
                    'federation_id' => $this->editingFederationId,
                    'role_id' => $roleId,
                    'requires_active_membership' => $this->requiresActiveMembership[$roleId] ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($data)) {
                DB::table('federation_roles')->insert($data);
            }
        });

        $this->showEditModal = false;
        $this->editingFederationId = null;
        $this->selectedRoles = [];
        $this->requiresActiveMembership = [];

        session()->flash('success', __('admin.role_mappings.federation_roles_updated'));
        $this->dispatch('refreshTable');
    }

    public function saveGlobalRoles()
    {
        $this->validate([
            'globalRoles' => 'array',
            'globalRoles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () {
            // Delete existing global roles
            DB::table('federation_roles')
                ->whereNull('federation_id')
                ->delete();

            // Insert new roles
            $data = [];
            foreach ($this->globalRoles as $roleId) {
                $data[] = [
                    'federation_id' => null,
                    'role_id' => $roleId,
                    'requires_active_membership' => $this->globalRequiresActiveMembership[$roleId] ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($data)) {
                DB::table('federation_roles')->insert($data);
            }
        });

        $this->showGlobalModal = false;
        $this->globalRoles = [];
        $this->globalRequiresActiveMembership = [];

        session()->flash('success', __('admin.role_mappings.global_roles_updated'));
        $this->dispatch('refreshTable');
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->editingFederationId = null;
        $this->selectedRoles = [];
        $this->requiresActiveMembership = [];
    }

    public function closeGlobalModal()
    {
        $this->showGlobalModal = false;
        $this->globalRoles = [];
        $this->globalRequiresActiveMembership = [];
    }

    private function getFederations()
    {
        $query = Federation::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        $federations = $this->getFederations()->paginate(20);

        // Get roles for each federation
        $federationIds = $federations->pluck('id')->toArray();
        $federationRoles = DB::table('federation_roles')
            ->join('roles', 'federation_roles.role_id', '=', 'roles.id')
            ->whereIn('federation_roles.federation_id', $federationIds)
            ->select('federation_roles.federation_id', 'roles.id', 'roles.name', 'federation_roles.requires_active_membership')
            ->get()
            ->groupBy('federation_id');

        // Get global roles (named differently to avoid collision with the public $globalRoles property)
        $currentGlobalRoles = DB::table('federation_roles')
            ->join('roles', 'federation_roles.role_id', '=', 'roles.id')
            ->whereNull('federation_roles.federation_id')
            ->select('roles.id', 'roles.name', 'federation_roles.requires_active_membership')
            ->get();

        $allRoles = Role::orderBy('name')->get();

        return view('livewire.admin.federation-role-mapping-table', [
            'federations' => $federations,
            'federationRoles' => $federationRoles,
            'currentGlobalRoles' => $currentGlobalRoles,
            'allRoles' => $allRoles,
        ]);
    }
}
