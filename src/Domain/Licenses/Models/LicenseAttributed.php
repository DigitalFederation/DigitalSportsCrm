<?php

namespace Domain\Licenses\Models;

use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Carbon\Carbon;
use Database\Factories\LicenseAttributedFactory;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\LicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\ProvisionalLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Domain\Licenses\States\WaitingApprovalLicenseAttributedState;
use Domain\Memberships\Models\Membership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Support\Traits\HasDocumentPaymentStatus;

/**
 * @method static create(array $data)
 * @method static make(array $data)
 * @method static insert(array $records)
 * @method static find(int $id)
 *
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $current_term_ends_at
 * @property \Illuminate\Support\Carbon|null $current_term_starts_at
 * @property \Illuminate\Support\Carbon|null $date_expire
 * @property int|null $license_id
 * @property int|null $federation_id
 * @property int|string|null $model_id
 * @property int|null $requested_by_id
 * @property string|null $federation_name
 * @property string|null $holder_name
 * @property string|null $license_name
 * @property string|null $model_type
 * @property string|null $notes
 * @property string|null $owner_type
 * @property string|null $requester_model_type
 * @property string|null $validation_notes
 * @property int|string|null $validated_by
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property string|float|int|null $total_value
 * @property class-string<LicenseAttributedState>|null $status_class
 * @property LicenseAttributedState $state
 */
class LicenseAttributed extends Model
{
    use CreatedUpdatedBy;
    use HasDocumentPaymentStatus;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'license_attributed';

    protected $fillable = [
        'status_class',
        'license_id',
        'federation_id',
        'membership_id',
        'model_type',
        'model_id',
        'license_name',
        'holder_name',
        'federation_name',
        'activated_at',
        'license_number',
        'total_value',
        'date_begin',
        'date_expire',
        'notes',
        'created_by',
        'updated_by',
        'requester_model_type',
        'requested_by_id',
        'request_type',
        'payment_id',
        'purchased_at',
        'current_term_starts_at',
        'current_term_ends_at',
    ];

    public string $morphName = 'License';

