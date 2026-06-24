<?php

namespace Domain\EvtEvents\Models;

use Domain\EvtEvents\Traits\FormatsEnrollmentAttributeValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualEnrollmentAttribute extends Model
{
    use FormatsEnrollmentAttributeValues;

    protected $table = 'evt_individual_enrollment_attributes';

    protected $fillable = [
        'individual_enrollment_id',
        'attribute_id',
        'value',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function individualEnrollment(): BelongsTo
    {
        return $this->belongsTo(IndividualEnrollment::class);
    }
}
