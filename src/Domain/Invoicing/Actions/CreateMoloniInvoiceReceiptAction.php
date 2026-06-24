<?php

namespace Domain\Invoicing\Actions;

use Domain\Documents\Models\Document;
use Domain\Invoicing\Exceptions\MoloniApiException;
use Domain\Invoicing\Exceptions\MoloniAuthenticationException;
use Domain\Invoicing\Exceptions\MoloniNotConfiguredException;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Services\MoloniInvoiceService;
use Domain\Invoicing\Services\MoloniSettingsService;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class CreateMoloniInvoiceReceiptAction
{
    public function __construct(
        private MoloniInvoiceService $invoiceService,
        private MoloniSettingsService $settingsService
    ) {}

    public function __invoke(Document $document, ?PaymentTransaction $transaction = null): ?MoloniInvoice
    {
        if (! $this->settingsService->isEnabled()) {
            Log::debug('CreateMoloniInvoiceReceiptAction: Moloni integration is disabled');

            return null;
        }

        if (! $document->isPaid()) {
            Log::warning('CreateMoloniInvoiceReceiptAction: Document is not paid, skipping', [
                'document_id' => $document->id,
                'status' => $document->status_class,
            ]);

            return null;
        }

        if (! $document->owner) {
            Log::warning('CreateMoloniInvoiceReceiptAction: Document has no owner, skipping', [
                'document_id' => $document->id,
            ]);

            return null;
        }

        if (MoloniInvoice::existsForDocument($document->id)) {
            Log::info('CreateMoloniInvoiceReceiptAction: Invoice already exists, skipping', [
                'document_id' => $document->id,
            ]);

            return MoloniInvoice::findByDocument($document->id);
        }

        if (! $this->settingsService->isConfigured()) {
            Log::warning('CreateMoloniInvoiceReceiptAction: Moloni not configured, skipping', [
                'document_id' => $document->id,
            ]);

            MoloniSyncLog::logFailure('invoice_create', 'Moloni not configured', [
                'document_id' => $document->id,
            ]);

            return null;
        }

        try {
            Log::info('CreateMoloniInvoiceReceiptAction: Creating invoice', [
                'document_id' => $document->id,
                'document_number' => $document->number_extended,
                'owner_type' => $document->owner_type,
                'owner_id' => $document->owner_id,
                'total_value' => $document->total_value,
            ]);

            $moloniInvoice = $this->invoiceService->createInvoiceReceipt($document);

            Log::info('CreateMoloniInvoiceReceiptAction: Invoice created successfully', [
                'document_id' => $document->id,
                'moloni_document_id' => $moloniInvoice->moloni_document_id,
                'moloni_number' => $moloniInvoice->moloni_number,
            ]);

            return $moloniInvoice;

        } catch (MoloniNotConfiguredException $e) {
            Log::warning('CreateMoloniInvoiceReceiptAction: Moloni not configured', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            return null;

        } catch (MoloniAuthenticationException $e) {
            Log::error('CreateMoloniInvoiceReceiptAction: Authentication error', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } catch (MoloniApiException $e) {
            Log::error('CreateMoloniInvoiceReceiptAction: API error', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'endpoint' => $e->getEndpoint(),
            ]);

            MoloniSyncLog::logFailure('invoice_create', $e->getMessage(), [
                'document_id' => $document->id,
                'document_number' => $document->number_extended,
                'endpoint' => $e->getEndpoint(),
            ]);

            throw $e;
        } catch (\RuntimeException $e) {
            // Catch customer creation failures and other runtime errors
            Log::error('CreateMoloniInvoiceReceiptAction: Runtime error', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            MoloniSyncLog::logFailure('invoice_create', $e->getMessage(), [
                'document_id' => $document->id,
                'document_number' => $document->number_extended,
            ]);

            throw $e;
        } catch (\Exception $e) {
            // Catch any other unexpected errors
            Log::error('CreateMoloniInvoiceReceiptAction: Unexpected error', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            MoloniSyncLog::logFailure('invoice_create', $e->getMessage(), [
                'document_id' => $document->id,
                'document_number' => $document->number_extended,
                'exception_class' => get_class($e),
            ]);

            throw $e;
        }
    }
}
