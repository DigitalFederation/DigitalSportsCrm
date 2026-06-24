<?php

namespace App\Livewire;

use Domain\Individuals\Actions\ValidLicenseAvailableToIndividualAction;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\GetAllowedFederationLicensesAction;
use Domain\OfficialDocuments\Actions\ValidateIndividualSportLicenseEligibilityAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GetIndividualByCodeForLicense extends Component
{
    public $entities = [];

    public $federations = [];

    public $federation = null;

    public ?int $entity = null;
    public bool $showdiv = false;

    public string $code = '';

    public array $records = [];

    public $licenses = [];

    public $selected_license = null;

    public $selected_license_error = false;

    public string $errorMessage = '';

    public $committee = null;

    public $licenseTypeName = null;

    public $professionalRole = null;

    public $showModal = false;

    protected $listeners = ['individual-selected' => 'setIndividualCode'];
    public function setIndividualCode($code)
    {
        $this->code = $code;
        $this->searchResult();
    }

    public function openModal()
    {
        $this->dispatch('open-modal');
    }

    // Fetch records
    public function searchResult()
    {
        if (empty($this->code) || empty($this->selected_license)) {
            return;
        }

        $individual = Individual::where('member_code', $this->code)->first();

        if (empty($individual)) {
            $this->errorMessage = __('validation.individual_not_found');

            return;
        }

        // Check if individual is already in records
        if ($this->isIndividualAlreadySelected($individual)) {
            $this->errorMessage = __('This individual is already selected.');

            return;
        }

        // First check license availability
        $validLicenseAction = new ValidLicenseAvailableToIndividualAction;
        $hasValidLicense = $validLicenseAction($individual, $this->selected_license);

        if (! $hasValidLicense) {
            $this->errorMessage = __('The individual does not have the certifications needed to take this license.');

            return;
        }

        // Then check if it's a sport license
        $isSportLicense = strtolower(trim($this->committee)) === 'sport';

        if ($isSportLicense) {
            $validateEligibility = new ValidateIndividualSportLicenseEligibilityAction;
            $eligibilityResult = $validateEligibility($individual);

            if (! $eligibilityResult['is_valid']) {
                $this->errorMessage = __('validation.license_eligibility_error') . ': ' .
                    implode(', ', array_column($eligibilityResult['errors'], 'message'));

                return;
            }
        }

        // Add new individual to records array
        $this->records[] = [
            'id' => $individual->id,
            'name' => $individual->full_name,
            'member_code' => $individual->member_code,
            'photo' => $individual->getFirstMediaUrl('photo'),
            'country' => $individual->country,
        ];

        $this->showdiv = true;
        $this->code = ''; // Clear the input
        $this->errorMessage = ''; // Clear any previous error messages
    }

    private function isIndividualAlreadySelected(Individual $individual): bool
    {
        return collect($this->records)->contains('id', $individual->id);
    }

    public function removeResult($key)
    {
        unset($this->records[$key]);
        if (empty($this->records)) {
            $this->showdiv = false;
        }
    }

    public function render(): View
    {
        if (! empty($this->federation) && ! empty($this->committee) && ! empty($this->licenseTypeName)) {
            $this->updatedFederation();
        }

        return view('livewire.get-individual-by-code-for-license');
    }

    /**
     * Detects federation and updates licenses
     */
    public function updatedFederation(): void
    {
        $this->licenses = [];
        $allowedLicenses = new GetAllowedFederationLicensesAction;
        $this->licenses = $allowedLicenses($this->federation, $this->committee, $this->licenseTypeName, $this->professionalRole);
    }
}
