<?php

namespace Domain\Insurance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InsuranceDocument extends Model
{
    protected $fillable = ['insurance_plan_id', 'documentable_id', 'documentable_type', 'issue_date', 'expiry_date', 'status'];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

}
