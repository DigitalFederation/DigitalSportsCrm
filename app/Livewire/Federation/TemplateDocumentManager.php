<?php

namespace App\Livewire\Federation;

use Domain\EventApplications\Actions\DeleteTemplateDocumentAction;
use Domain\EventApplications\Actions\UploadTemplateDocumentAction;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationTemplate;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class TemplateDocumentManager extends Component
{
    use WithFileUploads;

    public ApplicationTemplate $template;

    public $newAttachment;

    public ?string $newAttachmentName = null;

    public bool $confirmingDeletion = false;

    public ?int $documentIdToRemove = null;

    public string $successMessage = '';

    public string $errorMessage = '';

    public function mount(ApplicationTemplate $template): void
    {
        $this->template = $template;
    }

    public function saveAttachment(UploadTemplateDocumentAction $action): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $this->validate([
            'newAttachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'newAttachmentName' => 'nullable|string|max:255',
        ], [
            'newAttachment.required' => __('event_applications.validation.file_required'),
            'newAttachment.mimes' => __('event_applications.validation.file_mimes'),
            'newAttachment.max' => __('event_applications.validation.file_max'),
        ]);

        try {
            DB::beginTransaction();

            $action->execute($this->template, $this->newAttachment, $this->newAttachmentName);

            DB::commit();

            $this->template->refresh();
            $this->template->load('documents');

            $this->successMessage = __('event_applications.document_uploaded_success');

            $this->reset(['newAttachment', 'newAttachmentName']);
            $this->resetValidation();
        } catch (Exception $ex) {
            DB::rollBack();

            Log::error('Error uploading template document: '.$ex->getMessage(), [
                'exception' => $ex,
                'template_id' => $this->template->id,
            ]);

            $this->addError('newAttachment', __('event_applications.document_uploaded_error').' '.$ex->getMessage());
        }
    }

    public function confirmDelete(int $documentId): void
    {
        $this->confirmingDeletion = true;
        $this->documentIdToRemove = $documentId;
    }

    public function delete(DeleteTemplateDocumentAction $action): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $document = ApplicationDocument::find($this->documentIdToRemove);

        if (! $document || $document->template_id !== $this->template->id) {
            $this->errorMessage = __('event_applications.document_not_found');
            $this->confirmingDeletion = false;

            return;
        }

        try {
            DB::beginTransaction();

            $action->execute($document);

            DB::commit();

            $this->template->refresh();
            $this->template->load('documents');

            $this->successMessage = __('event_applications.document_deleted_success');
        } catch (Exception $ex) {
            DB::rollBack();

            Log::error('Error deleting template document: '.$ex->getMessage(), [
                'exception' => $ex,
                'document_id' => $document->id,
                'template_id' => $this->template->id,
            ]);

            $this->errorMessage = __('event_applications.document_deleted_error').' '.$ex->getMessage();
        }

        $this->confirmingDeletion = false;
        $this->documentIdToRemove = null;
    }

    public function render(): View
    {
        return view('livewire.federation.template-document-manager');
    }
}
