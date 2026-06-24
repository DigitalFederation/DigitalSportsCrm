<?php

namespace App\Livewire;

use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Actions\CreateOfficialDocumentAction;
use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class OfficialDocumentsUpload extends Component
{
    use WithFileUploads;

    public $attachments = [];
    public $individual;
    public $types; // Document types
    public $type = ''; // Selected document type
    public $federations; // All federations
    public $mainFederations; // Main federations
    public $localFederations; // Local federations
    public $federation_id = ''; // Selected federation
    public $local_federation_id = '';
    public $country_id = ''; // Selected country
    public $issue_date = null; // Issue date for ADEL certificates
    public $expiry_date = null; // Expiry date for documents
    public array $validationErrors = [];
    public $message;
    public $role;

    public function mount($individual, $types, $federations, $role): void
    {
        $this->individual = $individual;
        $this->types = \App\Enums\OfficialDocumentTypeEnum::sortedByTranslation($types);
        $this->federations = $federations;
        $this->mainFederations = $federations->where('is_local', false);
        $this->localFederations = collect();
        $this->role = $role;
    }

    public function updatedFederationId($federationId): void
    {
        $this->localFederations = $this->federations->where('parent_id', $federationId);
        $this->local_federation_id = ''; // Reset local federation selection
    }

    public function save()
    {
        $isAdel = $this->type === \App\Enums\OfficialDocumentTypeEnum::ADELCertificate->value;
        $requiresIssueDate = $isAdel || in_array($this->role, ['coach', 'diving-professional']);

        $this->validationErrors = [];

        // Validate using Livewire's built-in validation
        $this->validate([
            'attachments.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'type' => 'required',
            'issue_date' => $requiresIssueDate ? 'required|date' : 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
        ], [
            'attachments.*.required' => __('Please select at least one file to upload'),
            'attachments.*.file' => __('Invalid file format'),
            'attachments.*.mimes' => __('Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX'),
            'attachments.*.max' => __('File size exceeds 10MB limit'),
            'type.required' => __('Please select a document type'),
            'issue_date.required' => __('Issue date is required'),
            'expiry_date.after' => __('Expiry date must be after issue date'),
        ]);

        // Use the default/main federation
        $federation = Federation::where('is_default_federation', true)->first();

        if (! $federation) {
            session()->flash('error', __('Default federation not configured properly.'));
            $this->message = __('Default federation not configured properly');

            return;
        }

        $selectedFederationId = $federation->id;
        $federationCountryId = (int) $federation->country_id;

        $officialDocumentData = OfficialDocumentData::fromArray([
            'name' => null,
            'individual_id' => $this->individual->id,
            'country_id' => $federationCountryId,
            'type' => $this->type,
            'federation_id' => $selectedFederationId,
            'status_class' => $isAdel ? \Domain\OfficialDocuments\States\ActiveOfficialDocumentState::class : \Domain\OfficialDocuments\States\PendingOfficialDocumentState::class,
            'expiry_date' => $this->expiry_date,
            'issue_date' => $this->issue_date,
            'role' => $this->role,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        try {
            DB::beginTransaction();
            $createAction = new CreateOfficialDocumentAction;
            $odModel = $createAction($officialDocumentData);

            foreach ($this->attachments as $attachment) {
                $odModel->addMedia($attachment)->toMediaCollection('media');
            }

            DB::commit();
            session()->flash('success', __('File(s) uploaded successfully!'));
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', __('Error uploading file') . ': ' . $e->getMessage());
        }

        $this->reset('attachments');

        return redirect(request()->header('Referer'));
    }

    public function render(): View
    {
        return view('livewire.official-documents-upload');
    }
}
