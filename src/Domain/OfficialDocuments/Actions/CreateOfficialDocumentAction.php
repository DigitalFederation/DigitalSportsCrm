<?php

namespace Domain\OfficialDocuments\Actions;

use App\Notifications\OfficialDocumentCreatedNotification;
use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Support\Facades\Notification;

class CreateOfficialDocumentAction
{
    /**
     * Store official document using OfficialDocumentData.
     */
    public function __invoke(OfficialDocumentData $officialDocumentData): OfficialDocument
    {
        $officialDocument = $officialDocumentData->toModel();
        $officialDocument->save();

        // Log the activity
        activity('OfficialDocument')
            ->performedOn($officialDocument)
            ->event('created')
            ->withProperties($officialDocumentData->toArray())
            ->log('New official document has been sent: '.$officialDocument->name);

        // Send notifications based on document owner type
        if ($officialDocument->individual_id) {
            // For individual documents
            $individual = $officialDocument->individual()->first();
            if ($individual) {
                $federations = $individual->federations()->get();
                foreach ($federations as $federation) {
                    Notification::send($federation->users()->get(), new OfficialDocumentCreatedNotification($officialDocument));
                }
            }
        } elseif ($officialDocument->owner_type === (new \Domain\Entities\Models\Entity)->getMorphClass() && $officialDocument->owner_id) {
            // For entity documents
            $entity = $officialDocument->owner;
            if ($entity && $officialDocument->federation_id) {
                $federation = $officialDocument->federation;
                if ($federation) {
                    Notification::send($federation->users()->get(), new OfficialDocumentCreatedNotification($officialDocument));
                }
            }
        } elseif ($officialDocument->federation_id && ! $officialDocument->individual_id) {
            // For federation documents
            $federation = $officialDocument->federation;
            if ($federation) {
                Notification::send($federation->users()->get(), new OfficialDocumentCreatedNotification($officialDocument));
            }
        }

        return $officialDocument;
    }
}
