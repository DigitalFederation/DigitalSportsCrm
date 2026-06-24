<?php

namespace Domain\Individuals\Models;

use Domain\Federations\Models\Federation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\IndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Domain\Individuals\States\RejectedIndividualFederationState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property class-string<IndividualFederationState>|null $status_class
 * @property IndividualFederationState $state
 * @property int $federation_id
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class IndividualFederation extends Model
{
    use HasFactory;

    protected $table = 'individual_federation';

    protected $fillable = [
        'federation_id',
        'individual_id',
        'status_class',
        'active',
        'rejected_at',
    ];

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function getStateAttribute(): IndividualFederationState
    {
        // Ensure status_class is a valid class name
        if (! class_exists($this->status_class)) {
            // Handle invalid class name - either throw an exception or use a default class
            // For example, using a default 'Pending' state
            $defaultStateClass = PendingIndividualFederationState::class;

            return new $defaultStateClass($this);
        }

        return new $this->status_class($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive();
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function scopeFilterStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'active':
                $status = ActiveIndividualFederationState::class;
                break;
            case 'pending':
                $status = PendingIndividualFederationState::class;
                break;
            case 'rejected':
                $status = RejectedIndividualFederationState::class;
                break;
            default:
                return $query;
        }

        return $query->where('status_class', $status);
    }
}
