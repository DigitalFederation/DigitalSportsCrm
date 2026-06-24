<?php

namespace Domain\Memberships\Models;

use Carbon\Carbon;
use Database\Factories\MembershipFactory;
use Domain\Documents\Models\DocumentDetail;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\CanceledMembershipState;
use Domain\Memberships\States\ExpiredMembershipState;
use Domain\Memberships\States\MembershipState;
use Domain\Memberships\States\PendingMembershipState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mpociot\Versionable\VersionableTrait;

/**
 * @method static create(array $array)
 * @method static find(int $id)
 * @method static select(string ...$columns)
 * @method static orderBy(string $string)
 * @method static findOrFail(int $id)
 *
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $current_term_ends_at
 * @property int|null $federation_id
 * @property int|null $parent_id
 * @property string|null $name
 * @property MembershipState $state
 * @property class-string<MembershipState>|null $status_class
 */
class Membership extends Model
{
    use HasFactory, VersionableTrait;

    protected static function newFactory(): MembershipFactory
    {
        return MembershipFactory::new();
    }

    protected $table = 'membership';

    public string $morphName = 'Membership';

    protected $fillable = [
        'parent_id',
        'federation_id',
        'name',
        'status_class',
        'current_term_starts_at',
        'current_term_ends_at',
    ];
    protected $casts = [
        'current_term_starts_at' => 'datetime',
        'current_term_ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        // static::addGlobalScope(new BelongsToFederationScope());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'parent_id');
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class, 'membership_membership_plan');
    }

    public function parentPlans()
    {
        return $this->belongsToMany(MembershipPlan::class, 'membership_membership_plan', 'membership_id', 'membership_plan_id', 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Membership::class, 'parent_id');
    }

    public function licensesAttributed(): HasMany
    {
        return $this->hasMany(LicenseAttributed::class);
    }

    public function getStateAttribute(): MembershipState
    {
        return new $this->status_class($this);
    }
    public function documentDetails(): HasMany
    {
        return $this->hasMany(DocumentDetail::class, 'owner_id')
            ->where('owner_type', self::class);
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

    public function scopeMembershipStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'active':
                $status = ActiveMembershipState::class;
                break;
            case 'pending':
                $status = PendingMembershipState::class;
                break;
            case 'canceled':
                $status = CanceledMembershipState::class;
                break;
            case 'expired':
                $status = ExpiredMembershipState::class;
                break;
        }

        return $query->where('status_class', '=', $status);
    }

    public function scopeExpirationBefore(Builder $query, string $date): Builder
    {
        return $query->whereDate('current_term_ends_at', '>=', Carbon::parse($date));
    }

    public function scopeExpirationAfter(Builder $query, string $date): Builder
    {
        return $query->whereDate('current_term_ends_at', '<=', Carbon::parse($date));
    }

    public function scopeFilterFederation(Builder $query, string $federation_id): Builder
    {
        return $query->where('federation_id', $federation_id);
    }

    public function scopeFilterFederationCode(Builder $query, string $federation_code): Builder
    {
        return $query->whereHas('federation', function ($q) use ($federation_code) {
            return $q->where('member_code', $federation_code);
        });
    }

    public function scopeFilterSport(Builder $query, int $sport_id): Builder
    {
        return $query->whereHas('plans', function ($query) use ($sport_id) {
            $query->whereHas('licenses', function ($query) use ($sport_id) {
                $query->where(compact('sport_id'));
            });
        });
    }

    public function scopeFilterCommittee(Builder $query, int $committee_id): Builder
    {
        return $query->whereHas('plans', function ($query) use ($committee_id) {
            $query->where(compact('committee_id'));
        });
    }

    public function scopeFilterCountry(Builder $query, int $country_id): Builder
    {
        return $query->whereHas('federation', function ($q) use ($country_id) {
            return $q->where('country_id', $country_id);
        });
    }
}
