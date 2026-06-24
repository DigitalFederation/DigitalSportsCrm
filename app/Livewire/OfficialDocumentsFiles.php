<?php

namespace App\Livewire;

use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Actions\CreateOfficialDocumentAction;
use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class OfficialDocumentsFiles extends Component
{
    use WithFileUploads;

    public $individual;

    public $types;

    public $model;

    public $files;

    public $countries;

    public $attachments = [];

    public $type = '';

    public $issue_date = null;

    public $expiry_date = null;

    public $message;

    public function mount($individual, $types, $model, $files, $countries = null): void
    {
        $this->individual = $individual;
        $this->types = $this->normalizeTypes($types);
        $this->model = $model;
        $this->files = $files;
        $this->countries = $countries;
    }

    /**
     * Normalize types to a flat array of enum value strings.
     * Handles both flat arrays of OfficialDocumentTypeEnum cases
     * and nested config arrays grouped by category.
     * Sorts alphabetically by translated name.
     */
    private function normalizeTypes($types): array
    {
        $normalized = [];

        foreach ($types as $key => $value) {
            if ($value instanceof \App\Enums\OfficialDocumentTypeEnum) {
                $normalized[] = $value->value;
            } elseif (is_array($value)) {
                foreach ($value as $innerValue) {
                    $normalized[] = is_string($innerValue) ? $innerValue : (string) $innerValue;
                }
            } elseif (is_string($value)) {
                $normalized[] = $value;
            }
        }

        $uniqueTypes = array_unique($normalized);

        usort($uniqueTypes, fn (string $a, string $b) => strcmp(
            \App\Enums\OfficialDocumentTypeEnum::toString($a),
            \App\Enums\OfficialDocumentTypeEnum::toString($b)
        ));

        return $uniqueTypes;
    }

    public function save(): void
    {
        $isAdel = $this->type === \App\Enums\OfficialDocumentTypeEnum::ADELCertificate->value;

        $this->validate([
            'attachments.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'type' => 'required',
            'issue_date' => $isAdel ? 'required|date' : 'nullable|date',
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

        $federation = Federation::where('is_default_federation', true)->first();

        if (! $federation) {
            session()->flash('error', __('Default federation not configured properly.'));
            $this->message = __('Default federation not configured properly');

            return;
        }

        $officialDocumentData = OfficialDocumentData::fromArray([
            'name' => null,
            'individual_id' => $this->individual->id,
            'country_id' => (int) $federation->country_id,
            'type' => $this->type,
            'federation_id' => $federation->id,
            'status_class' => $isAdel
                ? \Domain\OfficialDocuments\States\ActiveOfficialDocumentState::class
                : \Domain\OfficialDocuments\States\PendingOfficialDocumentState::class,
            'expiry_date' => $this->expiry_date,
            'issue_date' => $this->issue_date,
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

        $this->reset('attachments', 'type', 'issue_date', 'expiry_date');

        $this->refreshDocuments();
    }

    public function refreshDocuments(): void
    {
        $this->model = OfficialDocument::with('media')
            ->where('individual_id', $this->individual->id)
            ->latest()
            ->get();

        $this->files = $this->model->map(function ($document) {
            return $document->getMedia('media');
        });
    }

    public function render(): View
    {
        return view('livewire.official-documents-files');
    }
}
