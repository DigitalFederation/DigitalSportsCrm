<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationDocument;
use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Http\UploadedFile;

class UploadTemplateDocumentAction
{
    public function execute(ApplicationTemplate $template, UploadedFile $file, ?string $customName = null): ApplicationDocument
    {
        // Store the file
        $path = $file->store('application-templates/'.$template->id, 'public');

        $fileName = $customName ?: $file->getClientOriginalName();

        // Create document record
        $document = ApplicationDocument::create([
            'template_id' => $template->id,
            'file_name' => $fileName,
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => 'template_guide', // Templates always use 'template_guide'
            'uploaded_by_type' => 'App\Models\User',
            'uploaded_by_id' => auth()->id(),
        ]);

        return $document;
    }
}
