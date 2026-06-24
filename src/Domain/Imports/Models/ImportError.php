<?php

namespace Domain\Imports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $row_number
 * @property string|null $error_message
 * @property string|null $severity
 * @property array<string, mixed>|null $row_data
 */
class ImportError extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'row_number',
        'row_data',
        'error_message',
        'severity',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    /**
     * Get the import that owns the error.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Check if this is an error (not a warning).
     */
    public function isError(): bool
    {
        return $this->severity === 'error';
    }

    /**
     * Check if this is a warning (not an error).
     */
    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }

    /**
     * Create multiple errors at once.
     */
    public static function createMany(Import $import, array $errors): void
    {
        $data = [];

        foreach ($errors as $error) {
            $data[] = [
                'import_id' => $import->id,
                'row_number' => $error['row_number'],
                'row_data' => json_encode($error['row_data'] ?? []),
                'error_message' => $error['message'],
                'severity' => $error['severity'] ?? 'error',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($data)) {
            static::insert($data);
        }
    }
}
