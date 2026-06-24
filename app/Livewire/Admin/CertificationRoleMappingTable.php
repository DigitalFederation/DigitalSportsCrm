<?php

namespace App\Livewire\Admin;

use App\Models\Committee;
use App\Models\Role;
use Domain\Certifications\Models\Certification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CertificationRoleMappingTable extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCommittee = '';
    public $selectedCertificationType = '';
    public $showEditModal = false;
    public $editingCertificationId = null;
    public $selectedRoles = [];
    public $selectedBulkCertifications = [];
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
        $this->selectedCertificationType = '';
    }

    public function updatedSelectedCertificationType()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBulkCertifications = $this->getCertifications()->pluck('id')->toArray();
        } else {
            $this->selectedBulkCertifications = [];
        }
    }

    public function editRoles($certificationId)
    {
        $this->editingCertificationId = $certificationId;

        // Get current roles for this certification
        $this->selectedRoles = DB::table('certification_roles')
            ->where('certification_id', $certificationId)
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
            DB::table('certification_roles')
                ->where('certification_id', $this->editingCertificationId)
                ->delete();

            // Insert new roles
            $data = [];
            foreach ($this->selectedRoles as $roleId) {
                $data[] = [
                    'certification_id' => $this->editingCertificationId,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($data)) {
                DB::table('certification_roles')->insert($data);
            }
        });

        $this->showEditModal = false;
        $this->editingCertificationId = null;
        $this->selectedRoles = [];

        session()->flash('success', __('admin.role_mappings.certification_roles_updated'));
        $this->dispatch('refreshTable');
    }

    public function openBulkModal()
    {
        if (empty($this->selectedBulkCertifications)) {
            session()->flash('error', __('admin.role_mappings.no_certifications_selected'));

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

        if (empty($this->selectedBulkCertifications)) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->selectedBulkCertifications as $certificationId) {
                // Delete existing roles
                DB::table('certification_roles')
                    ->where('certification_id', $certificationId)
                    ->delete();

                // Insert new roles
                $data = [];
                foreach ($this->bulkRoles as $roleId) {
                    $data[] = [
                        'certification_id' => $certificationId,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($data)) {
                    DB::table('certification_roles')->insert($data);
                }
            }
        });

        $this->showBulkModal = false;
        $this->selectedBulkCertifications = [];
        $this->selectAll = false;
        $this->bulkRoles = [];

        session()->flash('success', __('admin.role_mappings.bulk_update_success'));
        $this->dispatch('refreshTable');
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->editingCertificationId = null;
        $this->selectedRoles = [];
    }

    public function closeBulkModal()
    {
        $this->showBulkModal = false;
        $this->bulkRoles = [];
    }

    private function getCertifications()
    {
        $query = Certification::with(['professionalRole', 'committee']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('acronym', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCertificationType) {
            $query->where('certification_category', $this->selectedCertificationType);
        } elseif ($this->selectedCommittee) {
            $query->whereHas('committee', function ($q) {
                $q->where('code', $this->selectedCommittee);
            });
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        $certifications = $this->getCertifications()->paginate(20);

        // Get roles for each certification
        $certificationIds = $certifications->pluck('id')->toArray();
        $certificationRoles = DB::table('certification_roles')
            ->join('roles', 'certification_roles.role_id', '=', 'roles.id')
            ->whereIn('certification_roles.certification_id', $certificationIds)
            ->select('certification_roles.certification_id', 'roles.id', 'roles.name')
            ->get()
            ->groupBy('certification_id');

        // Get distinct committees from certifications
        $committees = Committee::orderBy('name')->pluck('code', 'code');

        // Get distinct certification categories for selected committee
        $certificationTypes = [];
        if ($this->selectedCommittee) {
            $certificationTypes = Certification::whereHas('committee', function ($q) {
                $q->where('code', $this->selectedCommittee);
            })
                ->select('certification_category')
                ->distinct()
                ->whereNotNull('certification_category')
                ->orderBy('certification_category')
                ->pluck('certification_category', 'certification_category');
        }

        $allRoles = Role::orderBy('name')->get();

        return view('livewire.admin.certification-role-mapping-table', [
            'certifications' => $certifications,
            'certificationRoles' => $certificationRoles,
            'committees' => $committees,
            'certificationTypes' => $certificationTypes,
            'allRoles' => $allRoles,
        ]);
    }
}
