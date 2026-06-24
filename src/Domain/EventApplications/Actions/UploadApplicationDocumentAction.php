<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationDocument;
use Illuminate\Http\UploadedFile;

class UploadApplicationDocumentAction
{
    public function execute(array $data): ApplicationDocument
    {
        $file = $data['file'];

        if (! $file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Invalid file upload');
        }

        $path = $file->store('application-documents', ApplicationDocument::STORAGE_DISK);

        $document = ApplicationDocument::create([
            'application_id' => $data['application_id'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'document_type' => $data['document_type'],
            'uploaded_by_type' => $data['uploaded_by_type'] ?? null,
            'uploaded_by_id' => $data['uploaded_by_id'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_required' => $data['is_required'] ?? false,
        ]);

        return $document;
    }
}
