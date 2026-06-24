<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\PricingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Pricing
 *
 * The Pricing model represents the pricing structure for events within the international project.
 * It is designed to accommodate a versatile range of pricing strategies, including
 * flat fees for federations or entities, individual or team-based fees for athletes,
 * and dynamic pricing based on specific time windows. This model is integral to
 * the event management system, particularly in calculating the total cost of
 * enrollments for different types of events (organizational or competition).
 *
 * Attributes:
 *
 * @property int $id Unique identifier for the Pricing record.
 * @property int $event_id Identifier of the Event associated with this pricing.
 * @property int|null $discipline_id Identifier of the Discipline, if the pricing is discipline-specific (nullable).
 * @property string $price_type Type of price ('flat_fee', 'individual', 'team') indicating the pricing model.
 * @property string $target_group Target group for the fee ('federation', 'entity', 'individual').
 * @property \Illuminate\Support\Carbon $start_date Start date of the pricing window.
 * @property \Illuminate\Support\Carbon $end_date End date of the pricing window.
 * @property float $price The actual price for this record.
 * @property bool $is_active Indicates if the pricing is currently active or inactive.
 *
 * Relationships:
 * @property Event $event The event associated with this pricing.
 * @property Discipline|null $discipline The discipline associated with this pricing, if applicable.
 *
 * Scopes:
 *
 * @method static \Illuminate\Database\Eloquent\Builder active() Scope for filtering active pricing records.
 * @method static \Illuminate\Database\Eloquent\Builder forTargetGroup(string $targetGroup) Scope for filtering records by target group.
 *
 * This model uses SoftDeletes to allow for data recovery and historical analysis.
 *
 * Usage:
 * - In organizational events, use this model to define a flat enrollment fee for the federation or entity.
 * - In competitions, link this model to specific disciplines to set varying fees per discipline.
 * - Utilize dynamic pricing by creating multiple records with different time windows.
 * - Activate or deactivate pricing as required to manage the availability of specific pricing options.
 */
class Pricing extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'evt_pricing';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'discipline_id',
        'price_type',
        'target_group',
        'start_date',
        'end_date',
        'price',
        'is_active',
        'pricing_option',
        'description',
        'enrollment_role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'float',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): PricingFactory
    {
        return PricingFactory::new();
    }

    /**
     * Get the event associated with the pricing.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the discipline associated with the pricing.
     */
    public function discipline()
    {
        return $this->belongsTo(Discipline::class)->withDefault();
    }

    /**
     * Scope a query to only include active prices.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include prices for a specific target group.
     */
    public function scopeForTargetGroup($query, $targetGroup)
    {
        return $query->where('target_group', $targetGroup);
    }

    public function isReferencedInEnrollments(): bool
    {
        return $this->enrollments()->exists();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'pricing_id');
    }
}
