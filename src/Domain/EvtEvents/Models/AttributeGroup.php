<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * The AttributeGroup model is used to organize Attributes into logical groups.
 * This organization aids in managing and applying sets of Attributes to different entities,
 * such as Events, especially for events categorized under 'organization' type.
 *
 * This model supports the dynamic application of grouped attributes, making it easier to manage
 * and apply standard sets of data fields across various parts of the system, particularly in
 * event management and enrollment processes.
 *
 * Relationships:
 * - BelongsToMany Attribute: An attribute group can contain multiple attributes, allowing for
 *   the grouping of related attributes under a unified theme or context.
 * - BelongsToMany Event: Attribute groups can be associated with multiple events, enabling the
 *   application of a standardized set of attributes to events, particularly useful for
 *   organizational events where standardized data collection is necessary.
 * - BelongsToMany Sport: Attribute groups can also be associated with different sports, allowing
 *   for sport-specific data collection requirements.
 *
 * Usage:
 * Use the AttributeGroup model to define collections of attributes that are frequently used together
 * or that share a common context. This is especially useful in scenarios where events or other entities
 * require standardized sets of information. By associating an entity with an attribute group, all
 * attributes contained within the group are implicitly associated with the entity, streamlining
 * the data collection and organization process.
 */
class AttributeGroup extends Model
{
    use HasFactory;
    protected $table = 'evt_attribute_groups';

    protected $fillable = ['name'];

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_attribute_groups_attribute');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'evt_event_attribute_groups', 'attribute_group_id', 'event_id');
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'evt_sport_attribute_group');
    }
}
