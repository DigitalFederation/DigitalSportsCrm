<?php

namespace Domain\Federations\Models;

use Database\Factories\FederationProfessionalRoleFactory;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FederationProfessionalRole extends Model
{
    use HasFactory;

    protected $table = 'federation_professional_role';

    protected $fillable = [
        'federation_id',
        'individual_id',
        'professional_role_id',
        'federation_name',
        'individual_name',
        'role_name',
        'status_class',
    ];

    protected static function newFactory(): FederationProfessionalRoleFactory
    {
        return FederationProfessionalRoleFactory::new();
    }
    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function professionalRole(): BelongsTo
    {
        return $this->belongsTo(ProfessionalRole::class);
    }

    public function scopeRole(Builder $query, string ...$role): void
    {
        $query->whereHas('professionalRole', function (Builder $query) use ($role) {
            $query->whereIn('role', $role);
        });
    }

}
