<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;

class EventPin extends Model
{
    protected $table = 'evt_event_pins';

    protected $fillable = ['pin', 'usage_count', 'last_used_at'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}
