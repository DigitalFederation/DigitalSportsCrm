<?php

namespace App\Jobs;

use Domain\Imports\Actions\ProcessImportFileAction;
use Domain\Imports\Models\Import;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIndividualImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The import ID.
     *
     * @var int
     */
    protected $importId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $importId)
    {
        $this->importId = $importId;
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(ProcessImportFileAction $processFileAction): void
    {
        $import = Import::find($this->importId);

        if (! $import || $import->isComplete()) {
            return;
        }

        try {
            Log::info('Starting import job', ['import_id' => $this->importId]);

            $import->markAsStarted();

            // Process the import file in chunks
            $processFileAction->execute($import);

            // Note: The import will be marked as completed by the chunk jobs
            // when all chunks are processed

        } catch (Exception $e) {
            Log::error('Import job failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $import->markAsFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $import = Import::find($this->importId);

        if ($import) {
            $import->markAsFailed('Job failed: ' . $exception->getMessage());

            // Log the failure
            Log::error('Import job failed', [
                'import_id' => $this->importId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['import', 'import:' . $this->importId];
    }
}
