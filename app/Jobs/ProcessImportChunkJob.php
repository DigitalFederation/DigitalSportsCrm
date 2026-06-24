<?php

namespace App\Jobs;

use Domain\Imports\Models\Import;
use Domain\Imports\Models\ImportError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImportChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes per chunk

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    protected $importId;

    protected $chunkData;

    protected $chunkIndex;

    /**
     * Create a new job instance.
     */
    public function __construct(int $importId, array $chunkData, int $chunkIndex)
    {
        $this->importId = $importId;
        $this->chunkData = $chunkData;
        $this->chunkIndex = $chunkIndex;
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(
        \Domain\Imports\Actions\ProcessImportChunkAction $processChunkAction
    ): void {
        $processChunkAction->execute($this->importId, $this->chunkData, $this->chunkIndex);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Import chunk job failed', [
            'import_id' => $this->importId,
            'chunk_index' => $this->chunkIndex,
            'error' => $exception->getMessage(),
        ]);

        $import = Import::find($this->importId);
        if ($import) {
            ImportError::create([
                'import_id' => $import->id,
                'row_number' => $this->chunkIndex * 1000,
                'error_message' => 'Chunk processing failed: '.$exception->getMessage(),
                'severity' => 'error',
                'row_data' => ['chunk_index' => $this->chunkIndex],
            ]);

            // Mark import as failed if too many chunk failures
            if ($import->errorMessages()->where('error_message', 'like', 'Chunk processing failed%')->count() > 5) {
                $import->markAsFailed('Too many chunk processing failures');
            }
        }
    }
}
