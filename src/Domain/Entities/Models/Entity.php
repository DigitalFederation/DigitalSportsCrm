<?php

namespace Domain\Entities\Models;

use App\Models\Committee;
use App\Models\Country;
use App\Models\User;
use Database\Factories\EntityFactory;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Documents\Models\Document;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mpociot\Versionable\VersionableTrait;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Support\Traits\HasQrCode;

/**
 * @method static paginate(int $int)
 * @method static create(array $param)
 * @method static select(string ...$columns)
 * @method static find(int $id)
 * @method static whereDoesntHave(string $foreignRelation)
 * @method static findOrFail(int $id)
 *
 * @property int|null $country_id
 * @property int|null $member_number
 * @property string|null $address
 * @property string|null $member_code
 * @property string|null $email
 * @property string|null $location
 * @property string $name
 */
class Entity extends Model implements HasMedia
{
    use HasFactory;
    use HasQrCode;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    // Verisonable Trait
    use VersionableTrait;

    protected static function newFactory(): EntityFactory
    {
        return EntityFactory::new();
    }

    protected $table = 'entity';

    protected $fillable = [
        'country_id',
        'district_id',
        'vat_number',
        'name',
        'legal_name',
        'legal_responsible_person',
        'phone',
        'website',
        'address',
        'postal_code',
        'location',
        'lat',
        'lng',
        'email',
        'member_code',
        'member_number',
        'qrcode_path',
        'facebook_url',
        'x_url',
        'instagram_url',
        'linkedin_url',
        'public_description',
        'has_international_portal',
        'visible_in_club_registry',
        'visible_in_diving_service_provider_registry',
        'visible_in_map',
    ];

    protected $casts = [
        'has_international_portal' => 'boolean',
        'visible_in_club_registry' => 'boolean',
        'visible_in_diving_service_provider_registry' => 'boolean',
        'visible_in_map' => 'boolean',
    ];

