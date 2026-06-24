<?php

namespace App\Livewire;

use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\FilterLicensesByCertificationsAndRolesAction;
use Domain\Licenses\Actions\GetLicensesBasedOnCommitteeAndIndividualAction;
use Livewire\Component;

class IndividualLicenseRequestSelector extends Component
{
    public $selectedFederationId;

    public $federations;

    public $licenses = [];

    public $type;

    public $selectedLicenseId;
    public $selectedLicenseCost;

    public function mount($federations)
    {
        $this->federations = $federations;

        // Set the default federation if only one is associated
        if ($federations->count() === 1) {
            $this->selectedFederationId = $federations->first()->id;
            $this->loadLicenses();
        }
    }

    public function render()
    {
        return view('livewire.individual-license-request-selector');
    }

    public function updatedSelectedFederationId($federationId): void
    {
        $this->loadLicenses();
    }

    public function updatedSelectedLicenseId($licenseId)
    {
        $this->selectedLicenseCost = null;

        if ($licenseId) {
            $license = $this->licenses->firstWhere('id', $licenseId);

            if ($license) {
                $calculatePriceAction = new CalculateLicensePriceAction;
                $this->selectedLicenseCost = $calculatePriceAction($license, Individual::class);
            }
        }
    }

    public function loadLicenses()
    {
        if (! $this->selectedFederationId) {
            $this->licenses = [];

            return;
        }

        $individual = auth()->user()->individuals()->first();
        // $federationIds = $individual->federations()->pluck('federation.id')->unique();
        $federationIds = collect([$this->selectedFederationId]);

        // Use the action to get licenses
        $getLicensesAction = new GetLicensesBasedOnCommitteeAndIndividualAction;
        $licenses = $getLicensesAction($this->type, $individual, $federationIds);

        $filterLicensesAction = new FilterLicensesByCertificationsAndRolesAction;

        // Calculate the price for each license
        $calculatePriceAction = new CalculateLicensePriceAction;
        foreach ($licenses as &$license) {
            $license->calculated_price = $calculatePriceAction($license, 'Individual');
        }

        $this->licenses = $filterLicensesAction($licenses, $individual);
    }
}
