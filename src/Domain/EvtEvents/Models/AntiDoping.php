<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AntiDoping extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'evt_antidoping';

    protected $fillable = [
        'event_id',
        'competition_id',
        'num_controls_planned',
        'date_updated',
        'number_of_controls',
        'responsible_name',
        'responsible_email',
        'responsible_phone',
        'expected_athletes',
        'created_at',
        'updated_at',
    ];

    protected static function booted()
    {
        static::saving(function ($antiDoping) {
            if ($antiDoping->isDirty('number_of_controls')) {
                $antiDoping->date_updated = Carbon::now();
            }
        });
    }

    /**
     * Get the competition that owns the anti-doping record.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
