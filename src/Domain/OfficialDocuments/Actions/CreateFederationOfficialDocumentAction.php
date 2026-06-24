<?php

namespace Domain\OfficialDocuments\Actions;

use Domain\OfficialDocuments\DataTransferObject\OfficialDocumentData;
use Domain\OfficialDocuments\Models\OfficialDocument;

class CreateFederationOfficialDocumentAction
{
    /**
     * Store an official document uploaded by a Federation.
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
            ->log('New official document uploaded by Federation: ' . $officialDocument->name);

        return $officialDocument;
    }
}
