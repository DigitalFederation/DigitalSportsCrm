<?php

namespace App\Livewire\Individual;

use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class DivingCertificationUpload extends Component
{
    use WithFileUploads;

    public $showForm = false;
    public $uploadSuccess = false;

    // Form fields
    public $certification_name;
    public $certification_system;
    public $certification_level;
    public $certification_number;
    public $national_equivalency;
    public $issue_date;
    public $expiration_date;
    public $certificate_document;

    protected function rules(): array
    {
        return [
            'certification_name' => 'required|string|max:255',
            'certification_system' => 'required|in:'.implode(',', config('diving.certification_systems')),
            'certification_level' => 'required|string|max:255',
            'certification_number' => 'required|string|max:255',
            'national_equivalency' => 'nullable|string|max:255',
            'issue_date' => 'required|date|before_or_equal:today',
            'expiration_date' => 'nullable|date|after:issue_date',
            'certificate_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    protected $messages = [
        'certification_name.required' => 'The certification name is required.',
        'certification_system.required' => 'Please select a certification system.',
        'certification_level.required' => 'The certification level is required.',
        'certification_number.required' => 'The certification number is required.',
        'issue_date.required' => 'The issue date is required.',
        'issue_date.before_or_equal' => 'The issue date cannot be in the future.',
        'expiration_date.after' => 'The expiration date must be after the issue date.',
        'certificate_document.required' => 'Please upload the certificate document.',
        'certificate_document.mimes' => 'The document must be a PDF, JPG, JPEG, or PNG file.',
        'certificate_document.max' => 'The document must not exceed 5MB.',
    ];

    public function mount()
    {
        $this->certification_system = '';
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        if (! $this->showForm) {
            $this->reset(['uploadSuccess']);
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->reset([
            'certification_name',
            'certification_system',
            'certification_level',
            'certification_number',
            'national_equivalency',
            'issue_date',
            'expiration_date',
            'certificate_document',
        ]);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $individual = auth()->user()->individuals()->first();

            $certification = DivingProfessionalCertification::create([
                'individual_id' => $individual->id,
                'certification_name' => $this->certification_name,
                'certification_system' => $this->certification_system,
                'certification_level' => $this->certification_level,
                'certification_number' => $this->certification_number,
                'national_equivalency' => $this->national_equivalency,
                'issue_date' => $this->issue_date,
                'expiration_date' => $this->expiration_date,
                'status_class' => PendingValidationDivingCertificationState::class,
            ]);

            if ($this->certificate_document) {
                $certification->addMedia($this->certificate_document->getRealPath())
                    ->usingName($this->certificate_document->getClientOriginalName())
                    ->toMediaCollection('certificate_documents');
            }

            DB::commit();

            $this->uploadSuccess = true;
            $this->resetForm();
            $this->showForm = false;

            activity('DivingCertification')
                ->causedBy(auth()->user())
                ->performedOn($certification)
                ->event('created')
                ->log('Diving certification submitted for validation');

            $this->dispatch('certification-uploaded');
            session()->flash('success', __('Certification uploaded successfully and is pending validation.'));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('certificate_document', __('Failed to upload certification. Please try again.'));
        }
    }

    public function render(): View
    {
        $certificationSystems = [
            'SSI' => 'SSI',
            'PADI' => 'PADI',
            'SDI_TDI' => 'SDI/TDI',
            'DDI' => 'DDI',
            'GUE' => 'GUE',
        ];

        return view('livewire.individual.diving-certification-upload', [
            'certificationSystems' => $certificationSystems,
        ]);
    }
}
