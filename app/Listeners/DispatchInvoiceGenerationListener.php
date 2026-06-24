<?php

namespace App\Listeners;

use App\Events\DocumentMarkedAsPaid;
use App\Jobs\GenerateExternalInvoiceJob;
use Domain\Invoicing\Services\MoloniSettingsService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Log;

/**
 * Listener that dispatches the invoice generation job when a document is marked as paid.
 *
 * This listener implements ShouldQueueAfterCommit to ensure the job is only
 * dispatched after the database transaction has been committed, preventing
 * race conditions where the job runs before data is persisted.
 *
 * The listener respects the createMoloniInvoice flag:
 * - For webhook payments: always creates invoice (flag is true by default)
 * - For manual payments: creates invoice only if admin checked the option
 *
 * Additionally, the listener checks the document's detail types against the
 * configured invoice generation rules (e.g., skip invoices for licenses).
 *
 * @see \App\Events\DocumentMarkedAsPaid
 * @see \App\Jobs\GenerateExternalInvoiceJob
 */
class DispatchInvoiceGenerationListener implements ShouldQueueAfterCommit
{
    public function __construct(
        private MoloniSettingsService $settingsService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(DocumentMarkedAsPaid $event): void
    {
        Log::info('DispatchInvoiceGenerationListener: Dispatching invoice generation job', [
            'document_id' => $event->document->id,
            'document_number' => $event->document->number_extended,
            'transaction_id' => $event->transaction?->id,
            'source' => $event->source,
            'amount' => $event->getAmount(),
        ]);

        GenerateExternalInvoiceJob::dispatch(
            $event->document,
            $event->transaction,
            $event->webhookData
        );
    }

    /**
     * Determine whether the listener should be queued.
     *
     * Only dispatch the job if:
     * - The createMoloniInvoice flag is true
     * - The document's detail types have invoice generation enabled
     */
    public function shouldQueue(DocumentMarkedAsPaid $event): bool
    {
        // Only create Moloni invoice if explicitly requested
        if (! $event->createMoloniInvoice) {
            Log::info('DispatchInvoiceGenerationListener: Skipping invoice generation (not requested)', [
                'document_id' => $event->document->id,
                'source' => $event->source,
            ]);

            return false;
        }

        // Check if document's detail types should generate invoices
        if (! $this->settingsService->shouldGenerateInvoiceForDocument($event->document)) {
            $event->document->loadMissing('details');
            $detailTypes = $event->document->details->pluck('owner_type')->unique()->values()->toArray();

            Log::info('DispatchInvoiceGenerationListener: Skipping invoice generation (detail types not enabled)', [
                'document_id' => $event->document->id,
                'document_number' => $event->document->number_extended,
                'source' => $event->source,
                'detail_owner_types' => $detailTypes,
            ]);

            return false;
        }

        return true;
    }
}
