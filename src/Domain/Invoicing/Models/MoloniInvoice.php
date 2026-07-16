<?php

namespace Domain\Invoicing\Models;

use Domain\Documents\Models\Document;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoloniInvoice extends Model
{
    use HasUuids;

    protected $table = 'moloni_invoices';

    protected $fillable = [
        'document_id',
        'moloni_document_id',
        'moloni_document_set_id',
        'moloni_number',
        'moloni_status',
        'moloni_total',
        'currency',
        'moloni_response',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'moloni_response' => 'array',
            'moloni_total' => 'decimal:2',
            'synced_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public static function existsForDocument(string $documentId): bool
    {
        return static::where('document_id', $documentId)->exists();
    }

    public static function findByDocument(string $documentId): ?self
    {
        return static::where('document_id', $documentId)->first();
    }

    public static function findByMoloniId(int $moloniDocumentId): ?self
    {
        return static::where('moloni_document_id', $moloniDocumentId)->first();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->moloni_status) {
            'closed' => 'green',
            'draft' => 'yellow',
            'canceled' => 'red',
            default => 'gray',
        };
    }
}
