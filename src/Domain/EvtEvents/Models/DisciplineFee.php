<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplineFee extends Model
{
    protected $table = 'evt_discipline_fees';

    protected $fillable = [
        'discipline_id',
        'description',
        'amount',
        'effective_from',
        'effective_to',
    ];

    public function discipline()
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }
}
