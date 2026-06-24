<?php

namespace App\Livewire;

use Domain\OfficialDocuments\Actions\CreateFederationOfficialDocumentAction;
use Domain\OfficialDocuments\Actions\CreateOfficialDocumentAction;
use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class OfficialDocumentsFederationUpload extends Component
{
    use WithFileUploads;

    public $attachments = [];
    public $selectedIndividuals = [];
    public $individuals;
    public $types;
    public $type = '';
    public $federations;
    public $mainFederations;
    public $localFederations;
    public $federation_id = '';
    public $local_federation_id = '';
    public $country_id = '';
    public array $validationErrors = [];
    public $message;
    public $role;
    public $isForIndividual = false;
    public $issue_date = null;
    public $expiry_date = null;

    protected $listeners = [
        'selectedMultipleUpdatedValue.official_document_individuals' => 'handleSelectedIndividualsUpdate',
        'individualsSelected' => 'handleIndividualsSelected',
    ];

    public function mount($individuals, $types, $federations, $role = null): void
    {
        $types = $types instanceof \Illuminate\Support\Collection ? $types->all() : $types;
        asort($types);
        $this->types = $types;
        $this->federations = $federations;
        $this->individuals = $individuals ?? collect();
        $this->role = $role;
        $this->isForIndividual = ! empty($individuals);
    }

    public function updatedSelectedIndividuals($value): void
    {
        // This will be triggered when the SelectMultiple component updates the selected individuals
        $this->selectedIndividuals = $value;
    }

    public function updatedFederationId($federationId): void
    {
        $this->localFederations = $this->federations->where('parent_id', $federationId);
        $this->local_federation_id = '';
    }

    public function save()
    {
        $this->validate([
            'selectedIndividuals' => $this->isForIndividual ? 'required|array|min:1' : 'nullable',
            'type' => 'required',
            'attachments' => 'required|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'federation_id' => 'sometimes|exists:federation,id',
            'local_federation_id' => 'sometimes|exists:federation,id',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ], [
            'selectedIndividuals.required' => 'Please select at least one individual.',
            'selectedIndividuals.min' => 'Please select at least one individual.',
            'attachments.*.max' => 'Each file must be less than 5MB',
            'attachments.*.mimes' => 'Allowed file types: jpg, jpeg, png, pdf, doc, docx',
            'expiry_date.after_or_equal' => 'The expiry date must be after or equal to the issue date',
        ]);

        try {
            DB::beginTransaction();

            $federation = $this->federations->first();
            $federationCountryId = $federation->country_id;
            $selectedFederationId = $federation->id;

            if ($this->isForIndividual && ! empty($this->selectedIndividuals)) {
                foreach ($this->selectedIndividuals as $individualId) {
                    $documentData = OfficialDocumentData::fromArray([
                        'name' => null,
                        'individual_id' => $individualId,
                        'country_id' => $federationCountryId,
                        'type' => $this->type,
                        'federation_id' => $selectedFederationId,
                        'status_class' => PendingOfficialDocumentState::class,
                        'expiry_date' => $this->expiry_date,
                        'issue_date' => $this->issue_date,
                        'role' => $this->role,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);

                    $action = new CreateOfficialDocumentAction;
                    $document = $action($documentData);

                    foreach ($this->attachments as $attachment) {
                        if (! $attachment->exists()) {
                            throw new \Exception('Uploaded file not found: ' . $attachment->getFilename());
                        }

                        $document->addMedia($attachment->getRealPath())
                            ->usingName($attachment->getClientOriginalName())
                            ->toMediaCollection('media');
                    }
                }
            } else {
                // Handle federation document
                $documentData = OfficialDocumentData::fromArray([
                    'name' => null,
                    'individual_id' => null,
                    'country_id' => $federationCountryId,
                    'type' => $this->type,
                    'federation_id' => $selectedFederationId,
                    'status_class' => PendingOfficialDocumentState::class,
                    'expiry_date' => $this->expiry_date,
                    'issue_date' => $this->issue_date,
                    'role' => null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $action = new CreateFederationOfficialDocumentAction;
                $document = $action($documentData);

                foreach ($this->attachments as $attachment) {
                    if (! $attachment->exists()) {
                        throw new \Exception('Uploaded file not found: ' . $attachment->getFilename());
                    }

                    $document->addMedia($attachment->getRealPath())
                        ->usingName($attachment->getClientOriginalName())
                        ->toMediaCollection('media');
                }
            }

            DB::commit();
            session()->flash('success', __('File(s) uploaded successfully!'));
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Document upload failed: ' . $e->getMessage());
            session()->flash('error', __('Error uploading file: :error', ['error' => $e->getMessage()]));
        }

        $this->reset('attachments');

        return redirect(request()->header('Referer'));
    }

    public function handleIndividualsSelected($selected)
    {
        $this->selectedIndividuals = $selected;
    }

    public function render()
    {
        return view('livewire.official-documents-federation-upload');
    }
}
