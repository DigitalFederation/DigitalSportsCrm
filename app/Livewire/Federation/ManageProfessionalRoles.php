<?php

namespace App\Livewire\Federation;

use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Livewire\Component;

class ManageProfessionalRoles extends Component
{
    public Individual $individual;
    public $federationId; // Assuming this is passed in or available in context
    public $federationName; // Assuming this is passed in or available in context
    public $selectedRole;
    public $allStaffRoles = [];
    public $addedRoles = [];

    public function mount(Individual $individual, $federationId, $federationName)
    {
        $this->individual = $individual;
        $this->federationId = $federationId;
        $this->federationName = $federationName;
        $this->allStaffRoles = ProfessionalRole::where('role', 'FEDERATION_STAFF')->pluck('name', 'id')->toArray();
        $this->loadAddedRoles();
    }

    private function loadAddedRoles()
    {
        $this->addedRoles = $this->individual->federationProfessionalRoles()
            ->where('federation_id', $this->federationId)
            ->pluck('role_name', 'id')
            ->toArray();
    }

    public function addRole()
    {
        if ($this->selectedRole) {
            $this->individual->federationProfessionalRoles()->create([
                'federation_id' => $this->federationId,
                'federation_name' => $this->federationName,
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
            ->where('federation_id', $this->federationId)
            ->delete();

        $this->loadAddedRoles(); // Refresh the list of added roles
    }

    public function render()
    {
        return view('livewire.federation.manage-professional-roles');
    }

}
