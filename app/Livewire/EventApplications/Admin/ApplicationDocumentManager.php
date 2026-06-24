<?php

namespace App\Livewire\EventApplications\Admin;

use Domain\EventApplications\Actions\UploadApplicationDocumentAction;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApplicationDocumentManager extends Component
{
    use WithFileUploads;

    public $model;

    public $modelType;

    public $file;

    public $document_type;

    public $description;

    public $showUploadForm = false;

    public function mount($model)
    {
        $this->model = $model;
        $this->modelType = get_class($model);
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'document_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function toggleUploadForm()
    {
        $this->showUploadForm = ! $this->showUploadForm;

        if (! $this->showUploadForm) {
            $this->reset(['file', 'document_type', 'description']);
            $this->resetValidation();
        }
    }

    public function uploadDocument(UploadApplicationDocumentAction $action)
    {
        $this->validate();

        try {
            if ($this->modelType === EventApplication::class) {
                $action->execute(
                    $this->model->id,
                    $this->file,
                    $this->document_type,
                    $this->description
                );
            } elseif ($this->modelType === ApplicationTemplate::class) {
                ApplicationDocument::create([
                    'template_id' => $this->model->id,
                    'filename' => $this->file->getClientOriginalName(),
                    'file_path' => $this->file->store('application-documents', 'public'),
                    'document_type' => $this->document_type,
                    'description' => $this->description,
                ]);
            }

            $this->reset(['file', 'document_type', 'description', 'showUploadForm']);
            $this->model->refresh();

            session()->flash('success', __('event_applications.document_uploaded_success'));
        } catch (\Exception $e) {
            session()->flash('error', __('event_applications.document_uploaded_error'));
        }
    }

    public function deleteDocument($documentId)
    {
        try {
            $document = ApplicationDocument::findOrFail($documentId);

            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            $this->model->refresh();

            session()->flash('success', __('event_applications.document_deleted_success'));
        } catch (\Exception $e) {
            session()->flash('error', __('event_applications.document_deleted_error'));
        }
    }

    public function render()
    {
        return view('livewire.event-applications.admin.application-document-manager');
    }
}
