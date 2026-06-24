<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EnrollmentCredit extends Model
{
    protected $table = 'evt_enrollment_credits';

    protected $fillable = [
        'event_id',
        'enrollable_id',
        'enrollable_type',
        'role_type',
        'available_slots',
        'monetary_value',
        'expires_at',
    ];

    protected $casts = [
        'available_slots' => 'integer',
        'monetary_value' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the event that owns the credit.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the enrollable model (Federation or Entity).
     */
    public function enrollable(): MorphTo
    {
        return $this->morphTo();
    }
}
