<?php

namespace App\Livewire\Individual;

use App\Enums\OfficialDocumentTypeEnum;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\Actions\CreateOfficialDocumentAction;
use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadOfficialDocument extends Component
{
    use WithFileUploads;

    public Individual $individual;
    public $attachment;
    public $type = '';
    public $issue_date;
    public $expiry_date;
    public $showForm = false;
    public $uploadSuccess = false;

    protected $rules = [
        'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        'type' => 'required|string',
        'issue_date' => 'nullable|date',
        'expiry_date' => 'nullable|date|after:issue_date',
    ];

    public function mount(Individual $individual)
    {
        $this->individual = $individual;
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->uploadSuccess = false;
        $this->resetValidation();
    }

    public function updatedType($value)
    {
        // Auto-require issue date for ADEL certificates
        if ($value === OfficialDocumentTypeEnum::ADELCertificate->value) {
            $this->rules['issue_date'] = 'required|date';
        } else {
            $this->rules['issue_date'] = 'nullable|date';
        }
    }

    public function save()
    {
        $this->validate();

        // Always use the main federation (federation with is_local = false)
        $federation = Federation::where('is_local', false)->first();

        if (! $federation) {
            // Fallback: get any federation
            $federation = Federation::first();
        }

        if (! $federation) {
            $this->addError('federation', 'No federation found in the system.');

            return;
        }

        $officialDocumentData = OfficialDocumentData::fromArray([
            'name' => null,
            'individual_id' => $this->individual->id,
            'country_id' => $federation->country_id,
            'type' => $this->type,
            'federation_id' => $federation->id,
            'status_class' => $this->type === OfficialDocumentTypeEnum::ADELCertificate->value
                ? \Domain\OfficialDocuments\States\ActiveOfficialDocumentState::class
                : PendingOfficialDocumentState::class,
            'expiry_date' => $this->expiry_date,
            'issue_date' => $this->issue_date,
            'role' => $this->individual->professionalRoles()->first()?->role ?? 'individual',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        try {
            DB::beginTransaction();

            $createAction = new CreateOfficialDocumentAction;
            $document = $createAction($officialDocumentData);

            // Attach the file
            $document->addMedia($this->attachment)->toMediaCollection('media');

            DB::commit();

            $this->uploadSuccess = true;
            $this->reset(['attachment', 'type', 'issue_date', 'expiry_date']);
            $this->dispatch('official-document-uploaded');

        } catch (Exception $e) {
            DB::rollBack();
            $this->addError('attachment', 'Error uploading document: ' . $e->getMessage());
        }
    }

    public function getAvailableTypesProperty()
    {
        // Get common individual document types
        $types = collect([
            OfficialDocumentTypeEnum::MedicalStatement,
            OfficialDocumentTypeEnum::ADELCertificate,
        ]);

        // Add insurance types
        $types->push(OfficialDocumentTypeEnum::InsuranceAthlete);
        $types->push(OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance);

        // Add code of conduct types
        $types->push(OfficialDocumentTypeEnum::DivingProfessionalCodeOfConduct);
        $types->push(OfficialDocumentTypeEnum::InternationalAthleteCodeOfConduct);
        $types->push(OfficialDocumentTypeEnum::InternationalCoachCodeOfConduct);
        $types->push(OfficialDocumentTypeEnum::InternationalRefereeJudgeCodeOfConduct);
        $types->push(OfficialDocumentTypeEnum::TeamOfficialCodeOfConduct);

        // Check if user is admin or entity to allow additional types
        if (Auth::user()->hasRole('admin') || Auth::user()->entities()->exists()) {
            $types->push(OfficialDocumentTypeEnum::OtherDocument);
        }

        return $types->unique()->sortBy(fn ($type) => OfficialDocumentTypeEnum::toString($type->value));
    }

    public function render(): View
    {
        return view('livewire.individual.upload-official-document');
    }
}
