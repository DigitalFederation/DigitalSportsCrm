<?php

namespace App\Models;

use App\Enums\UserGroupEnum;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method static insert(array $users)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles;
    use HasUuids;
    use Notifiable;
    use TwoFactorAuthenticatable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'active',
        'locale',
        'last_login_at',
        'email_verified_at',
        'welcome_email_sent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $dates = ['last_login_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'welcome_email_sent_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'profile_photo_url',
    ];
    // If user is an entity, get the primary Entity
    public function getEntity()
    {
        // Cache key unique for each user
        $cacheKey = "user:{$this->id}:primary_entity";

        // We assume that the user has only one entity wich is true
        return cache()->remember($cacheKey, now()->addDay(), function () {
            $entity = $this->entities()->first();

            return $entity ? $entity : null;
        });
    }

    public function getIndividual()
    {
        // Cache key unique for each user
        $cacheKey = "user:{$this->id}:primary_individual";

        // We assume that the user has only one individual which is true
        return cache()->remember($cacheKey, now()->addDay(), function () {
            $individual = $this->individual;

            return $individual ? $individual : null;
        });
    }

    public function getFederationId()
    {
        // Cache key unique for each user
        $cacheKey = "user:{$this->id}:primary_federation_id";

        // Retrieve the federation ID from cache or execute the closure to get and cache it
        return cache()->rememberForever($cacheKey, function () {
            $federation = $this->federations()->first();

            return $federation ? $federation->id : null;
        });
    }

    public function getFederation()
    {
        // Cache key unique for each user
        $cacheKey = "user:{$this->id}:primary_federation";

        // Retrieve the federation ID from cache or execute the closure to get and cache it
        return cache()->rememberForever($cacheKey, function () {
            $federation = $this->federations()->first();

            return $federation ? $federation : null;
        });
    }

    public function getEntityId()
    {
        // Cache key unique for each user
        $cacheKey = "user:{$this->id}:primary_entity_id";

        // Retrieve the federation ID from cache or execute the closure to get and cache it
        return cache()->rememberForever($cacheKey, function () {
            $entity = $this->entities()->first();

            return $entity ? $entity->id : null;
        });
    }

    public function individuals(): HasMany
    {
        return $this->hasMany(Individual::class);
    }

    // Relationship to the individual model
    public function individual(): HasOne
    {
        return $this->hasOne(Individual::class);
    }

    public function documentsCreated(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function documentsUpdated(): HasMany
    {
        return $this->hasMany(Document::class, 'updated_by');
    }

    public function eventsApplicationCreated(): HasMany
    {
        return $this->hasMany(EventApplication::class, 'created_by');
    }

    public function eventsApplicationUpdated(): HasMany
    {
        return $this->hasMany(EventApplication::class, 'updated_by');
    }

    public function individualsCreated(): HasMany
    {
        return $this->hasMany(Individual::class, 'created_by');
    }

    public function IndividualsUpdated(): HasMany
    {
        return $this->hasMany(Individual::class, 'updated_by');
    }

    public function licensesAttributedCreated(): HasMany
    {
        return $this->hasMany(LicenseAttributed::class, 'created_by');
    }

    public function licensesAttributedUpdated(): HasMany
    {
        return $this->hasMany(LicenseAttributed::class, 'updated_by');
    }

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'entity_user');
    }

    public function federations(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'federation_user', 'user_id', 'federation_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function hasGroup(string $groupCode): bool
    {
        return $this->group?->code === strtoupper($groupCode);
    }

    public function scopeFilterDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeFilterEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', 'like', '%' . $email . '%');
    }

    public function scopeFilterStatus(Builder $query, bool $status): Builder
    {
        return $query->where('active', $status);
    }

    public function scopeFilterRelationship(Builder $query, $relationship): Builder
    {
        if ($relationship === 'federation') {
            return $query->whereHas('federations');
        } elseif ($relationship === 'entity') {
            return $query->whereHas('entities');
        } elseif ($relationship === 'individual') {
            return $query->whereHas('individuals');
        }

        return $query;
    }

    public function isIndividual(): bool
    {
        return $this->group_id === UserGroupEnum::INDIVIDUAL->value;
    }

    public function isEntity(): bool
    {
        return $this->group_id === UserGroupEnum::ENTITY->value;
    }

    public function isFederation(): bool
    {
        return $this->group_id === UserGroupEnum::FEDERATION->value;
    }

    public function isAdmin(): bool
    {
        return $this->group_id === UserGroupEnum::ADMIN->value;
    }

    /**
     * Get the URL to the user's profile photo.
     * Override to handle entity logos
     */
    protected function profilePhotoUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(function (): string {
            // If user is an entity, get logo from entity's media collection
            if ($this->isEntity()) {
                $entity = $this->getEntity();
                if ($entity && $entity->hasMedia('profile')) {
                    return $entity->getFirstMediaUrl('profile');
                }
            }

            // Otherwise use default Jetstream behavior
            return $this->profile_photo_path
                    ? \Illuminate\Support\Facades\Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     * Override to handle entity names
     */
    protected function defaultProfilePhotoUrl()
    {
        // For entity users, use entity name if available
        $displayName = $this->name;
        if ($this->isEntity()) {
            $entity = $this->getEntity();
            if ($entity) {
                $displayName = $entity->name;
            }
        }

        $name = trim(collect(explode(' ', $displayName))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that profile photos should be stored on.
     */
    protected function profilePhotoDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.profile_photo_disk', 'public');
    }

    /**
     * Send the password reset notification.
     * Overrides the default to use bilingual notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
