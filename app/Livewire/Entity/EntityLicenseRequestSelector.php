<?php

namespace App\Livewire\Entity;

use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\GetAllowedEntityLicensesAction;
use Livewire\Component;

class EntityLicenseRequestSelector extends Component
{
    public $selectedLicenseId;
    public $selectedLicenseCost;
    public $licenses = [];
    public $type;

    public function mount($type)
    {
        $this->type = $type;
        $this->loadLicenses();
    }

    public function render()
    {
        return view('livewire.entity.entity-license-request-selector');
    }

    public function updatedSelectedLicenseId($licenseId)
    {
        $this->selectedLicenseCost = null;

        if ($licenseId) {
            $license = $this->licenses->firstWhere('id', $licenseId);
            $this->selectedLicenseCost = $license ? $license->unit_value + $license->tax_value : null;
        }
    }

    public function loadLicenses()
    {
        $entity = auth()->user()->entities()->first();

        // Use the action to get licenses for the entity
        $getLicensesAction = new GetAllowedEntityLicensesAction;
        $licenses = $getLicensesAction($this->type, $entity);

        // Calculate the price for each license
        $calculatePriceAction = new CalculateLicensePriceAction;
        foreach ($licenses as &$license) {
            $license->calculated_price = $calculatePriceAction($license, 'Entity');
        }

        $this->licenses = $licenses;
    }
}
