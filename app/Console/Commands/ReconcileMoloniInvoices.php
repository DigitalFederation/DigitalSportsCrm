<?php

namespace App\Console\Commands;

use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Invoicing\Actions\CreateMoloniInvoiceReceiptAction;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Services\MoloniSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Automated reconciliation command for Moloni invoices.
 *
 * This command finds paid documents that should have Moloni invoices
 * but don't, and attempts to create them. It's designed to run on a
 * schedule to catch any documents that fell through the cracks due to:
 * - API failures that exhausted retries
 * - System outages during payment processing
 * - Manual payments where invoice creation was skipped
 *
 * The command respects rate limits and processes documents in batches
 * to avoid overwhelming the Moloni API.
 */
class ReconcileMoloniInvoices extends Command
{
    protected $signature = 'moloni:reconcile
                            {--hours= : Only process documents paid within the last N hours (default from config)}
                            {--limit= : Maximum number of documents to process per run (default from config)}
                            {--dry-run : Show what would be processed without actually creating invoices}';

    protected $description = 'Reconcile paid documents that are missing Moloni invoices';

    public function handle(
        MoloniSettingsService $settingsService,
        CreateMoloniInvoiceReceiptAction $createInvoiceAction
    ): int {
        if (! $settingsService->isEnabled()) {
            $this->warn('Moloni integration is disabled. Skipping reconciliation.');

            return self::SUCCESS;
        }

        if (config('app.currency', 'EUR') !== 'EUR') {
            $this->warn('Moloni invoicing only supports EUR; installation currency is ' . config('app.currency') . '. Skipping reconciliation.');

            return self::SUCCESS;
        }

        if (! $settingsService->isConfigured()) {
            $this->warn('Moloni is not fully configured. Skipping reconciliation.');

            return self::SUCCESS;
        }

        $hours = (int) ($this->option('hours') ?? config('invoicing.providers.moloni.reconciliation.hours_lookback', 72));
        $limit = (int) ($this->option('limit') ?? config('invoicing.providers.moloni.reconciliation.batch_size', 50));
        $dryRun = $this->option('dry-run');

        $this->info("Reconciling Moloni invoices for documents paid in the last {$hours} hours...");

        $documents = $this->getDocumentsMissingInvoices($hours, $limit);

        if ($documents->isEmpty()) {
            $this->info('No documents missing Moloni invoices. All caught up!');

            return self::SUCCESS;
        }

        $this->info("Found {$documents->count()} document(s) missing invoices.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Number', 'Owner', 'Total', 'Paid At'],
                $documents->map(fn ($doc) => [
                    $doc->id,
                    $doc->number_extended,
                    $doc->owner?->name ?? 'N/A',
                    money($doc->total_value, $doc->currency),
                    $doc->updated_at->format('Y-m-d H:i'),
                ])
            );
            $this->warn('Dry run mode - no invoices were created.');

            return self::SUCCESS;
        }

        $successCount = 0;
        $failedCount = 0;
        $startTime = microtime(true);

        $progressBar = $this->output->createProgressBar($documents->count());
        $progressBar->start();

        foreach ($documents as $document) {
            try {
                $invoice = $createInvoiceAction($document);

                if ($invoice) {
                    $successCount++;
                    Log::info('ReconcileMoloniInvoices: Invoice created', [
                        'document_id' => $document->id,
                        'moloni_number' => $invoice->moloni_number,
                    ]);
                } else {
                    $failedCount++;
                    Log::warning('ReconcileMoloniInvoices: Invoice not created (returned null)', [
                        'document_id' => $document->id,
                    ]);
                }
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('ReconcileMoloniInvoices: Failed to create invoice', [
                    'document_id' => $document->id,
                    'error' => $e->getMessage(),
                ]);
                $this->newLine();
                $this->error("Failed for document {$document->number_extended}: {$e->getMessage()}");
            }

            $progressBar->advance();

            // Small delay between API calls to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }

        $progressBar->finish();
        $this->newLine(2);

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        // Log the reconciliation run
        MoloniSyncLog::logSuccess('reconciliation', [
            'documents_processed' => $documents->count(),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'hours_lookback' => $hours,
        ], $durationMs);

        $this->info('Reconciliation complete:');
        $this->line("  - Processed: {$documents->count()}");
        $this->line("  - Success: {$successCount}");
        $this->line("  - Failed: {$failedCount}");

        if ($failedCount > 0) {
            $this->warn('Some invoices failed to create. Check logs for details.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function getDocumentsMissingInvoices(int $hours, int $limit)
    {
        return Document::with('owner')
            ->where('status_class', PaidDocumentState::class)
            ->whereDoesntHave('moloniInvoice')
            ->whereHas('type', fn ($q) => $q->where('code', 'ORD'))
            ->whereNotNull('owner_id') // Must have an owner for invoice
            ->where('updated_at', '>=', now()->subHours($hours))
            ->orderBy('updated_at', 'asc') // Process oldest first
            ->limit($limit)
            ->get();
    }
}
