<?php

namespace Domain\Individuals\Models;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Models\Country;
use App\Models\User;
use App\Scopes\IndividualsFromFederationScope;
use App\Traits\CreatedUpdatedBy;
use Database\Factories\IndividualFactory;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\CompetitionReferee;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationProfessionalRole;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Memberships\Models\MemberSubscription;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mpociot\Versionable\VersionableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\Traits\HasQrCode;

/**
 * @method static paginate(int $int)
 * @method static create(array $param)
 * @method static find(int $id)
 * @method static select(string ...$column)
 * @method static findOrFail(string $id)
 *
 * @property string|null $affiliate_number
 * @property \Illuminate\Support\Carbon|null $birthdate
 * @property string|null $member_code
 * @property int|null $country_id
 * @property int|null $member_number
 * @property string|null $address
 * @property string|null $doc_ref
 * @property string|null $doc_ref_type
 * @property string|null $doc_ref_validation_date
 * @property string|null $email
 * @property string|null $first_name_latin
 * @property string|null $gender
 * @property string|null $last_name_latin
 * @property string|null $location
 * @property string $name
 * @property string|null $native_name
 * @property string|null $phone
 * @property string|null $postal_code
 * @property string|null $qrcode_path
 * @property string $surname
 * @property string|null $user_id
 * @property string|null $vat_number
 * @property bool|null $visible_in_coach_registry
 */
class Individual extends Model implements HasMedia
{
    use CreatedUpdatedBy;
    use HasFactory;
    use HasQrCode;
    use HasUuids;
    use InteractsWithMedia;
    use SoftDeletes;

    /** Key Type */
    public $incrementing = false;
    protected $keyType = 'string'; // Recommended when using UUIDs

    // Verisonable Trait
    use VersionableTrait;

    /**
     * @var array
     */
    protected $dontVersionFields = ['last_login_at'];

    // this is a recommended way to declare event handlers
    public static function boot(): void
    {
        parent::boot();
    }

    protected $table = 'individual';

    protected $fillable = ['country_id', 'district_id', 'user_id', 'name', 'surname', 'first_name_latin', 'last_name_latin', 'native_name', 'birthdate', 'gender', 'email', 'address', 'location', 'postal_code', 'doc_ref_type', 'doc_ref', 'doc_ref_validation_date', 'vat_number', 'phone', 'created_by', 'updated_by', 'member_code', 'member_number', 'national_federation_number', 'qrcode_path', 'facebook_url', 'x_url', 'instagram_url', 'linkedin_url', 'has_international_portal', 'visible_in_coach_registry', 'visible_in_technical_official_registry', 'visible_in_diving_professional_registry'];

    protected $appends = ['full_name', 'full_name_latin'];

    protected $casts = [
        'birthdate' => 'date',
        'doc_ref_validation_date' => 'date',
        'has_international_portal' => 'boolean',
        'visible_in_coach_registry' => 'boolean',
        'visible_in_technical_official_registry' => 'boolean',
        'visible_in_diving_professional_registry' => 'boolean',
    ];

    protected $dates = [
        'birthdate',
        'doc_ref_validation_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Register media collections and configure secure storage for profile pictures
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->useDisk('secure-media')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
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

    protected static function booted(): void
    {
        // Filter Individuals By Federation
        static::addGlobalScope(new IndividualsFromFederationScope);
    }

    protected static function newFactory(): IndividualFactory
    {
        return IndividualFactory::new();
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
        return 'individuals';
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    /**
     * Get the full name using Latin characters.
     */
    public function getFullNameLatinAttribute(): string
    {
        // Ensure fallback if latin names are somehow null
        $firstName = $this->first_name_latin ?? Str::ascii($this->name);
        $lastName = $this->last_name_latin ?? Str::ascii($this->surname);

        return $firstName . ' ' . $lastName;
    }

    /**
     * Get the URL for the individual's avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        $media = $this->getFirstMedia('profile');

        if (! $media) {
            return asset('img/user_placeholder.png');
        }

        if ($media->disk === 'secure-media') {
            return route('secure-media.profile.thumb', [$this->id, $media->id]);
        }

        return $this->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png');
    }

    /**
     * Get the initial letter(s) for the avatar.
     */
    public function getAvatarInitial(): string
    {
        $name = trim($this->name);

        if (empty($name)) {
            return '?';
        }

        return strtoupper(substr($name, 0, 1));
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
        return $this->belongsToMany(Zone::class, 'individual_zone')
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificationsAttributed(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class);
    }

    public function certificationsSportAttributed(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class)
            ->whereHas('certification', function (Builder $query) {
                $query->whereHas('committee', function (Builder $query) {
                    $query->where('code', 'SPORT');
                });
            });
    }

    public function certificationsDivingAttributed(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class)
            ->whereHas('certification', function (Builder $query) {
                $query->whereHas('committee', function (Builder $query) {
                    $query->where('code', 'DIVING');
                });
            });
    }

