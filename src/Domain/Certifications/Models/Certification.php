<?php

namespace Domain\Certifications\Models;

use App\Models\Committee;
use App\Models\Sport;
use Database\Factories\CertificationFactory;
use Domain\EvtEvents\Models\Competition;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

/**
 * @method static paginate(int $int)
 * @method static select(string ...$columns)
 * @method static create(array $param)
 * @method static find(int $id)
 * @method static insert(array $certifications)
 *
 * @property int|null $committee_id
 * @property string|null $certification_view
 * @property bool $allow_entity_group_request
 * @property bool|null $is_international
 * @property int|null $license_id
 * @property int|null $professional_role_id
 * @property string|null $acronym
 * @property string|null $moloni_reference
 * @property string|null $name
 * @property string|float|int|null $digital_plus_card_price
 * @property string|float|int|null $tax_percentage
 * @property string|float|int|null $unit_value
 * @property string|float|int|null $unit_value_entity
 * @property string|float|int|null $unit_value_federation
 * @property string|float|int|null $unit_value_individual
 * @property string|null $requester_model
 *
 * @mixin \Domain\Certifications\Models\Certification;
 */
class Certification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'certification';

    protected $fillable = [
        'committee_id',
        'professional_role_id',
        'name',
        'acronym',
        'license_id',
        'certification_category',
        'offset_initial',
        'offset_current',
        'minimum_age',
        'confined_water_sessions',
        'open_water_sessions',
        'theoretical_sessions',
        'is_available',
        // Pricing fields (legacy)
        'unit_value',
        'unit_value_federation',
        'unit_value_entity',
        'tax_value',
        'tax_percentage',
        'moloni_reference',
        // New pricing fields
        'digital_price',
        'digital_plus_card_price',
        // Purchase configuration
        'requester_model',
        'allow_entity_group_request',
        'requires_admin_validation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_available' => 'boolean',
        'allow_entity_group_request' => 'boolean',
        'requires_admin_validation' => 'boolean',
        'unit_value' => 'decimal:2',
        'unit_value_federation' => 'decimal:2',
        'unit_value_entity' => 'decimal:2',
        'tax_value' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'digital_price' => 'decimal:2',
        'digital_plus_card_price' => 'decimal:2',
    ];

    /**
     * Scope a query to only include non-international certifications.
     * Uses committee.is_international instead of certification.is_international.
     */
    public function scopeNotInternational(Builder $query): Builder
    {
        return $query->whereHas('committee', function (Builder $q) {
            $q->where('is_international', false);
        });
    }

    protected static function newFactory(): CertificationFactory
    {
        return CertificationFactory::new();
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'certification_parents', 'certification_id', 'parent_id');
    }

    public function childs(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'certification_parents', 'parent_id', 'certification_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function professionalRole(): BelongsTo
    {
        return $this->belongsTo(ProfessionalRole::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function certificationsAttributed(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class);
    }

    public function refereeCompetitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'evt_competition_referee_certifications', 'certification_id', 'competition_id');
    }

    /**
     * Get the price for a specific requester type
     */
    public function getPriceForRequester($requesterType = 'entity'): float
    {
        if ($requesterType === 'entity' && $this->unit_value_entity !== null) {
            return (float) $this->unit_value_entity;
        }

        if ($requesterType === 'federation' && $this->unit_value_federation !== null) {
            return (float) $this->unit_value_federation;
        }

        return (float) ($this->unit_value ?? 0);
    }

    /**
     * Check if this certification can be requested by a specific model type
     */
    public function canBeRequestedBy($modelType): bool
    {
        if ($this->requester_model === 'all') {
            return true;
        }

        return strtolower($this->requester_model) === strtolower($modelType);
    }

    /**
     * Check if certification is free (uses new pricing model)
     */
    public function isFree(): bool
    {
        return $this->getDigitalPrice() === 0.0 && $this->getDigitalPlusCardPrice() === 0.0;
    }

    /**
     * Get the digital certification price
     */
    public function getDigitalPrice(): float
    {
        return (float) ($this->digital_price ?? 0);
    }

    /**
     * Get the digital + physical card price
     */
    public function getDigitalPlusCardPrice(): float
    {
        return (float) ($this->digital_plus_card_price ?? 0);
    }

    /**
     * Get price for a specific option
     */
    public function getPriceForOption(string $option): float
    {
        return match ($option) {
            'digital_plus_card' => $this->getDigitalPlusCardPrice(),
            default => $this->getDigitalPrice(),
        };
    }

    /**
     * Check if certification has a physical card option available
     */
    public function hasCardOption(): bool
    {
        return $this->digital_plus_card_price !== null && $this->digital_plus_card_price > 0;
    }

    /**
     * Check if this is an international certification.
     * Delegates to the committee's is_international flag.
     */
    public function isInternationalCertification(): bool
    {
        return $this->committee?->isInternational() ?? false;
    }

    /**
     * Check if this certification's committee is an international committee.
     * International committees: DIVING, SCIENTIFIC.
     */
    public function isValidInternationalCommittee(): bool
    {
        if (! $this->committee) {
            return false;
        }

        return $this->committee->isInternational();
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'certification_sport');
    }

    /**
     * Scope a query to only include results from date
     */
    public function scopeFilterName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    /**
     * Scope a query to be used on the filters
     * scopeFilterCommittee
     */
    public function scopeFilterCommittee(Builder $query, string ...$codes): Builder
    {
        return $query->whereHas('committee', function (Builder $q) use ($codes) {
            return $q->whereIn('code', $codes);
        });
    }

    public function scopeFilterCommitteeId(Builder $query, int $committee_id): Builder
    {
        return $query->where(compact('committee_id'));
    }

    /**
     * Scope a query to only include results from date
     */
    public function scopeFilterProfessionalRole(Builder $query, int $professional_role_id): Builder
    {
        return $query->where(compact('professional_role_id'));
    }

    /**
     * Scope a query to only include results from Professional Roles
     */
    public function scopeFilterLicense(Builder $query, int $license_id): Builder
    {
        return $query->where(compact('license_id'));
    }

    public function scopeFilterSport(Builder $query, int $sport_id): Builder
    {
        return $query->where(function (Builder $q) use ($sport_id) {
            $q->whereHas('license', fn (Builder $lq) => $lq->whereHas('sports', fn (Builder $sq) => $sq->where('sports.id', $sport_id))->orWhere('sport_id', $sport_id))
                ->orWhereHas('sports', fn (Builder $sq) => $sq->where('sports.id', $sport_id));
        });
    }

    public function scopeFilterAvailable(Builder $query, string $is_available): Builder
    {
        return $query->where('is_available', $is_available === '1');
    }

    /**
     * Scope to filter certifications available for purchase
     */
    public function scopeAvailableForPurchase(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to filter certifications by requester model
     */
    public function scopeForRequesterModel(Builder $query, string $model): Builder
    {
        return $query->where(function ($q) use ($model) {
            $q->where('requester_model', 'all')
                ->orWhere('requester_model', $model);
        });
    }

    /**
     * Scope to filter free certifications
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('unit_value')->orWhere('unit_value', 0);
        })->where(function ($q) {
            $q->whereNull('unit_value_entity')->orWhere('unit_value_entity', 0);
        })->where(function ($q) {
            $q->whereNull('unit_value_federation')->orWhere('unit_value_federation', 0);
        });
    }

    /**
     * Scope to filter paid certifications
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('unit_value', '>', 0)
                ->orWhere('unit_value_entity', '>', 0)
                ->orWhere('unit_value_federation', '>', 0);
        });
    }

    /**
     * Scope to filter certifications based on entity role
     */
    public function scopeForEntityRole(Builder $query, $entity): Builder
    {
        // If entity doesn't have international operator role, exclude international certifications
        if (! $entity->hasRole('entity-international')) {
            return $query->whereHas('committee', function (Builder $q) {
                $q->where('is_international', false);
            });
        }

        return $query;
    }

    /**
     * Roles that can be assigned based on this certification.
     * Many-to-many relationship through certification_roles pivot table.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'certification_roles')
            ->withPivot('committee_id')
            ->withTimestamps();
    }
}
