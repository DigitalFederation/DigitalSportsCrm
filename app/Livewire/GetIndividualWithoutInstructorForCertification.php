<?php

namespace App\Livewire;

use App\Models\Committee;
use Domain\Certifications\Actions\GetCertificationsByLicensesAction;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\GetLicensesFromFederationMembership;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GetIndividualWithoutInstructorForCertification extends Component
{
    public $federations = [];
    public $federationId;
    public $entityId;
    public $selectedEntity;
    public $selectedFederation;
    public bool $is_federation = false;
    public bool $is_admin = false;
    public bool $federationApprove = false;
    public bool $showdivindividual = false;
    public string $codeIndividual = '';
    public $individual = [];
    public string $selected_license_error_individual = '';
    public $certifications = [];
    public ?string $committee_code;

    public function mount($federationId = null)
    {
        $this->federationId = $federationId ?: auth()->user()->getFederationId() ?? null;
        if (! empty($this->federationId)) {
            $this->selectedFederation = $this->federationId;
            $this->searchCertificationsFromFederation();
        }
    }

    public function searchIndividual()
    {
        if (! empty($this->codeIndividual)) {
            $individualSearch = Individual::where('member_code', $this->codeIndividual)
                ->select('member_code', 'name', 'surname', 'id')
                ->IndividualFromFederation($this->selectedFederation)
                ->with('certificationsAttributed.certification')
                ->first();

            if (isset($individualSearch)) {
                foreach ($this->individual as $individual) {
                    if ($individual['id'] == $individualSearch->id) {
                        $this->selected_license_error_individual = __('This student is already selected');

                        return;
                    }
                }

                $this->selected_license_error_individual = '';
                $this->individual[] = $individualSearch;
            } else {
                $this->selected_license_error_individual = __("Can't find a student with that code");
            }

            $this->codeIndividual = '';
        }
    }

    private function searchCertificationsFromFederation(): void
    {
        $getLicensesFromFederation = new GetLicensesFromFederationMembership;
        $getCertificationsByLicenses = new GetCertificationsByLicensesAction;
        $this->certifications = $getCertificationsByLicenses($getLicensesFromFederation($this->selectedFederation));

        $committee_id = Committee::select('id')->where('code', $this->committee_code)->value('id');

        $this->certifications = $this->certifications->where('committee_id', $committee_id)->sortBy('name');
    }

    public function render(): View
    {
        if (! empty(auth()->user()->federations()->first())) {
            $this->selectedFederation = auth()->user()->federations()->first()->id;
        }

        if (! empty($this->selectedFederation)) {
            $this->searchCertificationsFromFederation();
        }

        return view('livewire.get-individual-without-instructor-for-certification');
    }

    public function removeItem($index)
    {
        unset($this->individual[$index]);
        $this->individual = array_values($this->individual); // Re-index the array
    }

}