    public function certificationsScientificAttributed(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class)
            ->whereHas('certification', function (Builder $query) {
                $query->whereHas('committee', function (Builder $query) {
                    $query->where('code', 'SCIENTIFIC');
                });
            });
    }

    public function instructorCertificationsAttributed(): HasMany
    {
        return $this->hasMany(CertificationAttributed::class, 'instructor_id');
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'certification_attributed');
    }

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'individual_entity');
    }

    public function individualEntities(): HasMany
    {
        return $this->HasMany(IndividualEntity::class);
    }

    public function entityAthletes(): HasMany
    {
        return $this->HasMany(EntityAthlete::class);
    }

    public function eventDisciplines()
    {
        return $this->hasMany(AthleteEnrollment::class, 'individual_id')
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
            ])
            ->with('discipline:id,name');
    }

    public function federations(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'individual_federation');
    }

    public function individualFederations(): HasMany
    {
        return $this->HasMany(IndividualFederation::class);
    }

    public function professionalRoleEntities(): HasMany
    {
        return $this->HasMany(EntityProfessionalRole::class);
    }

    public function federationProfessionalRoles(): HasMany
    {
        return $this->hasMany(FederationProfessionalRole::class, 'individual_id');
    }

    public function licenses(): MorphMany
    {
        return $this->morphMany(LicenseAttributed::class, 'model');
    }

    public function licensesSportAttributed(): MorphMany|Builder
    {
        return $this->morphMany(LicenseAttributed::class, 'model')
            ->whereHas('license', function (Builder $query) {
                $query->whereHas('committee', function (Builder $query) {
                    $query->where('code', 'SPORT');
                });
            });
    }

    public function licensesDivingAttributed(): Builder|MorphMany
    {
        return $this->morphMany(LicenseAttributed::class, 'model')
            ->whereHas('license', function (Builder $query) {
                $query->whereHas('committee', function (Builder $query) {
                    $query->where('code', 'DIVING');
                });
            });
    }

    public function licensesScientificAttributed(): Builder|MorphMany
    {
        return $this->morphMany(LicenseAttributed::class, 'model')
            ->whereHas('license', function (Builder $query) {
                $query->whereHas('committee', function (Builder $query) {
                    $query->where('code', 'SCIENTIFIC');
                });
            });
    }

    public function activator(): MorphMany
    {
        return $this->morphMany(CertificationAttributed::class, 'activator');
    }

    public function professionalRoles(): BelongsToMany
    {
        return $this->belongsToMany(ProfessionalRole::class, 'individual_professional_role', 'individual_id', 'professional_role_id');
    }

    public function officialDocuments(): HasMany
    {
        return $this->hasMany(OfficialDocument::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'owner', 'owner_type', 'owner_id');
    }

    public function divingProfessionalCertifications(): HasMany
    {
        return $this->hasMany(\Domain\Diving\Models\DivingProfessionalCertification::class);
    }

    public function divingTechnicalDirectorAssignments(): HasMany
    {
        return $this->hasMany(\Domain\Diving\Models\DivingEntityTechnicalDirector::class);
    }

    public function userCreated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function coachEnrollments(): HasMany
    {
        return $this->hasMany(CoachEnrollment::class);
    }

    // Method to get the display name of the individual
    public function getDisplayName(): string
    {
        return $this->full_name ?? $this->member_code;
    }

    public function isInstructor(): bool
    {
        return $this->licenses()
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function (Builder $query) {
                $query->whereHas('professionalRole', function (Builder $query) {
                    $query->where('role', 'like', 'INSTRUCTOR')
                        ->orWhere('role', 'like', 'LEADER');
                });
            })->exists();
    }

    public function isCoach(): bool
    {
        return $this->licenses()
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function (Builder $query) {
                $query->whereHas('professionalRole', function (Builder $query) {
                    $query->where('role', 'like', 'COACH');
                });
            })->exists();
    }

    public function athleteEnrollments(): HasMany
    {
        return $this->hasMany(AthleteEnrollment::class);
    }

    public function officialsEnrollments(): HasMany
    {
        return $this->hasMany(TeamOfficialEnrollment::class);
    }

    public function teamOfficialEnrollments(): HasMany
    {
        return $this->hasMany(TeamOfficialEnrollment::class);
    }

    public function individualEnrollments(): HasMany
    {
        return $this->hasMany(IndividualEnrollment::class);
    }

    public function refereeEnrollments(): HasMany
    {
        return $this->hasMany(RefereeEnrollment::class);
    }

    public function memberSubscriptions(): MorphMany
    {
        return $this->morphMany(MemberSubscription::class, 'member');
    }

    public function affiliations(): MorphMany
    {
        return $this->morphMany(\Domain\Memberships\Models\Affiliation::class, 'member');
    }

    /**
     * Check if the individual has an active subscription.
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
     * Check if the individual has an active affiliation through their subscriptions.
     */
    public function hasActiveAffiliation(): bool
    {
        return $this->memberSubscriptions()
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                \Domain\Memberships\States\ActiveMemberSubscriptionState::class,
            ])
            ->whereHas('affiliations', function ($query) {
                $query->where('end_date', '>=', now())
                    ->whereIn('status_class', [
                        \Domain\Memberships\States\ActiveAffiliationState::class,
                    ]);
            })
            ->exists();
    }

    /**
     * Check if the individual has an active affiliation with a validation plan for the main federation.
     */
    public function hasActiveValidationPlanAffiliation(): bool
    {
        return $this->affiliations()
            ->tap(fn ($q) => self::applyValidationPlanCondition($q))
            ->exists();
    }

    private static function applyValidationPlanCondition($query): void
    {
        $query->where('end_date', '>=', now())
            ->where('status_class', \Domain\Memberships\States\ActiveAffiliationState::class)
            ->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
            ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($ap) {
                $ap->where('is_validation_plan', true);
            });
    }

    public function scopeCommittee(Builder $query, string $committee): Builder
    {
        return $query->whereHas('certifications', function ($q) use ($committee) {
            return $q->whereHas('committee', function ($q) use ($committee) {
                return $q->where('code', $committee);
            });
        });
    }

    public function scopeIndividualsFromEntity(Builder $query): Builder
    {
        // Check if a user is authenticated before accessing user properties
        if (Auth::check() && ($user = Auth::user()) && $user->group()->first() && $user->group()->first()['code'] == 'ENTITY') {
            // Ensure the user has an entity associated
            if ($entity = $user->entities()->first()) {
                $entityId = $entity->id;

                return $query->whereHas('entities', function ($q) use ($entityId) {
                    $q->where('entity.id', $entityId)
                        ->where('status_class', ActiveIndividualEntityState::class);
                });
            }
        }

        // Return unmodified query if user is not authenticated or not an entity user
        return $query;
    }

    public function scopeIndividualFromFederation(Builder $query, $federationId): Builder
    {
        return $query->whereHas('federations', function ($q) use ($federationId) {
            $q->where('federation_id', $federationId)
                ->where('status_class', ActiveIndividualFederationState::class);
        });
    }

    /**
     * Scope a query to only include results from date
     */
    public function scopeFilterMemberCode(Builder $query, string $code): Builder
    {
        return $query->where('member_code', 'like', '%' . $code . '%');
    }

    /**
     * Scope a query to only include results from date
     */
    public function scopeFilterName(Builder $query, string $name): Builder
    {
        // Transliterate search term and query the latin field
        // Use double quotes for the regex pattern string
        $cleanedName = preg_replace("/[^\p{L}\p{N}'\s-]/u", '', $name);
        $latinName = Str::ascii($cleanedName);

        return $query->where('first_name_latin', 'like', '%' . $latinName . '%');
    }

    public function scopeFilterSurname(Builder $query, string $surname): Builder
    {
        // Transliterate search term and query the latin field
        // Use double quotes for the regex pattern string
        $cleanedSurname = preg_replace("/[^\p{L}\p{N}'\s-]/u", '', $surname);
        $latinSurname = Str::ascii($cleanedSurname);

        return $query->where('last_name_latin', 'like', '%' . $latinSurname . '%');
    }

    public function scopeFilterMemberNumber(Builder $query, string $member_number): Builder
    {
        return $query->where('member_number', 'like', '%' . $member_number . '%');
    }

    public function scopeFilterCountry(Builder $query, int $country_id): Builder
    {
        return $query->where(compact('country_id'));
    }

    public function scopeFilterFederation(Builder $query, int $federation_id): Builder
    {
        return $query->whereHas('federations', function (Builder $query) use ($federation_id) {
            return $query->where('federation_id', $federation_id);
        });
    }

    public function scopeFilterEntity(Builder $query, int $entity_id): Builder
    {
        return $query->whereHas('individualEntities', function (Builder $query) use ($entity_id) {
            $query->where('entity_id', $entity_id)
                ->where('status_class', ActiveIndividualEntityState::class);
        });
    }

    public function scopeFilterStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'pending':
                $status = PendingEntityProfessionalRoleState::class;
                break;
            case 'active':
                $status = ActiveEntityProfessionalRoleState::class;
                break;
        }

        return $query->whereHas('professionalRoleEntities', function (Builder $query) use ($status) {
            return $query->where('status_class', $status);
        });
    }

    public function scopeFilterZone(Builder $query, int $geo_zone_id): Builder
    {
        return $query->whereHas('country', function (Builder $query) use ($geo_zone_id) {
            return $query->where('geo_zone_id', $geo_zone_id);
        });
    }

    public function scopeFilterRegion(Builder $query, int $sub_region_id): Builder
    {
        return $query->whereHas('country', function (Builder $query) use ($sub_region_id) {
            return $query->where('sub_region_id', $sub_region_id);
        });
    }

    public function scopeFilterNationalAffiliationStatus(Builder $query, string $status): Builder
    {
        $condition = fn ($q) => self::applyValidationPlanCondition($q);

        return match ($status) {
            'active' => $query->whereHas('affiliations', $condition),
            'inactive' => $query->whereDoesntHave('affiliations', $condition),
            default => $query,
        };
    }

    public function scopeFilterHasDivingCertifications(Builder $query, bool $has_certifications = true): Builder
    {
        if ($has_certifications) {
            return $query->whereHas('divingProfessionalCertifications');
        } else {
            return $query->whereDoesntHave('divingProfessionalCertifications');
        }
    }

    public function scopeInstructors(Builder $query, bool $is_instructor): Builder
    {
        if ($is_instructor) {
            return $query->whereHas('licenses', function (Builder $query) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->whereHas('license', function (Builder $query) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->select('id', 'role')
                                    ->where('role', 'like', 'INSTRUCTOR');
                            });
                    });
            });
        } else {
            return $query->whereDoesntHave('licenses', function (Builder $query) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->whereHas('license', function (Builder $query) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->select('id', 'role')
                                    ->where('role', 'like', 'INSTRUCTOR');
                            });
                    });
            });
        }
    }

    public function scopeCoachs(Builder $query, bool $is_coach): Builder
    {
        if ($is_coach) {
            return $query->whereHas('licenses', function (Builder $query) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->whereHas('license', function (Builder $query) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->select('id', 'role')
                                    ->where('role', 'like', 'COACH');
                            });
                    });
            });
        } else {
            return $query->whereDoesntHave('licenses', function (Builder $query) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->whereHas('license', function (Builder $query) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->select('id', 'role')
                                    ->where('role', 'like', 'COACH');
                            });
                    });
            });
        }
    }

    public function scopeReferees(Builder $query, bool $is_referee): Builder
    {
        if ($is_referee) {
            return $query->whereHas('licenses', function (Builder $query) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->whereHas('license', function (Builder $query) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->select('id', 'role')
                                    ->where('role', 'TECHNICAL_OFFICIAL');
                            });
                    });
            });
        } else {
            return $query->whereDoesntHave('licenses', function (Builder $query) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->whereHas('license', function (Builder $query) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) {
                                $query->select('id', 'role')
                                    ->where('role', 'TECHNICAL_OFFICIAL');
                            });
                    });
            });
        }
    }

    /**
     * Scope a query to only include individuals from a specific federation.
     */
    public function scopeFromFederation(Builder $query, int $federationId): Builder
    {
        return $query->whereHas('individualFederations', function ($query) use ($federationId) {
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

    public function refereedCompetitions(): HasMany
    {
        return $this->hasMany(CompetitionReferee::class, 'individual_id');
    }

    // Add Mutators for automatic transliteration
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        // Remove characters not allowed by the policy (keep letters, numbers, space, hyphen, apostrophe) before transliteration
        // Use double quotes for the regex pattern string
        $cleanedValue = preg_replace("/[^\p{L}\p{N}'\s-]/u", '', $value ?? '');
        $this->attributes['first_name_latin'] = Str::ascii($cleanedValue);
    }

    public function setSurnameAttribute($value)
    {
        $this->attributes['surname'] = $value;
        // Remove characters not allowed by the policy (keep letters, numbers, space, hyphen, apostrophe) before transliteration
        // Use double quotes for the regex pattern string
        $cleanedValue = preg_replace("/[^\p{L}\p{N}'\s-]/u", '', $value ?? '');
        $this->attributes['last_name_latin'] = Str::ascii($cleanedValue);
    }

    /**
     * Get the secure URL for the profile image
     */
    public function getSecureProfileImageUrl(): ?string
    {
        $media = $this->getFirstMedia('profile');

        if (! $media) {
            return null;
        }

        return route('secure-media.profile', [
            'individual' => $this->id,
            'media' => $media->id,
        ]);
    }

    /**
     * Get the secure URL for the profile thumbnail
     */
    public function getSecureProfileThumbnailUrl(): ?string
    {
        $media = $this->getFirstMedia('profile');

        if (! $media) {
            return null;
        }

        return route('secure-media.profile.thumb', [
            'individual' => $this->id,
            'media' => $media->id,
        ]);
    }

    /**
     * Check if the individual has a profile image
     */
    public function hasProfileImage(): bool
    {
        return $this->hasMedia('profile');
    }
}
