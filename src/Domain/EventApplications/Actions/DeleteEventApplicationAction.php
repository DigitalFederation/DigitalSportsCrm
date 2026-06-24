<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\EventApplication;
use Illuminate\Support\Facades\Storage;

class DeleteEventApplicationAction
{
    public function execute(EventApplication $application): void
    {
        foreach ($application->documents as $document) {
            if ($document->file_path) {
                Storage::disk('secure-media')->delete($document->file_path);
            }
        }

        $application->clearMediaCollection('application-attachments');

        $application->forceDelete();
    }
}
