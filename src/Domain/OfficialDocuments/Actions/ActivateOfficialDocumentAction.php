<?php

namespace Domain\OfficialDocuments\Actions;

use App\Notifications\OfficialDocumentActivatedNotification;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;

class ActivateOfficialDocumentAction
{
    public function __invoke(OfficialDocument $document, ?string $expireDate, ?string $startDate = null): void
    {
        $updateData = [
            'status_class' => ActiveOfficialDocumentState::class,
            'activated_at' => now(),
            'expiry_date' => $expireDate,
        ];

        if ($startDate) {
            $updateData['issue_date'] = $startDate;
        }

        $document->update($updateData);

        activity('Official Document')
            ->performedOn($document)
            ->event('approved')
            ->withProperties($document->toArray())
            ->log('Official document was approved:'.$document->name);

        if (! empty($document->individual) && ! empty($document->individual->user)) {
            $document->individual->user->notify(new OfficialDocumentActivatedNotification($document));
        }
    }
}
