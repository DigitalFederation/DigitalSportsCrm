<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\SportAgeGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportAgeGroup extends Model
{
    use HasFactory;

    protected $table = 'evt_sport_age_groups';

    // Cast birthday_start to date
    protected $casts = [
        'birthday_start' => 'date',
        'birthday_end' => 'date',
    ];

    protected $fillable = [
        'sport_id',
        'title',
        'birthday_start',
        'birthday_end',
    ];

    protected static function newFactory(): SportAgeGroupFactory
    {
        return SportAgeGroupFactory::new();
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }
}
