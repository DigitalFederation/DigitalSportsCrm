<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;

class EventAttributes extends Model
{
    protected $table = 'evt_event_attributes';

    protected $fillable = [
        'event_id',
        'attribute_id',
        'value',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
