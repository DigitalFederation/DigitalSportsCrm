<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\CompetitionTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionType extends Model
{
    use HasFactory;

    protected $table = 'evt_competition_types';

    protected $fillable = [
        'competition_id',
        'competition_type',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return CompetitionTypeFactory::new();
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
