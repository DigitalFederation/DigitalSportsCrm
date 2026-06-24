<?php

namespace App\Livewire\Entity;

use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Actions\CreateOfficialDocumentAction;
use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class OfficialDocumentsUpload extends Component
{
    use WithFileUploads;

    public $attachments = [];
    public $entity;
    public $types; // Document types
    public $type = ''; // Selected document type
    public $country_id = ''; // Selected country
    public $issue_date = null; // Issue date
    public $expiry_date = null; // Expiry date for documents
    public array $validationErrors = [];
    public $message;

    public function mount($entity, $types, $federations): void
    {
        $this->entity = $entity;
        $this->types = \App\Enums\OfficialDocumentTypeEnum::sortedByTranslation($types);
    }

    public function save()
    {
        $this->validationErrors = [];
        $validator = Validator::make([
            'attachments' => $this->attachments,
            'type' => $this->type,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
        ], [
            'attachments' => 'required|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'type' => 'required',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
        ]);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->getMessages();

            if (isset($this->validationErrors['attachments'])) {
                $this->message = __('official_documents.upload_error') . ': ' . implode(', ', $this->validationErrors['attachments']);
            } else {
                $keys = array_keys($this->validationErrors);
                $this->message = __('official_documents.upload_error') . ': ' . $keys[0] . ' ' . $this->validationErrors[$keys[0]][0];
            }

            return;
        }

        // Get the main federation
        $mainFederation = Federation::where('is_default_federation', true)->first();

        if (! $mainFederation) {
            session()->flash('error', __('official_documents.main_federation_not_found'));
            $this->message = __('official_documents.main_federation_not_found');

            return;
        }

        $selectedFederationId = $mainFederation->id;
        $federationCountryId = $mainFederation->country_id ?: $this->entity->country_id;

        $officialDocumentData = OfficialDocumentData::fromArray([
            'name' => null,
            'individual_id' => null, // Not used for entities
            'owner_type' => $this->entity->getMorphClass(),
            'owner_id' => $this->entity->id,
            'country_id' => $federationCountryId,
            'type' => $this->type,
            'federation_id' => $selectedFederationId,
            'status_class' => PendingOfficialDocumentState::class,
            'expiry_date' => $this->expiry_date,
            'issue_date' => $this->issue_date,
            'role' => 'entity',
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
            session()->flash('success', __('official_documents.upload_success'));
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', __('official_documents.upload_error') . ': ' . $e->getMessage());
        }

        $this->reset(['attachments', 'type', 'issue_date', 'expiry_date']);

        return redirect(request()->header('Referer'));
    }

    public function render(): View
    {
        return view('livewire.entity.official-documents-upload');
    }
}
