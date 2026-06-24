<?php

namespace Domain\Payments\Models;

use Domain\Documents\Models\Document;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'gateway',
        'request_id',
        'status',
        'ip_address',
        'headers',
        'payload',
        'response',
        'transaction_id',
        'document_id',
        'error_message',
        'response_code',
        'processing_time_ms',
    ];

    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'response' => 'array',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'green',
            'already_processed' => 'blue',
            'failed' => 'red',
            'error' => 'red',
            'invalid_signature' => 'orange',
            'acknowledged' => 'yellow',
            default => 'gray',
        };
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['success', 'already_processed']);
    }

    public function scopeCreatedAfter($query, string $date)
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    public function scopeCreatedBefore($query, string $date)
    {
        return $query->whereDate('created_at', '<=', $date);
    }
}
