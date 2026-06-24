<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\OrganizerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Organizer extends Model
{
    use HasFactory;
    protected $table = 'evt_organizers';

    protected $fillable = [
        'organizable_id',
        'organizable_type',
        'event_id',
    ];

    protected static function newFactory(): OrganizerFactory
    {
        return OrganizerFactory::new();
    }
    public function organizable(): MorphTo
    {
        return $this->morphTo();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
