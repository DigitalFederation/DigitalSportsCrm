<?php

namespace Domain\Imports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection<int, ImportError> $errorMessages
 */
class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'filename',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'success_count',
        'error_count',
        'warning_count',
        'field_mapping',
        'options',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'field_mapping' => 'array',
        'options' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the errors for the import.
     */
    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class);
    }

    /**
     * Get only error severity messages.
     */
    public function errorMessages(): HasMany
    {
        return $this->errors()->where('severity', 'error');
    }

    /**
     * Get only warning severity messages.
     */
    public function warnings(): HasMany
    {
        return $this->errors()->where('severity', 'warning');
    }

    /**
     * Check if import is complete.
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    /**
     * Check if import can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return min(100, round(($this->processed_rows / $this->total_rows) * 100));
    }

    /**
     * Get estimated time remaining in seconds.
     */
    public function getEstimatedTimeRemainingAttribute(): ?int
    {
        if (! $this->started_at || $this->processed_rows === 0) {
            return null;
        }

        $elapsedSeconds = $this->started_at->diffInSeconds(now());
        $rowsPerSecond = $this->processed_rows / max(1, $elapsedSeconds);

        if ($rowsPerSecond === 0) {
            return null;
        }

        $remainingRows = $this->total_rows - $this->processed_rows;

        return (int) ceil($remainingRows / $rowsPerSecond);
    }

    /**
     * Get processing rate (rows per second).
     */
    public function getProcessingRateAttribute(): float
    {
        if (! $this->started_at || $this->processed_rows === 0) {
            return 0;
        }

        $elapsedSeconds = $this->started_at->diffInSeconds($this->completed_at ?? now());

        return round($this->processed_rows / max(1, $elapsedSeconds), 2);
    }

    /**
     * Mark import as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark import as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark import as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark import as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
    }

    /**
     * Increment processed rows and update counts.
     */
    public function incrementProgress(int $successCount = 0, int $errorCount = 0, int $warningCount = 0): void
    {
        $this->increment('processed_rows', $successCount + $errorCount);

        if ($successCount > 0) {
            $this->increment('success_count', $successCount);
        }

        if ($errorCount > 0) {
            $this->increment('error_count', $errorCount);
        }

        if ($warningCount > 0) {
            $this->increment('warning_count', $warningCount);
        }
    }
}
