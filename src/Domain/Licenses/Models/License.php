<?php

namespace Domain\Licenses\Models;

use App\Models\Committee;
use App\Models\Sport;
use App\Traits\InteractsWithMediaExtend;
use Database\Factories\LicenseFactory;
use Domain\Certifications\Models\Certification;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Actions\NormalizeRequesterTypeAction;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Role;

/**
 * @method static select(string ...$columns)
 * @method static find(int $id)
 *
 * @property bool $allow_entity_group_request
 * @property int|null $committee_id
 * @property int|null $interval
 * @property int|null $license_id
 * @property int|null $professional_role_id
 * @property int|null $sport_id
 * @property bool|null $is_international
 * @property string|null $interval_unit
 * @property string|null $license_code
 * @property string|null $moloni_reference
 * @property string|null $name
 * @property object{status: string|null} $pivot
 * @property array<int, string>|string|null $requester_model
 * @property bool $requires_official_documents
 * @property string|float|int|null $tax_percentage
 * @property string|float|int|null $tax_value
 * @property string|float|int|null $unit_value
 * @property string|float|int|null $unit_value_entity
 * @property string|float|int|null $unit_value_federation
 * @property string|float|int|null $unit_value_individual
 */
class License extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia {
        InteractsWithMedia::getLastMedia insteadof InteractsWithMediaExtend;
        InteractsWithMedia::getLastMediaUrl insteadof InteractsWithMediaExtend;
    }
    use InteractsWithMediaExtend;
    use SoftDeletes;

    protected $table = 'license';

    protected $fillable = [
        'committee_id',
        'type_id',
        'professional_role_id',
        'name',
        'is_school_license',
        'active',
        'interval',
        'interval_unit',
        'validity_type',
        'sport_id',
        'unit_value',
        'unit_value_individual',
        'unit_value_entity',
        'unit_value_federation',
        'tax_value',
        'tax_percentage',
        'moloni_reference',
        'license_code',
        'requester_model',
        'allow_entity_group_request',
        'requires_official_documents',
        'required_document_types',
        'required_athlete_documents',
        'required_coach_documents',
        'required_official_documents',
        'required_diving_professional_documents',
    ];

    public $timestamps = false;

    protected $casts = [
        'is_school_license' => 'boolean',
        'allow_entity_group_request' => 'boolean',
        'requires_official_documents' => 'boolean',
        'required_document_types' => 'array',
        'required_athlete_documents' => 'array',
        'required_coach_documents' => 'array',
        'required_official_documents' => 'array',
        'required_diving_professional_documents' => 'array',
        'requester_model' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'allow_entity_group_request' => true,
    ];

    protected static function newFactory(): LicenseFactory
    {
        return LicenseFactory::new();
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new ExcludeInternationalScope);

        // Normalize requester_model when saving
        static::saving(function ($license) {
            $action = new NormalizeRequesterTypeAction;

            // Get the value being set (could be from attributes or direct property)
            $value = $license->requester_model ?? $license->getAttributes()['requester_model'] ?? null;

            if ($value !== null) {
                $normalized = $action($value);
                // Set it directly in attributes to ensure it's saved correctly
                $license->attributes['requester_model'] = $normalized !== null ? json_encode($normalized) : null;
            }
        });
    }

    /**
     * The Collection component will show a preview thumbnail for items in the collection it is showing.
     * To generate that thumbnail, you must add a conversion like this one to your model.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('resized')
            ->performOnCollections('profile')
            ->fit(Fit::Crop, 150, 150)
            ->nonQueued();

        // Add thumb conversion for logos
        $this->addMediaConversion('thumb')
            ->performOnCollections('logo')
            ->fit(Fit::Contain, 100, 100) // Use contain to avoid cropping
            ->nonQueued();
    }

    // Removed federation() relationship - use federations() many-to-many relationship instead

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Check if this is an international license.
     * Delegates to the committee's is_international flag.
     */
    public function isInternationalLicense(): bool
    {
        return $this->committee?->isInternational() ?? false;
    }

    public function professionalRole(): BelongsTo
    {
        return $this->belongsTo(ProfessionalRole::class, 'professional_role_id');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'license_sport')
            ->withTimestamps();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LicenseType::class);
    }

    public function licensesAttributed(): HasMany
    {
        return $this->hasMany(LicenseAttributed::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function requiredCertifications(): BelongsToMany
    {
        return $this->belongsToMany(
            Certification::class,
            'license_required_certifications',
            'license_id',
            'certification_id'
        )->withPivot('requester_type')->withTimestamps();
    }

    public function requiredCertificationsForRequester(?string $requesterType = null): BelongsToMany
    {
        $query = $this->requiredCertifications();

        if ($requesterType) {
            $query->where(function ($q) use ($requesterType) {
                $q->where('license_required_certifications.requester_type', $requesterType)
                    ->orWhereNull('license_required_certifications.requester_type');
            });
        }

        return $query;
    }

    /**
     * Get required certification levels for a specific requester type.
     */
    public function getRequiredCertificationLevels(?string $requesterType = null): Collection
    {
        return DB::table('license_required_certifications')
            ->where('license_id', $this->id)
            ->when($requesterType, function ($query) use ($requesterType) {
                $query->where('requester_type', $requesterType);
            })
            ->whereNotNull('certification_level')
            ->pluck('certification_level');
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class, 'membership_plan_licenses', 'license_id', 'membership_plan_id');
    }

    public function scopeFilterName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function scopeFilterCommittee(Builder $query, string $committee_id): Builder
    {
        return $query->where(compact('committee_id'));
    }

    public function scopeFilterType(Builder $query, string $type_id): Builder
    {
        return $query->where(compact('type_id'));
    }

    public function scopeHasCommitteeCode(Builder $query, string $committee): Builder
    {
        return $query->whereHas('committee', function (Builder $q) use ($committee) {
            $q->where('code', $committee);
        });
    }

    public function scopeHasLicenseType(Builder $query, string $license_type_name): Builder
    {
        return $query->whereHas('type', function (Builder $q) use ($license_type_name) {
            $q->where('name', $license_type_name);
        });
    }

    public function scopeFilterSport(Builder $query, string $sport_id): Builder
    {
        return $query->where(function (Builder $q) use ($sport_id) {
            $q->whereHas('sports', fn (Builder $sq) => $sq->where('sports.id', $sport_id))
                ->orWhere('sport_id', $sport_id);
        });
    }

    public function scopeAllowsEntityGroupRequest(Builder $query): Builder
    {
        return $query->where('allow_entity_group_request', true);
    }

    // Removed scopeForFederation and scopeForFederations - use scopeForFederationEntities instead
    // These scopes were based on the old federation_id column which is being removed

    /**
     * Federations that can offer this license to their member entities.
     * Many-to-many relationship through federation_licenses pivot table.
     */
    public function federations(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'federation_licenses')
            ->withTimestamps();
    }

    /**
     * Scope to filter licenses by federations that can offer them.
     * Used to filter licenses available to entities based on their federation memberships.
     *
     * @param  Collection|array  $federationIds  Collection or array of federation IDs
     */
    public function scopeForFederationEntities(Builder $query, $federationIds): Builder
    {
        // Handle both Collection and array inputs
        if ($federationIds instanceof Collection) {
            $federationIds = $federationIds->toArray();
        }

        // If no federations provided, return empty result
        if (empty($federationIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('federations', function ($q) use ($federationIds) {
            $q->whereIn('federation_licenses.federation_id', $federationIds);
        });
    }

    /**
     * Roles that can be assigned based on this license.
     * Many-to-many relationship through license_roles pivot table.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'license_roles')
            ->withPivot('committee_id')
            ->withTimestamps();
    }

    /**
     * Check if a specific requester type can request this license
     */
    public function canBeRequestedBy(string $requesterType): bool
    {
        $action = new NormalizeRequesterTypeAction;

        return $action->isTypeAllowed($this->requester_model, $requesterType);
    }

    /**
     * Get formatted requester types for display
     */
    public function getFormattedRequesterTypes(): string
    {
        $action = new NormalizeRequesterTypeAction;
        $normalized = $action($this->requester_model);

        if ($normalized === null) {
            return 'All';
        }

        // Convert morph keys back to display names
        $displayNames = array_map(function ($type) {
            return ucfirst($type);
        }, $normalized);

        return implode(' + ', $displayNames);
    }

    /**
     * Scope to filter licenses by requester type
     */
    public function scopeForRequesterType(Builder $query, string $requesterType): Builder
    {
        $action = new NormalizeRequesterTypeAction;

        // Normalize the requester type to get the morph key
        $normalizedType = $action->normalizeType($requesterType);

        if ($normalizedType === null) {
            // If we can't normalize the type, return no results
            return $query->whereRaw('1 = 0');
        }

        $morphKey = $normalizedType->value;

        return $query->where(function ($q) use ($morphKey) {
            // Include licenses where requester_model is null (allows all)
            $q->whereNull('requester_model')
                // Or where the value is the legacy string "All" (allows all)
                ->orWhere('requester_model', 'All')
                // Or where the array is empty (allows all)
                ->orWhereJsonLength('requester_model', 0)
                // Or where the specific type is in the array
                ->orWhereJsonContains('requester_model', $morphKey);
        });
    }
}
