<?php

namespace Domain\Entities\Models;

use Domain\Entities\States\EntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property class-string<EntityFederationState>|null $status_class
 * @property EntityFederationState $state
 * @property int $federation_id
 */
class EntityFederation extends Model
{
    use HasFactory;

    protected $table = 'entity_federation';

    protected $fillable = ['entity_id', 'federation_id', 'active', 'national_federation_number', 'rejected_at', 'status_class'];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function getStateAttribute(): EntityFederationState
    {
        // Ensure status_class is a valid class name
        if (! class_exists($this->status_class)) {
            $defaultStateClass = PendingEntityFederationState::class;

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

}
