<?php

namespace Domain\EvtEvents\Models;

use Domain\EvtEvents\Traits\FormatsEnrollmentAttributeValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialsEnrollmentAttributes extends Model
{
    use FormatsEnrollmentAttributeValues;

    protected $table = 'evt_officials_attributes';

    protected $fillable = [
        'officials_enrollment_id',
        'attribute_id',
        'value',
    ];

    public function officialsEnrollment(): BelongsTo
    {
        return $this->belongsTo(TeamOfficialEnrollment::class, 'officials_enrollment_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
