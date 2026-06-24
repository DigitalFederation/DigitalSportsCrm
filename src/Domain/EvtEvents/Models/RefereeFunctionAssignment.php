<?php

namespace Domain\EvtEvents\Models;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefereeFunctionAssignment extends Model
{
    protected $table = 'evt_referee_function_assignments';

    protected $fillable = [
        'event_id',
        'referee_enrollment_id',
        'is_present',
        'referee_function_id',
        'function_text',
        'assigned_by',
        'notes',
        'competition_days',
        'number_of_games',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'competition_days' => 'integer',
        'number_of_games' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function refereeEnrollment(): BelongsTo
    {
        return $this->belongsTo(RefereeEnrollment::class, 'referee_enrollment_id');
    }

    public function refereeFunction(): BelongsTo
    {
        return $this->belongsTo(RefereeFunction::class, 'referee_function_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'assigned_by');
    }

    /**
     * Get the function name (from RefereeFunction or custom text)
     */
    public function getFunctionNameAttribute(): string
    {
        if ($this->referee_function_id && $this->refereeFunction) {
            return $this->refereeFunction->function_name;
        }

        return $this->function_text ?? '';
    }

    /**
     * Check if this is a custom function (text-based)
     */
    public function isCustomFunction(): bool
    {
        return empty($this->referee_function_id) && ! empty($this->function_text);
    }
}
