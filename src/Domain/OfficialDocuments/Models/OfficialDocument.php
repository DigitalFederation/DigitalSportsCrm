<?php

namespace Domain\OfficialDocuments\Models;

use App\Enums\OfficialDocumentTypeEnum;
use App\Models\Country;
use App\Traits\CreatedUpdatedBy;
use Carbon\Carbon;
use Database\Factories\OfficialDocumentFactory;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\ExpiredOfficialDocumentState;
use Domain\OfficialDocuments\States\OfficialDocumentState;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Domain\OfficialDocuments\States\RejectedOfficialDocumentState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Lang;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property Entity|Federation|Individual|null $owner
 */
class OfficialDocument extends Model implements HasMedia
{
    use CreatedUpdatedBy;
    use HasFactory;
    use HasUuids;
    use InteractsWithMedia;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'individual_id',
        'owner_type',
        'owner_id',
        'country_id',
        'name',
        'type',
        'federation_id',
        'status_class',
        'expiry_date',
        'issue_date',
        'role',
        'activated_at',
        'created_by',
        'updated_by',
        'license_attributed_id',
    ];

    protected static function newFactory(): OfficialDocumentFactory
    {
        return OfficialDocumentFactory::new();
    }

    protected $casts = [
        'type' => OfficialDocumentTypeEnum::class,
    ];

    /**
     * Get the owner of the official document (polymorphic)
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy relationship - kept for backward compatibility
     *
     * @deprecated Use owner() relationship instead
     */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    /**
     * Get the entity if the owner is an entity
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'owner_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function getStateAttribute(): OfficialDocumentState
    {
        if ($this->expiry_date && Carbon::parse($this->expiry_date)->isPast()) {
            return new ExpiredOfficialDocumentState($this);
        }

        return new $this->status_class($this);
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor()
    {
        return $this->state->color();
    }

    /**
     * Human-readable label for the document role.
     *
     * Roles reach this model in two formats: slug roles from self-service uploads
     * (e.g. "instructor-leader", "referee-judge") and professional role codes from
     * individual uploads (e.g. "INSTRUCTOR", "TECHNICAL_OFFICIAL"). The latter reuse
     * the canonical professional_roles.role_types translations to avoid duplication.
     */
    public function roleLabel(): string
    {
        if (! $this->role) {
            return '';
        }

        $slugKey = str_replace('-', '_', $this->role);
        if (Lang::has('official_documents.roles.' . $slugKey)) {
            return __('official_documents.roles.' . $slugKey);
        }

        if (Lang::has('professional_roles.role_types.' . $this->role)) {
            return __('professional_roles.role_types.' . $this->role);
        }

        return $this->role;
    }

    public static function scopeCommittee(Builder $query, ?string $committee = null): Builder
    {
        $types = OfficialDocumentTypeEnum::getKeysByCommittee(strtoupper($committee));

        return $query->whereIn('type', $types);
    }

    public function scopeFilterType(Builder $query, $type): Builder
    {
        if (is_array($type)) {
            return $query->whereIn('type', $type);
        }

        return $query->where('type', $type);
    }
    public function scopeFilterFederation($query, $federationId)
    {
        return $query->where('federation_id', $federationId);
    }
    public function scopeFilterStatus(Builder $query, $status): Builder
    {
        $statusClassMap = [
            'pending' => PendingOfficialDocumentState::class,
            'active' => ActiveOfficialDocumentState::class,
            'rejected' => RejectedOfficialDocumentState::class,
            'expired' => ExpiredOfficialDocumentState::class,
        ];

        $statusClass = $statusClassMap[$status] ?? null;

        if (empty($statusClass)) {
            return $query;
        }

        $today = Carbon::today();

        if ($status === 'expired') {
            return $query->where(function (Builder $q) use ($statusClass, $today) {
                $q->where('status_class', $statusClass)
                    ->orWhere(function (Builder $q2) use ($today) {
                        $q2->whereNotNull('expiry_date')
                            ->where('expiry_date', '<', $today);
                    });
            });
        }

        return $query->where('status_class', $statusClass)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $today);
            });
    }

    public function scopeFilterMemberCode(Builder $query, $memberCode): Builder
    {
        return $query->whereHas('individual', function ($q) use ($memberCode) {
            $q->where('member_code', 'like', '%' . $memberCode . '%');
        });
    }

    public function scopeFilterName(Builder $query, $name): Builder
    {
        return $query->whereHas('individual', function ($q) use ($name) {
            $q->where('name', 'like', '%' . $name . '%');
        });
    }

    public function scopeFilterSurname(Builder $query, $surname): Builder
    {
        return $query->whereHas('individual', function ($q) use ($surname) {
            $q->where('surname', 'like', '%' . $surname . '%');
        });
    }

    public function scopeFilterMemberNumber(Builder $query, $memberNumber): Builder
    {
        return $query->whereHas('individual', function ($q) use ($memberNumber) {
            $q->where('member_number', 'like', '%' . $memberNumber . '%');
        });
    }

    public function scopeFilterEntityMemberNumber(Builder $query, $memberNumber): Builder
    {
        return $query->where('owner_type', 'Domain\\Entities\\Models\\Entity')
            ->whereHas('owner', function ($q) use ($memberNumber) {
                $q->where('member_number', 'like', '%' . $memberNumber . '%');
            });
    }

    public function scopeFilterEntityName(Builder $query, $entityName): Builder
    {
        return $query->where('owner_type', 'Domain\\Entities\\Models\\Entity')
            ->whereHas('owner', function ($q) use ($entityName) {
                $q->where('name', 'like', '%' . $entityName . '%');
            });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('media')
            ->useDisk('secure-media');

        $this->addMediaCollection('documents')
            ->useDisk('secure-media');
    }
}
