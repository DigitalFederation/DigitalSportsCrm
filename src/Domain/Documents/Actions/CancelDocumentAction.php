<?php

namespace Domain\Documents\Actions;

use Domain\Documents\Models\Document;
use Domain\Documents\States\CanceledDocumentState;
use Domain\EvtEvents\Models\Enrollment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelDocumentAction
{
    /**
     * Execute the action.
     */
    public function execute(Document $document, ?string $reason = null): bool
    {
        if (! $document->state->isPending()) {
            throw new Exception('Only documents in Pending state can be canceled.');
        }

        DB::beginTransaction();

        try {
            // Here you can apply additional logic if needed
            // For example, sending notifications or logging the cancellation reason

            // Update the document state to Canceled
            $document->status_class = CanceledDocumentState::class;
            $document->save();

            // Unlink any enrollments referencing this document so a new one can be generated
            Enrollment::where('document_id', $document->id)->update(['document_id' => null]);

            Log::info('Document has been cancelled.', ['document_id' => $document->id]);

            // Optionally, store the reason for cancellation if provided

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to cancel the document: '.$e->getMessage());
        }
    }
}
