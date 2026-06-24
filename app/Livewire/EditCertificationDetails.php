<?php

namespace App\Livewire;

use Domain\Certifications\Actions\ActivateCertificationAttributedByFederationAction;
use Domain\Certifications\Models\CertificationAttributed;
use Livewire\Component;

class EditCertificationDetails extends Component
{
    public $certificationId;

    public $current_term_starts_at;

    public $national_code;

    public $current_term_ends_at;

    public $certification;

    protected $rules = [
        'current_term_starts_at' => 'sometimes|date',
        'national_code' => 'required|string|max:255',
        'current_term_ends_at' => 'nullable|date',
    ];

    public function mount(CertificationAttributed $certification)
    {
        $this->certification = $certification;
        $this->certificationId = $certification->id;
        $this->current_term_starts_at = $certification->current_term_starts_at;
        $this->national_code = $certification->national_code;
        $this->current_term_ends_at = $certification->current_term_ends_at;
    }

    public function updateCertificationDetails()
    {
        $this->validate();

        try {
            $activateAction = app()->make(ActivateCertificationAttributedByFederationAction::class);
            $activateAction($this->certification, 1);

            $this->certification->update([
                'current_term_starts_at' => $this->current_term_starts_at,
                'national_code' => $this->national_code,
                'current_term_ends_at' => $this->current_term_ends_at,
            ]);

            session()->flash('success', __('certifications.actions.details_updated'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->dispatch('details-updated');

    }

    public function render()
    {
        return view('livewire.edit-certification-details');
    }
}
