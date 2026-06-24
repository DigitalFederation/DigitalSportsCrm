<?php

use App\Jobs\ProcessImportChunkJob;
use App\Jobs\ProcessIndividualImportJob;
use App\Models\User;
use Domain\Imports\Actions\ProcessImportChunkAction;
use Domain\Imports\Actions\ProcessImportFileAction;
use Domain\Imports\Models\Import;
use Domain\Imports\Models\ImportError;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Queue::fake();
    Storage::fake('local');

    $this->user = User::factory()->create();
    $this->import = Import::create([
        'user_id' => $this->user->id,
        'type' => 'individual',
        'filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'status' => 'pending',
        'field_mapping' => ['Name' => 'name', 'Email' => 'email'],
        'options' => ['duplicate_strategy' => 'skip'],
    ]);
});

test('ProcessIndividualImportJob dispatches chunk jobs', function () {
    // Create a test CSV file
    $csvContent = "Name,Email,Birthdate,Country\nJohn Doe,john@example.com,1990-01-01,Brazil";
    Storage::disk('local')->put('imports/test.csv', $csvContent);

    $job = new ProcessIndividualImportJob($this->import->id);

    // Mock the action - use callback to match the import by ID since job loads fresh instance
    $mockAction = Mockery::mock(ProcessImportFileAction::class);
    $importId = $this->import->id;
    $mockAction->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($import) => $import instanceof Import && $import->id === $importId));

    $job->handle($mockAction);

    expect($this->import->fresh()->status)->toBe('processing');
});

test('ProcessIndividualImportJob handles failures', function () {
    $job = new ProcessIndividualImportJob($this->import->id);

    $exception = new Exception('Test error');
    $job->failed($exception);

    expect($this->import->fresh()->status)->toBe('failed')
        ->and($this->import->fresh()->error_message)->toContain('Test error');
});

test('ProcessIndividualImportJob has correct configuration', function () {
    $job = new ProcessIndividualImportJob($this->import->id);

    expect($job->timeout)->toBe(3600)
        ->and($job->tries)->toBe(3)
        ->and($job->tags())->toBe(['import', 'import:' . $this->import->id]);
});

test('ProcessImportChunkJob processes chunk data', function () {
    $chunkData = [
        ['Name' => 'John Doe', 'Email' => 'john@example.com'],
        ['Name' => 'Jane Smith', 'Email' => 'jane@example.com'],
    ];

    $job = new ProcessImportChunkJob($this->import->id, $chunkData, 0);

    // Mock the action
    $mockAction = Mockery::mock(ProcessImportChunkAction::class);
    $mockAction->shouldReceive('execute')
        ->once()
        ->with($this->import->id, $chunkData, 0);

    $job->handle($mockAction);
});

test('ProcessImportChunkJob handles failures', function () {
    $job = new ProcessImportChunkJob($this->import->id, [], 0);

    $exception = new Exception('Chunk processing failed');
    $job->failed($exception);

    $error = ImportError::where('import_id', $this->import->id)->first();

    expect($error)->not->toBeNull()
        ->and($error->error_message)->toContain('Chunk processing failed');
});

test('ProcessImportChunkJob marks import as failed after too many failures', function () {
    // Create 5 existing chunk failure errors
    for ($i = 0; $i < 5; $i++) {
        ImportError::create([
            'import_id' => $this->import->id,
            'row_number' => $i * 1000,
            'error_message' => 'Chunk processing failed: Test',
            'severity' => 'error',
            'row_data' => ['chunk_index' => $i],
        ]);
    }

    $job = new ProcessImportChunkJob($this->import->id, [], 5);

    $exception = new Exception('Another chunk failed');
    $job->failed($exception);

    expect($this->import->fresh()->status)->toBe('failed');
});

test('jobs are queued on imports queue', function () {
    ProcessIndividualImportJob::dispatch($this->import->id);
    ProcessImportChunkJob::dispatch($this->import->id, [], 0);

    Queue::assertPushedOn('imports', ProcessIndividualImportJob::class);
    Queue::assertPushedOn('imports', ProcessImportChunkJob::class);
});
