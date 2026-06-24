<?php

namespace App\Livewire;

use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Livewire\Component;

class ManageAllProfessionalRoles extends Component
{
    public Individual $individual;
    public $selectedFederationId;
    public $selectedRole;
    public $federations = [];
    public $allStaffRoles = [];
    public $addedRoles = [];

    public function mount(Individual $individual)
    {
        $this->individual = $individual;
        $this->federations = $individual->federations()
            ->pluck('name', 'federation.id')
            ->toArray();

        // Automatically select the federation if there is only one
        if (count($this->federations) === 1) {
            $this->selectedFederationId = key($this->federations);
            $this->loadRoles();
        }
    }

    public function loadRoles()
    {
        if ($this->selectedFederationId) {
            $this->allStaffRoles = ProfessionalRole::where('role', 'STAFF')
                ->pluck('name', 'id')
                ->toArray();
            $this->loadAddedRoles();
        }
    }

    private function loadAddedRoles()
    {
        $this->addedRoles = $this->individual->federationProfessionalRoles()
            ->where('federation_id', $this->selectedFederationId)
            ->pluck('role_name', 'id')
            ->toArray();
    }

    public function addRole()
    {
        if ($this->selectedRole) {

            // Retrieve the federation name from the federations array
            $federationName = $this->federations[$this->selectedFederationId] ?? null;

            // Check if the role ID exists in the allStaffRoles array
            if (! isset($this->allStaffRoles[$this->selectedRole])) {
                // Handle the error, e.g., setting an error message
                session()->flash('error', __('Selected role does not exist.'));

                return;
            }

            $this->individual->federationProfessionalRoles()->create([
                'federation_id' => $this->selectedFederationId,
                'federation_name' => $federationName,
                'professional_role_id' => $this->selectedRole,
                'role_name' => $this->allStaffRoles[$this->selectedRole],
                'status_class' => ActiveIndividualFederationState::class,
            ]);

            $this->loadAddedRoles(); // Refresh the list of added roles
        }
    }

    public function removeRole($roleId)
    {
        $this->individual->federationProfessionalRoles()
            ->where('id', $roleId)
            ->where('federation_id', $this->selectedFederationId)
            ->delete();

        $this->loadAddedRoles(); // Refresh the list of added roles
    }

    public function render()
    {
        return view('livewire.manage-all-professional-roles');
    }
}
