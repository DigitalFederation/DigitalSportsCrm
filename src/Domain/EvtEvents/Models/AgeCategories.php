<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;

class AgeCategories extends Model
{
    protected $table = 'evt_age_categories';

    protected $fillable = [
        'min_age',
        'max_age',
        'comments',
        'discipline_id',
    ];

    public function discipline()
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }
}
