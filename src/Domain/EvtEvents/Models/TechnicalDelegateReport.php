<?php

namespace Domain\EvtEvents\Models;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $event_id
 */
class TechnicalDelegateReport extends Model
{
    protected $table = 'evt_technical_delegate_reports';

    protected $fillable = [
        'event_id',
        'submitted_by',
        'participants_withdrawals',
        'incidents_occurrences',
        'officials_performance',
        'facilities_evaluation',
        'safety_first_aid',
        'anti_doping_control',
        'sports_protests',
        'observations_recommendations',
        'is_submitted',
        'submitted_at',
    ];

    protected $casts = [
        'is_submitted' => 'boolean',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'submitted_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(EventReportDocument::class, 'documentable');
    }

    public function submit(): void
    {
        $this->update([
            'is_submitted' => true,
            'submitted_at' => now(),
        ]);
    }

    public function isEditable(): bool
    {
        return ! $this->is_submitted;
    }
}
