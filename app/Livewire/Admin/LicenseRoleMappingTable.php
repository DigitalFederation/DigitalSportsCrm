<?php

namespace App\Livewire\Admin;

use App\Models\Committee;
use App\Models\Role;
use Domain\Licenses\Models\License;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LicenseRoleMappingTable extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCommittee = '';
    public $selectedRequesterModel = '';
    public $showEditModal = false;
    public $editingLicenseId = null;
    public $selectedRoles = [];
    public $selectedBulkLicenses = [];
    public $selectAll = false;
    public $showBulkModal = false;
    public $bulkRoles = [];

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

    public function updatedSelectedCommittee()
    {
        $this->resetPage();
    }

    public function updatedSelectedRequesterModel()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBulkLicenses = $this->getLicenses()->pluck('id')->toArray();
        } else {
            $this->selectedBulkLicenses = [];
        }
    }

    public function editRoles($licenseId)
    {
        $this->editingLicenseId = $licenseId;

        // Get current roles for this license
        $this->selectedRoles = DB::table('license_roles')
            ->where('license_id', $licenseId)
            ->pluck('role_id')
            ->toArray();

        $this->showEditModal = true;
    }

    public function saveRoles()
    {
        $this->validate([
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'exists:roles,id',
        ]);

        DB::transaction(function () {
            // Delete existing roles
            DB::table('license_roles')
                ->where('license_id', $this->editingLicenseId)
                ->delete();

            // Insert new roles
            $data = [];
            foreach ($this->selectedRoles as $roleId) {
                $data[] = [
                    'license_id' => $this->editingLicenseId,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($data)) {
                DB::table('license_roles')->insert($data);
            }
        });

        $this->showEditModal = false;
        $this->editingLicenseId = null;
        $this->selectedRoles = [];

        session()->flash('success', __('admin.role_mappings.license_roles_updated'));
        $this->dispatch('refreshTable');
    }

    public function openBulkModal()
    {
        if (empty($this->selectedBulkLicenses)) {
            session()->flash('error', __('admin.role_mappings.no_licenses_selected'));

            return;
        }

        $this->bulkRoles = [];
        $this->showBulkModal = true;
    }

    public function saveBulkRoles()
    {
        $this->validate([
            'bulkRoles' => 'array',
            'bulkRoles.*' => 'exists:roles,id',
        ]);

        if (empty($this->selectedBulkLicenses)) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->selectedBulkLicenses as $licenseId) {
                // Delete existing roles
                DB::table('license_roles')
                    ->where('license_id', $licenseId)
                    ->delete();

                // Insert new roles
                $data = [];
                foreach ($this->bulkRoles as $roleId) {
                    $data[] = [
                        'license_id' => $licenseId,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($data)) {
                    DB::table('license_roles')->insert($data);
                }
            }
        });

        $this->showBulkModal = false;
        $this->selectedBulkLicenses = [];
        $this->selectAll = false;
        $this->bulkRoles = [];

        session()->flash('success', __('admin.role_mappings.bulk_update_success'));
        $this->dispatch('refreshTable');
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->editingLicenseId = null;
        $this->selectedRoles = [];
    }

    public function closeBulkModal()
    {
        $this->showBulkModal = false;
        $this->bulkRoles = [];
    }

    private function getLicenses()
    {
        $query = License::with(['professionalRole', 'committee']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCommittee) {
            $query->where('committee_id', $this->selectedCommittee);
        }

        if ($this->selectedRequesterModel) {
            $query->whereJsonContains('requester_model', $this->selectedRequesterModel);
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        $licenses = $this->getLicenses()->paginate(20);

        // Get roles for each license
        $licenseIds = $licenses->pluck('id')->toArray();
        $licenseRoles = DB::table('license_roles')
            ->join('roles', 'license_roles.role_id', '=', 'roles.id')
            ->whereIn('license_roles.license_id', $licenseIds)
            ->select('license_roles.license_id', 'roles.id', 'roles.name')
            ->get()
            ->groupBy('license_id');

        $committees = Committee::orderBy('name')->get();
        $allRoles = Role::orderBy('name')->get();

        return view('livewire.admin.license-role-mapping-table', [
            'licenses' => $licenses,
            'licenseRoles' => $licenseRoles,
            'committees' => $committees,
            'allRoles' => $allRoles,
        ]);
    }
}
