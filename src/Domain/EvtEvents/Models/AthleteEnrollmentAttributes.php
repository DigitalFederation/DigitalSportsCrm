<?php

namespace Domain\EvtEvents\Models;

use Domain\EvtEvents\Traits\FormatsEnrollmentAttributeValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AthleteEnrollmentAttributes extends Model
{
    use FormatsEnrollmentAttributeValues;
    use SoftDeletes;

    protected $table = 'evt_athletes_enrollment_attributes';

    protected $fillable = [
        'athlete_enrollment_id',
        'attribute_id',
        'value',
    ];

    public function athleteEnrollment()
    {
        return $this->belongsTo(AthleteEnrollment::class, 'athlete_enrollment_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
