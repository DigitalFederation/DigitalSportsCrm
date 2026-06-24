<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventGeographic extends Model
{
    use HasFactory;

    protected $table = 'evt_event_geographic';

    protected $fillable = ['event_id', 'geo_entity_id', 'geo_entity_type'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function geoEntity()
    {
        return $this->morphTo(null, 'geo_entity_type', 'geo_entity_id');
    }
}
