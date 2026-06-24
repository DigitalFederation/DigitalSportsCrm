<?php

namespace Domain\EvtEvents\Models;

use Domain\EvtEvents\Traits\FormatsEnrollmentAttributeValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffEnrollmentAttributes extends Model
{
    use FormatsEnrollmentAttributeValues;

    protected $table = 'evt_staff_attributes';

    protected $fillable = [
        'staff_enrollment_id',
        'attribute_id',
        'value',
    ];

    public function staffEnrollment(): BelongsTo
    {
        return $this->belongsTo(StaffEnrollment::class, 'staff_enrollment_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
