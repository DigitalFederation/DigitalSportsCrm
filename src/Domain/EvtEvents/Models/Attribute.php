<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\EvtAttributeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The Attribute model represents a customizable field that can be associated with various entities
 * within the system, particularly within the event enrollment process.
 *
 * This model is used to dynamically add additional data fields to entities such as events, enrollments,
 * and potentially users or other entities requiring customizable fields. Each attribute can store
 * various types of data, governed by its 'attribute_type', and may include validation rules to ensure
 * data integrity.
 *
 * Relationships:
 * - BelongsToMany Discipline: An attribute can belong to multiple disciplines, which allows for
 *   categorizing attributes under different sets of rules or contexts.
 * - BelongsToMany Event: Attributes can be associated with multiple events, typically through the
 *   context of AttributeGroups for organizational-level events.
 * - HasMany AttributeRules: Each attribute can have multiple validation or behavior rules associated
 *   with it, defining how the attribute should be validated or behave in different contexts.
 *
 * Usage:
 * This model should be used whenever there is a need to extend the data collection requirements for
 * an entity within the system without altering the database schema. Attributes are particularly useful
 * in scenarios where different events require different data fields, or when the data requirements for
 * entities are subject to frequent changes.
 */
/**
 * @method static create(mixed $data)
 *
 * @property object{custom_value: mixed} $pivot
 */
class Attribute extends Model
{
    use HasFactory;

    protected $table = 'evt_attributes';

    protected $fillable = [
        'name',
        'attribute_type',
        'attribute_data',
        'default_value',
        'validation_rules',
        'custom_class',
        'fillable_type',
        'fillable_global',
        'enrollment_type',
        'required',
    ];

    protected $casts = [
        'attribute_data' => 'array',
        'required' => 'boolean',
    ];

    protected static function newFactory(): EvtAttributeFactory
    {
        return EvtAttributeFactory::new();
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'evt_discipline_attribute_association', 'attribute_id', 'discipline_id')
            ->withPivot('custom_value');
    }

    public function event(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'evt_event_attribute');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(AttributeRules::class);
    }

    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(AttributeGroup::class, 'evt_attribute_groups_attribute');
    }

    public function athleteEnrollments(): HasMany
    {
        return $this->hasMany(AthleteEnrollmentAttributes::class, 'attribute_id');
    }

    public function individualEnrollments(): HasMany
    {
        return $this->hasMany(IndividualEnrollmentAttribute::class, 'attribute_id');
    }
    public function officialsEnrollments(): HasMany
    {
        return $this->hasMany(OfficialsEnrollmentAttributes::class, 'attribute_id');
    }

    public function coachEnrollments(): HasMany
    {
        return $this->hasMany(CoachEnrollmentAttributes::class, 'attribute_id');
    }
}
