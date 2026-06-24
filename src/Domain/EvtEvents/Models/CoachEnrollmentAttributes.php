<?php

namespace Domain\EvtEvents\Models;

use Domain\EvtEvents\Traits\FormatsEnrollmentAttributeValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachEnrollmentAttributes extends Model
{
    use FormatsEnrollmentAttributeValues;

    public $timestamps = false;

    protected $table = 'evt_coaches_attributes';

    protected $fillable = [
        'coach_enrollment_id',
        'attribute_id',
        'value',
    ];

    public function coachEnrollment(): BelongsTo
    {
        return $this->belongsTo(CoachEnrollment::class, 'coach_enrollment_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
