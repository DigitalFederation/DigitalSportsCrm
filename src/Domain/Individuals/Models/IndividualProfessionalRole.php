<?php

namespace Domain\Individuals\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualProfessionalRole extends Model
{
    protected $table = 'individual_professional_role';

    protected $fillable = ['professional_role_id', 'individual_id'];

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function professionalRole(): BelongsTo
    {
        return $this->belongsTo(ProfessionalRole::class);
    }
}
