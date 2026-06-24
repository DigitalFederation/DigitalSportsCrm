<?php

namespace Domain\OfficialDocuments\Actions;

use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ExpiredOfficialDocumentState;
use Illuminate\Support\Facades\DB;

class SuspendExpiredOfficialDocumentsAction
{
    public function execute(): int
    {
        return DB::transaction(function () {
            $expiredDocuments = OfficialDocument::where('expiry_date', '<=', now()->toDateString())
                ->where('status_class', '!=', ExpiredOfficialDocumentState::class)
                ->get();

            foreach ($expiredDocuments as $document) {
                $document->status_class = ExpiredOfficialDocumentState::class;
                $document->save();

                activity('OfficialDocument')
                    ->performedOn($document)
                    ->event('expired')
                    ->log('Official document expired.');
            }

            return $expiredDocuments->count();
        });
    }
}
