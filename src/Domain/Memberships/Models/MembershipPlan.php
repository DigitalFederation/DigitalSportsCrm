<?php

namespace Domain\Memberships\Models;

use App\Models\Committee;
use Database\Factories\MembershipPlanFactory;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static create(array $array)
 * @method static find(int $id)
 * @method static select(string ...$column)
 *
 * @property int|null $interval
 * @property string|null $interval_unit
 * @property string|null $name
 * @property string|float|int|null $price
 * @property string|float|int|null $tax_percentage
 * @property string|float|int|null $tax_value
 */
class MembershipPlan extends Model
{
    use HasFactory;

    protected static function newFactory(): MembershipPlanFactory
    {
        return MembershipPlanFactory::new();
    }

    protected $table = 'membership_plan';

    protected $fillable = [
        'committee_id',
        'name',
        'friendly_name',
        'price',
        'interval',
        'interval_unit',
        'tax_value',
        'tax_percentage',
    ];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'membership_membership_plan');
    }

    public function membershipsChilds(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'membership_membership_plan', 'membership_plan_id', 'membership_id', 'id', 'parent_id');
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'membership_plan_licenses', 'membership_plan_id', 'license_id');
    }

    public function scopeFilterCommittee(Builder $query, int $committee_id): Builder
    {
        return $query->where('committee_id', $committee_id);
    }

    public function scopeFilterName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    public function scopeFilterPrice(Builder $query, string $price): Builder
    {
        return $query->where('price', 'like', '%'.$price.'%');
    }

    public function scopeFilterSport(Builder $query, int $sport_id): Builder
    {
        return $query->whereHas('licenses', function (Builder $query) use ($sport_id) {
            $query->where(compact('sport_id'));
        });
    }
}