    protected $casts = [
        'activated_at' => 'datetime',
        'current_term_starts_at' => 'date',
        'current_term_ends_at' => 'date',
        'date_begin' => 'datetime',
        'date_expire' => 'datetime',
        'purchased_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    protected static function booted()
    {
        // static::addGlobalScope(new BelongsToFederationScope());
        static::addGlobalScope(new ExcludeInternationalScope);

        static::creating(function (LicenseAttributed $licenseAttributed) {
            if (empty($licenseAttributed->status_class)) {
                $licenseAttributed->status_class = PendingLicenseAttributedState::class;
            }
        });

        static::updating(function (LicenseAttributed $licenseAttributed) {
            if (empty($licenseAttributed->status_class)) {
                $licenseAttributed->status_class = PendingLicenseAttributedState::class;
            }
        });
    }

    protected static function newFactory(): LicenseAttributedFactory
    {
        return LicenseAttributedFactory::new();
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class)->withoutGlobalScope(ExcludeInternationalScope::class);
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public function divingTechnicalDirectors(): HasMany
    {
        return $this->hasMany(\Domain\Diving\Models\DivingEntityTechnicalDirector::class);
    }

    /**
     * Alias for divingTechnicalDirectors for backward compatibility
     * Used by DivingLicenseValidationController
     */
    public function divingTechnicalDirectorInvitations(): HasMany
    {
        return $this->divingTechnicalDirectors();
    }

    public function userCreated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'requested_by_id');
    }

    public function getStateAttribute(): LicenseAttributedState
    {
        $statusClass = $this->status_class ?: PendingLicenseAttributedState::class;

        return new $statusClass($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function scopeLicenseAttributedStatus(Builder $query, string $status): Builder
    {
        $statusMap = [
            'active' => ActiveLicenseAttributedState::class,
            'pending' => PendingLicenseAttributedState::class,
            'canceled' => CanceledLicenseAttributedState::class,
            'provisional' => ProvisionalLicenseAttributedState::class,
            'suspended' => SuspendedLicenseAttributedState::class,
            'waiting_approval' => WaitingApprovalLicenseAttributedState::class,
            'expired' => ExpiredLicenseAttributedState::class,
        ];

        if (isset($statusMap[$status])) {
            return $query->where('status_class', '=', $statusMap[$status]);
        }

        return $query;
    }

    public function scopeExpirationBefore(Builder $query, string $date): Builder
    {
        return $query->whereDate('current_term_ends_at', '>=', Carbon::parse($date));
    }

    public function scopeExpirationAfter(Builder $query, string $date): Builder
    {
        return $query->whereDate('current_term_ends_at', '<=', Carbon::parse($date));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status_class', ExpiredLicenseAttributedState::class);
    }

    public function scopeHolderType(Builder $query, string $holder_type): Builder
    {
        // Use the morph alias directly (already 'entity', 'individual', or 'federation')
        return $query->where('model_type', $holder_type);
    }

    public function scopeHolderName(Builder $query, string $holder_name): Builder
    {
        return $query->where('holder_name', 'like', '%' . $holder_name . '%');
    }

    public function scopeLicenseName(Builder $query, string $license_name): Builder
    {
        return $query->where('license_name', 'like', '%' . $license_name . '%');
    }

    public function scopeCommittee(Builder $query, string $code): Builder
    {
        return $query->whereHas('license', function (Builder $q) use ($code) {
            return $q->whereHas('committee', function (Builder $a) use ($code) {
                return $a->where(compact('code'));
            });
        });
    }

    public function scopeFederation(Builder $query, int $federation_id): Builder
    {
        return $query->where(compact('federation_id'));
    }

    public function scopeEntity(Builder $query, int $entity_id): Builder
    {
        return $query->where('model_type', 'entity')->where('model_id', $entity_id);
    }

    public function scopeCountry(Builder $query, int $country_id): Builder
    {
        return $query->whereHas('federation', function (Builder $query) use ($country_id) {
            return $query->where(compact('country_id'));
        });
    }

    public function scopeMemberCode(Builder $query, string $member_code): Builder
    {
        return $query->whereHas('owner', function (Builder $query) use ($member_code) {
            return $query->where('member_code', 'like', '%' . $member_code . '%');
        });
    }

    public function scopeSport(Builder $query, int $sport_id): Builder
    {
        return $query->whereHas('license', function (Builder $query) use ($sport_id) {
            $query->where(compact('sport_id'));
        });
    }

    public function scopeProfessionalRole(Builder $query, int $professional_role_id): Builder
    {
        return $query->whereHas('license', function (Builder $query) use ($professional_role_id) {
            return $query->where(compact('professional_role_id'));
        });
    }

    public function scopeFilterZone(Builder $query, int $geo_zone_id): Builder
    {
        return $query->whereHas('federation', function (Builder $query) use ($geo_zone_id) {
            return $query->whereHas('country', function (Builder $query) use ($geo_zone_id) {
                return $query->where(compact('geo_zone_id'));
            });
        });
    }

    public function scopeFilterProfessional(Builder $query, string $role): Builder
    {
        return $query->whereHas('license', function (Builder $query) use ($role) {
            $query->whereHas('professionalRole', function (Builder $query) use ($role) {
                $query->when(in_array($role, ['refereejudge', 'technical_official']), function (Builder $query) {
                    $query->where('role', 'TECHNICAL_OFFICIAL');
                })->when($role == 'instructorleader', function (Builder $query) {
                    $query->where('role', 'like', 'INSTRUCTOR')->orWhere('role', 'like', 'LEADER');
                })->when(! in_array($role, ['refereejudge', 'technical_official', 'instructorleader']), function (Builder $query) use ($role) {
                    $query->where('role', 'like', ucwords($role));
                });
            });
        });
    }

    public function getMemberCodeAttribute()
    {
        $owner = $this->owner;

        if ($owner instanceof Individual) {
            return $owner->member_code;
        }

        if ($owner instanceof Entity) {
            return $owner->member_code;
        }

        return null;
    }

    public function scopeRequestedByEntity(Builder $query, int $entity_id): Builder
    {
        return $query->where('requested_by_id', $entity_id);
    }

    public function scopeDirectRequests(Builder $query): Builder
    {
        return $query->where('request_type', 'direct');
    }

    public function scopeEntityGroupRequests(Builder $query): Builder
    {
        return $query->where('request_type', 'entity_group');
    }

    public function officialDocuments(): HasMany
    {
        return $this->hasMany(\Domain\OfficialDocuments\Models\OfficialDocument::class);
    }

    public function hasDocument(string $docType): bool
    {
        return $this->officialDocuments()
            ->where('type', $docType)
            ->where('status_class', \Domain\OfficialDocuments\States\ActiveOfficialDocumentState::class)
            ->exists();
    }

    public function scopeEmissionBefore(Builder $query, string $date): Builder
    {
        return $query->whereDate('current_term_starts_at', '>=', Carbon::parse($date));
    }

    public function scopeEmissionAfter(Builder $query, string $date): Builder
    {
        return $query->whereDate('current_term_starts_at', '<=', Carbon::parse($date));
    }

    /**
     * Filter by individual's first name
     */
    public function scopeIndividualFirstName(Builder $query, string $name): Builder
    {
        return $query->where('model_type', 'individual')
            ->whereHas('owner', function (Builder $q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
    }

    /**
     * Filter by individual's surname
     */
    public function scopeIndividualSurname(Builder $query, string $surname): Builder
    {
        return $query->where('model_type', 'individual')
            ->whereHas('owner', function (Builder $q) use ($surname) {
                $q->where('surname', 'like', '%' . $surname . '%');
            });
    }

    /**
     * Filter by individual's member number
     */
    public function scopeIndividualMemberNumber(Builder $query, string $memberNumber): Builder
    {
        return $query->where('model_type', 'individual')
            ->whereHas('owner', function (Builder $q) use ($memberNumber) {
                $q->where('member_number', 'like', '%' . $memberNumber . '%');
            });
    }

    /**
     * Filter by license_id
     */
    public function scopeLicenseId(Builder $query, string $licenseId): Builder
    {
        return $query->where('license_id', $licenseId);
    }

    /**
     * Filter by entity name
     */
    public function scopeEntityName(Builder $query, string $name): Builder
    {
        return $query->where('model_type', 'entity')
            ->whereHas('owner', function (Builder $q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
    }

}
