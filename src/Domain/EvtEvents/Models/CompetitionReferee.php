<?php

namespace Domain\EvtEvents\Models;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionReferee extends Model
{
    use HasFactory;

    protected $table = 'evt_competition_referees';

    protected $fillable = [
        'competition_id',
        'individual_id',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }
}
