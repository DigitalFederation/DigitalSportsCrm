<?php

namespace Domain\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class MoloniSyncLog extends Model
{
    protected $table = 'moloni_sync_logs';

    protected $fillable = [
        'sync_type',
        'status',
        'data',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public static function logSuccess(string $syncType, ?array $data = null, ?int $durationMs = null): self
    {
        return static::create([
            'sync_type' => $syncType,
            'status' => 'success',
            'data' => $data,
            'duration_ms' => $durationMs,
        ]);
    }

    public static function logFailure(string $syncType, string $errorMessage, ?array $data = null, ?int $durationMs = null): self
    {
        return static::create([
            'sync_type' => $syncType,
            'status' => 'failed',
            'data' => $data,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);
    }

    public static function getLastSync(string $syncType): ?self
    {
        return static::where('sync_type', $syncType)
            ->latest()
            ->first();
    }

    public static function getLastSuccessfulSync(string $syncType): ?self
    {
        return static::where('sync_type', $syncType)
            ->where('status', 'success')
            ->latest()
            ->first();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'failed' => 'red',
            default => 'gray',
        };
    }

    public function getDurationFormatted(): string
    {
        if (! $this->duration_ms) {
            return '-';
        }

        if ($this->duration_ms < 1000) {
            return $this->duration_ms . 'ms';
        }

        return number_format($this->duration_ms / 1000, 2) . 's';
    }
}
