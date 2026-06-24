<?php

use App\Models\User;
use Domain\Imports\Models\Import;
use Domain\Imports\Models\ImportError;

beforeEach(function () {
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

test('import has correct relationships', function () {
    expect($this->import->user)->toBeInstanceOf(User::class)
        ->and($this->import->user->id)->toBe($this->user->id);
});

test('import can track progress', function () {
    $this->import->update([
        'total_rows' => 1000,
        'processed_rows' => 250,
    ]);

    expect($this->import->progress_percentage)->toBe(25);
});

test('import can be marked as started', function () {
    $this->import->markAsStarted();

    expect($this->import->status)->toBe('processing')
        ->and($this->import->started_at)->not->toBeNull();
});

test('import can be marked as completed', function () {
    $this->import->markAsCompleted();

    expect($this->import->status)->toBe('completed')
        ->and($this->import->completed_at)->not->toBeNull();
});

test('import can be marked as failed', function () {
    $this->import->markAsFailed('Test error message');

    expect($this->import->status)->toBe('failed')
        ->and($this->import->error_message)->toBe('Test error message')
        ->and($this->import->completed_at)->not->toBeNull();
});

test('import can be cancelled', function () {
    $this->import->markAsStarted();

    expect($this->import->canBeCancelled())->toBeTrue();

    $this->import->markAsCancelled();

    expect($this->import->status)->toBe('cancelled')
        ->and($this->import->canBeCancelled())->toBeFalse();
});

test('import calculates processing rate', function () {
    $this->import->update([
        'processed_rows' => 100,
        'started_at' => now()->subSeconds(10),
    ]);

    // processing_rate returns a float (rows per second)
    expect($this->import->processing_rate)->toBeGreaterThanOrEqual(9.0)
        ->and($this->import->processing_rate)->toBeLessThanOrEqual(11.0);
});

test('import estimates time remaining', function () {
    $this->import->update([
        'total_rows' => 1000,
        'processed_rows' => 100,
        'started_at' => now()->subSeconds(10),
    ]);

    // 100 rows in 10 seconds = 10 rows/second
    // 900 rows remaining / 10 rows per second = 90 seconds
    // estimated_time_remaining returns an int (seconds)
    expect($this->import->estimated_time_remaining)->toBeGreaterThanOrEqual(80)
        ->and($this->import->estimated_time_remaining)->toBeLessThanOrEqual(100);
});

test('import can increment progress', function () {
    $this->import->update(['total_rows' => 100]);

    // incrementProgress(successCount, errorCount, warningCount)
    // processed_rows is incremented by successCount + errorCount
    $this->import->incrementProgress(10, 2, 1);

    expect($this->import->processed_rows)->toBe(12) // 10 success + 2 errors
        ->and($this->import->success_count)->toBe(10)
        ->and($this->import->error_count)->toBe(2)
        ->and($this->import->warning_count)->toBe(1);
});

test('import has error messages relationship', function () {
    ImportError::create([
        'import_id' => $this->import->id,
        'row_number' => 1,
        'error_message' => 'Test error',
        'severity' => 'error',
        'row_data' => ['test' => 'data'],
    ]);

    ImportError::create([
        'import_id' => $this->import->id,
        'row_number' => 2,
        'error_message' => 'Test warning',
        'severity' => 'warning',
        'row_data' => ['test' => 'data'],
    ]);

    expect($this->import->errorMessages)->toHaveCount(1)
        ->and($this->import->warnings)->toHaveCount(1)
        ->and($this->import->errors)->toHaveCount(2);
});
