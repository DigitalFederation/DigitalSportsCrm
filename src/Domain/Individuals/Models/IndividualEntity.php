<?php

namespace Domain\Individuals\Models;

use Domain\Entities\Models\Entity;
use Domain\Individuals\States\IndividualEntityState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $status_class
 * @property mixed $state
 * @property int|null $entity_id
 */
class IndividualEntity extends Model
{
    use HasFactory;

    protected $table = 'individual_entity';

    protected $fillable = ['entity_id', 'individual_id', 'status_class'];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function getStateAttribute(): IndividualEntityState
    {
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
