<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\IndividualEnrollmentFactory;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $state
 * @property mixed $status_class
 * @property mixed $processedAttributes
 * @property int|null $document_id
 * @property \Domain\Documents\Models\Document|null $document
 */
class IndividualEnrollment extends Model
{
    use HasFactory;
    protected $table = 'evt_individuals_enrollment';

    protected $fillable = [
        'enrollment_id',
        'event_id',
        'federation_id',
        'entity_id',
        'individual_id',
        'status_class',
        'price_type',
        'price',
        'pricing_id',
    ];

    protected static function newFactory(): IndividualEnrollmentFactory
    {
        return IndividualEnrollmentFactory::new();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class, 'federation_id');
    }

    // Add entity relationship
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'individual_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(IndividualEnrollmentAttribute::class, 'individual_enrollment_id');
    }

    public function getStateAttribute()
    {
        if (! empty($this->status_class) && class_exists($this->status_class)) {
            return new $this->status_class($this);
        }

        // Optionally, return a default state or null if class does not exist
        return null;
    }

    public function stateName(): string
    {
        return $this->state ? $this->state->name() : '';
    }

    public function stateColor(): string
    {
        return $this->state ? $this->state->color() : 'pending-color';
    }
}