    // LogActivity
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'legal_name', 'email']);
        // Chain fluent methods for configuration options
    }

    public function qrCodeSourceField(): string
    {
        return 'member_code';
    }

    public function qrCodePathField(): string
    {
        return 'qrcode_path';
    }

    public function qrCodeDirectory(): string
    {
        return 'entities';
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(Committee::class, 'entity_committee');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'entity_zone')
            ->withTimestamps();
    }

    public function individuals(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'individual_entity')->withPivot('status_class');
    }

    public function federations(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'entity_federation')
            ->withPivot(['status_class', 'national_federation_number'])
            ->withTimestamps();
    }

    public function getFederationStateAttribute($federation)
    {
        $statusClass = $federation->pivot->status_class;
        if (! class_exists($statusClass)) {
            $statusClass = PendingEntityFederationState::class;
        }

        return new $statusClass($federation->pivot);
    }

    public function getFederationStateNameAttribute($federation)
    {
        $statusClass = $federation->pivot->status_class;

        // Map state classes to their readable names
        return match ($statusClass) {
            \Domain\Entities\States\ActiveEntityFederationState::class => 'Active',
            \Domain\Entities\States\PendingEntityFederationState::class => 'Pending',
            \Domain\Entities\States\RejectedEntityFederationState::class => 'Rejected',
            default => 'Pending'
        };
    }

    public function getFederationStateColorAttribute($federation)
    {
        $statusClass = $federation->pivot->status_class;

        // Map common state colors without instantiating the state class
        return match ($statusClass) {
            \Domain\Entities\States\ActiveEntityFederationState::class => 'active-state',
            \Domain\Entities\States\PendingEntityFederationState::class => 'pending-state',
            \Domain\Entities\States\RejectedEntityFederationState::class => 'canceled-state',
            default => 'pending-state'
        };
    }

    public function entityFederations(): HasMany
    {
        return $this->hasMany(EntityFederation::class);
    }

    public function scopeLocalFederationIfExists(): ?Federation
    {
        return $this->federations()->whereNotNull('parent_id')->first() ?? $this->federations()->first();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'entity_user');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'entity_user');
    }

    public function entityProfessionals(): HasMany
    {
        return $this->hasMany(EntityProfessionalRole::class);
    }

    public function entityAthletes(): HasMany
    {
        return $this->hasMany(EntityAthlete::class);
    }

    public function licenses(): MorphMany
    {
        return $this->morphMany(LicenseAttributed::class, 'model')
            ->whereNull('license_attributed.deleted_at');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class);
    }

    public function individualEntities(): HasMany
    {
        return $this->HasMany(IndividualEntity::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'owner');
    }

    public function officialDocuments()
    {
        return $this->morphMany(\Domain\OfficialDocuments\Models\OfficialDocument::class, 'owner');
    }

    public function memberSubscriptions(): MorphMany
    {
        return $this->morphMany(MemberSubscription::class, 'member');
    }

    public function affiliations(): MorphMany
    {
        return $this->morphMany(Affiliation::class, 'member');
    }

    /**
     * Check if the entity has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->memberSubscriptions()
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                \Domain\Memberships\States\ActiveMemberSubscriptionState::class,
                \Domain\Memberships\States\PendingPaymentMemberSubscriptionState::class,
            ])
            ->exists();
    }

    /**
     * Check if the entity has an active affiliation.
     */
    public function hasActiveAffiliation(): bool
    {
        // Check direct affiliations using the relationship
        return $this->affiliations()
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                \Domain\Memberships\States\ActiveAffiliationState::class,
            ])
            ->exists();
    }

    /**
     * Check if the entity has an active affiliation with a validation plan for the main federation.
     */
    public function hasActiveValidationPlanAffiliation(): bool
    {
        return $this->affiliations()
            ->tap(fn ($q) => self::applyValidationPlanCondition($q))
            ->exists();
    }

    public function scopeFilterAffiliationStatus(Builder $query, string $status): Builder
    {
        $validationPlanCondition = fn ($q) => self::applyValidationPlanCondition($q);

        return match ($status) {
            'active' => $query->whereHas('affiliations', $validationPlanCondition),
            'inactive' => $query->whereDoesntHave('affiliations', $validationPlanCondition),
            'pending' => $query->whereHas('entityFederations', function ($q) {
                $q->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
                    ->where('status_class', \Domain\Entities\States\PendingEntityFederationState::class);
            }),
            'rejected' => $query->whereHas('entityFederations', function ($q) {
                $q->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
                    ->where('status_class', \Domain\Entities\States\RejectedEntityFederationState::class);
            }),
            default => $query,
        };
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Affiliation>  $query
     */
    public static function applyValidationPlanCondition($query): void
    {
        $query->where('end_date', '>=', now())
            ->where('status_class', ActiveAffiliationState::class)
            ->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
            ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($ap) {
                $ap->where('is_validation_plan', true);
            });
    }

    /**
     * Check if the entity has any active entity license (non-individual license).
     * An entity license is a license where is_individual is false/null on the license type.
     */
    public function hasActiveEntityLicense(): bool
    {
        // Remove the ExcludeInternationalScope from LicenseAttributed to include both national and international
        return LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'entity')
            ->where('model_id', $this->id)
            ->whereNull('deleted_at')
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function ($query) {
                // Also remove the scope from License query
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->whereHas('type', function ($typeQuery) {
                        $typeQuery->where('is_individual', '!=', true)
                            ->orWhereNull('is_individual');
                    });
            })
            ->exists();
    }

    /**
     * Check if the entity has an active entity license for a specific sport.
     * Licenses without sport restriction (sport_id = null) are always allowed.
     */
    public function hasActiveEntityLicenseForSport(?int $sportId): bool
    {
        // Licenses without sport restriction are always allowed
        if (! $sportId) {
            return true;
        }

        // Remove the ExcludeInternationalScope from LicenseAttributed to include both national and international
        return LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'entity')
            ->where('model_id', $this->id)
            ->whereNull('deleted_at')
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function ($query) use ($sportId) {
                // Also remove the scope from License query
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->where('sport_id', $sportId)
                    ->whereHas('type', function ($typeQuery) {
                        $typeQuery->where('is_individual', '!=', true)
                            ->orWhereNull('is_individual');
                    });
            })
            ->exists();
    }

    // Get the display name of the entity
    public function getDisplayName(): string
    {
        return $this->name;
    }

    /**
     * Entities that have Licenses with is_school_licenses
     * Mean that they are Schools in the e-learning package.
     */
    public function isSchool(): bool
    {
        return $this->licenses()
            ->whereHas('license', function ($query) {
                $query->where('is_school_license', true);
            })
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->exists();
    }

    /**
     * Scope a query to only include results from date
     */
    public function scopeFilterName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function scopeFilterLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    public function scopeFilterEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', 'like', '%' . $email . '%');
    }

    public function scopeFilterFederation(Builder $query, int $federation_id): Builder
    {
        return $query->whereHas('federations', function (Builder $q) use ($federation_id) {
            return $q->where('federation.id', $federation_id);
        });
    }

    public function scopeFilterCountry(Builder $query, int $country_id): Builder
    {
        return $query->where(compact('country_id'));
    }

    public function scopeFilterMemberCode(Builder $query, string $code): Builder
    {
        return $query->where('member_code', 'like', '%' . $code . '%');
    }

    public function scopeFilterZone(Builder $query, int $geo_zone_id): Builder
    {
        return $query->whereHas('country', function (Builder $q) use ($geo_zone_id) {
            return $q->where(compact('geo_zone_id'));
        });
    }

    public function scopeFilterRegion(Builder $query, int $sub_region_id): Builder
    {
        return $query->whereHas('country', function (Builder $q) use ($sub_region_id) {
            return $q->where(compact('sub_region_id'));
        });
    }

    public function scopeCommittee(Builder $query, string $code): Builder
    {
        return $query->whereHas('committees', function (Builder $q) use ($code) {
            return $q->where(compact('code'));
        });
    }

    public function scopeFromFederation(Builder $query, $federationId): Builder
    {
        return $query->whereHas('federations', function ($query) use ($federationId) {
            $query->where('federation_id', $federationId);
        });
    }

    public function scopeFilterByZone(Builder $query, int $zoneId): Builder
    {
        return $query->whereHas('zones', function ($q) use ($zoneId) {
            $q->where('zones.id', $zoneId);
        });
    }

    public function scopeFilterByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    public function scopeFilterDistrict(Builder $query, int $district_id): Builder
    {
        return $query->where(compact('district_id'));
    }

    public function divingTechnicalDirectors(): HasMany
    {
        return $this->hasMany(\Domain\Diving\Models\DivingEntityTechnicalDirector::class);
    }
    public function getNationalFederationNumber(): ?string
    {
        if (! $this->relationLoaded('entityFederations')) {
            $this->load('entityFederations');
        }

        return $this->entityFederations->first()->national_federation_number ?? null;
    }

    // Add this method for Spatie Media Library
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile();

        $this->addMediaCollection('entity-background')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('documents');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        // Thumbnail for lists and small displays
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->optimize()
            ->nonQueued()
            ->format('jpg');

        // Medium size for profile display
        $this->addMediaConversion('medium')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->optimize()
            ->nonQueued()
            ->format('jpg');

        // Optimized original - max 800px, compressed
        $this->addMediaConversion('optimized')
            ->width(800)
            ->height(800)
            ->optimize()
            ->nonQueued()
            ->format('jpg');
    }
}
