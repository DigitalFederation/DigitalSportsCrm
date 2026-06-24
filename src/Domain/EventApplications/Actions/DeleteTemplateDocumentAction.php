<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationDocument;
use Illuminate\Support\Facades\Storage;

class DeleteTemplateDocumentAction
{
    public function execute(ApplicationDocument $document): bool
    {
        // Delete the file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete the document record
        return $document->delete();
    }
}
