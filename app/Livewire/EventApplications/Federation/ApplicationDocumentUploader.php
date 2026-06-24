<?php

namespace App\Livewire\EventApplications\Federation;

use Domain\EventApplications\Actions\UploadApplicationDocumentAction;
use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\EventApplication;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApplicationDocumentUploader extends Component
{
    use WithFileUploads;

    public EventApplication $application;
    public bool $readonly = false;

    public $file;
    public $document_type = '';

    public $documentTypes = [];

    protected function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'document_type' => 'required|string|max:255',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'file' => __('event_applications.labels.document'),
            'document_type' => __('event_applications.labels.document_type'),
        ];
    }

    public function mount(): void
    {
        $this->loadDocumentTypes();
    }

    protected function loadDocumentTypes(): void
    {
        $this->documentTypes = [
            'proposal' => __('event_applications.document_types.proposal'),
            'budget' => __('event_applications.document_types.budget'),
            'insurance' => __('event_applications.document_types.insurance'),
            'venue_agreement' => __('event_applications.document_types.venue_agreement'),
            'technical_plan' => __('event_applications.document_types.technical_plan'),
            'marketing_plan' => __('event_applications.document_types.marketing_plan'),
            'safety_plan' => __('event_applications.document_types.safety_plan'),
            'other' => __('event_applications.document_types.other'),
        ];
    }

    public function uploadDocument(): void
    {
        if ($this->readonly) {
            Notification::make()
                ->title(__('event_applications.messages.cannot_edit_submitted'))
                ->warning()
                ->send();

            return;
        }

        $this->validate();

        try {
            $uploadAction = app(UploadApplicationDocumentAction::class);

            $uploadAction->execute([
                'file' => $this->file,
                'application_id' => $this->application->id,
                'template_id' => $this->application->template_id,
                'document_type' => $this->document_type,
                'uploaded_by_type' => get_class(auth()->user()),
                'uploaded_by_id' => auth()->id(),
                'is_required' => false,
            ]);

            Notification::make()
                ->title(__('event_applications.document_uploaded_success'))
                ->success()
                ->send();

            $this->reset(['file', 'document_type']);
            $this->application->refresh();

        } catch (\Exception $e) {
            report($e);

            Notification::make()
                ->title(__('event_applications.document_uploaded_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteDocument(ApplicationDocument $document): void
    {
        try {
            $document->delete();

            Notification::make()
                ->title(__('event_applications.document_deleted_success'))
                ->success()
                ->send();

            $this->application->refresh();

        } catch (\Exception $e) {
            report($e);

            Notification::make()
                ->title(__('event_applications.document_deleted_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.event-applications.federation.application-document-uploader', [
            'documents' => $this->application->documents()->latest()->get(),
        ]);
    }
}
