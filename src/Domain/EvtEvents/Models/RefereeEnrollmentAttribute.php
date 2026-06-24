<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefereeEnrollmentAttribute extends Model
{
    protected $table = 'evt_referees_enrollment_attributes';

    protected $fillable = [
        'referee_enrollment_id',
        'attribute_id',
        'value',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function refereeEnrollment(): BelongsTo
    {
        return $this->belongsTo(RefereeEnrollment::class);
    }
}
