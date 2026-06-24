<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\MoloniInvoiceFailedNotification;
use Domain\Documents\Models\Document;
use Domain\Invoicing\Actions\CreateMoloniInvoiceReceiptAction;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Job to generate an external invoice via Moloni API.
 *
 * This job is dispatched after a payment is confirmed via webhook.
 * It handles the async communication with Moloni invoice generation
 * service to avoid blocking the webhook response.
 *
 * Configuration:
 * - Retries up to 3 times with exponential backoff
 * - Times out after 60 seconds per attempt
 * - Failed jobs are logged for manual review
 *
 * @see \App\Listeners\DispatchInvoiceGenerationListener
 */
class GenerateExternalInvoiceJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Keep duplicate invoice generation jobs for the same document out of the queue.
     */
    public int $uniqueFor = 300;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 120];

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 60;

    public function __construct(
        public Document $document,
        public ?PaymentTransaction $transaction,
        public array $webhookData
    ) {}

    public function uniqueId(): string
    {
        return $this->document->id;
    }

    /**
     * Execute the job.
     */
    public function handle(CreateMoloniInvoiceReceiptAction $createInvoiceAction): void
    {
        Log::info('GenerateExternalInvoiceJob: Starting invoice generation', [
            'document_id' => $this->document->id,
            'document_number' => $this->document->number_extended,
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Check if external invoice was already generated (idempotency)
            if ($this->invoiceAlreadyGenerated()) {
                Log::info('GenerateExternalInvoiceJob: Invoice already generated, skipping', [
                    'document_id' => $this->document->id,
                ]);

                return;
            }

            // Create invoice via Moloni
            $moloniInvoice = $createInvoiceAction($this->document, $this->transaction);

            if ($moloniInvoice) {
                Log::info('GenerateExternalInvoiceJob: Moloni invoice created', [
                    'document_id' => $this->document->id,
                    'moloni_document_id' => $moloniInvoice->moloni_document_id,
                    'moloni_number' => $moloniInvoice->moloni_number,
                ]);
            } else {
                Log::info('GenerateExternalInvoiceJob: Invoice not created (Moloni disabled or not configured)', [
                    'document_id' => $this->document->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('GenerateExternalInvoiceJob: Failed to generate invoice', [
                'document_id' => $this->document->id,
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if an external invoice was already generated for this document.
     */
    private function invoiceAlreadyGenerated(): bool
    {
        return MoloniInvoice::existsForDocument($this->document->id);
    }

    /**
     * Handle a job failure.
     *
     * This method is called when all retry attempts have been exhausted.
     * It logs the failure and notifies administrators so they can manually
     * investigate and resolve the issue.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateExternalInvoiceJob: Job failed permanently', [
            'document_id' => $this->document->id,
            'transaction_id' => $this->transaction?->id,
            'error' => $exception->getMessage(),
            'max_attempts' => $this->tries,
        ]);

        $this->notifyAdministrators($exception);
    }

    /**
     * Notify administrators about the failed invoice generation.
     */
    private function notifyAdministrators(\Throwable $exception): void
    {
        try {
            $alertEmail = config('invoicing.providers.moloni.alert_email');

            if ($alertEmail) {
                // Send to configured email address
                Notification::route('mail', $alertEmail)
                    ->notify(new MoloniInvoiceFailedNotification(
                        $this->document,
                        $exception->getMessage(),
                        $this->tries
                    ));

                Log::info('GenerateExternalInvoiceJob: Failure notification sent', [
                    'document_id' => $this->document->id,
                    'email' => $alertEmail,
                ]);
            }

            // Also notify admin users with 'access settings' permission
            $admins = User::permission('access settings')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new MoloniInvoiceFailedNotification(
                    $this->document,
                    $exception->getMessage(),
                    $this->tries
                ));

                Log::info('GenerateExternalInvoiceJob: Admin users notified', [
                    'document_id' => $this->document->id,
                    'admin_count' => $admins->count(),
                ]);
            }
        } catch (\Exception $e) {
            // Don't let notification failure cause additional problems
            Log::warning('GenerateExternalInvoiceJob: Failed to send failure notification', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
