<?php

namespace Domain\Certifications\Models;

use App\Enums\CommitteeCodeEnum;
use App\Models\Committee;
use App\Traits\CreatedUpdatedBy;
use Carbon\Carbon;
use Database\Factories\CertificationAttributedFactory;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\CanceledCertificationAttributedState;
use Domain\Certifications\States\CertificationAttributedState;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Domain\Certifications\States\DirectorApprovedCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Certifications\States\ProvisionalCertificationAttributedState;
use Domain\Certifications\States\RejectedCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Support\Traits\HasDocumentPaymentStatus;

/**
 * @method static paginate(int $int)
 * @method static create(array $certificationAttributedData)
 * @method static make(array|string[] $certificationAttributedData)
 * @method static find(int $id)
     * @method static findOrFail(string $id)
     *
     * @property \Illuminate\Support\Carbon|null $activated_at
     * @property \Illuminate\Support\Carbon|null $current_term_ends_at
     * @property \Illuminate\Support\Carbon|null $current_term_starts_at
     * @property int|null $batch_id
     * @property int|null $certification_id
     * @property int|null $entity_id
     * @property int|null $federation_id
     * @property int|null $individual_id
     * @property string|null $certification_name
     * @property string|null $international_code
     * @property string|null $federation_name
     * @property string|null $holder_name
     * @property string|null $national_code
     * @property string|null $notes
     * @property string|null $price_option
     * @property CertificationAttributedState $state
     * @property class-string<CertificationAttributedState>|null $status_class
 */
class CertificationAttributed extends Model
{
    use CreatedUpdatedBy;
    use HasDocumentPaymentStatus;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'certification_attributed';

    protected $fillable = [
        'certification_id',
        'federation_id',
        'entity_id',
        'status_class',
        'individual_id',
        'national_code',
        'international_code',
        'certification_name',
        'holder_name',
        'federation_name',
        'entity_name',
        'instructor_id',
        'code',
        'number',
        'activator_id',
        'activator_type',
        'activated_at',
        'current_term_starts_at',
        'current_term_ends_at',
        'notes',
        'price_option',
        'price_paid',
        'batch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'current_term_starts_at' => 'datetime',
        'current_term_ends_at' => 'datetime',
        'price_paid' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (CertificationAttributed $model) {
            $mainFederation = Federation::where('is_default_federation', true)->first();

            // If no main federation exists, skip validation (allows tests/seeding without main federation)
            if (! $mainFederation) {
                return;
            }

            // CertificationAttributed must always belong to the main federation
            if ($model->federation_id !== $mainFederation->id) {
                \Log::warning('CertificationAttributed: Correcting federation_id to main federation', [
                    'original_federation_id' => $model->federation_id,
                    'main_federation_id' => $mainFederation->id,
                    'certification_id' => $model->certification_id,
                ]);
                $model->federation_id = $mainFederation->id;
                $model->federation_name = $mainFederation->legal_name ?? $mainFederation->name;
            }
        });
    }

    protected static function newFactory(): CertificationAttributedFactory
    {
        return CertificationAttributedFactory::new();
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function allInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'certifications_attributed_instructors', 'attributed_id', 'individual_id', 'id', 'id');
    }

    public function mainInstructor(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'certifications_attributed_instructors', 'attributed_id', 'individual_id')
            ->wherePivot('is_main', true);
    }

    public function assistantInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'certifications_attributed_instructors', 'attributed_id', 'individual_id', 'id', 'id')->where('is_main', '=', false);
    }

    public function activator(): MorphTo
    {
        return $this->morphTo();
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function assistants(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'certifications_attributed_instructors', 'certification_attributed_id', 'individual_id');
    }

    public function getStateAttribute(): CertificationAttributedState
    {
        return new $this->status_class($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive();
    }

    public function isProvisional(): bool
    {
        return $this->state->isProvisional();
    }

    /**
     * Check if this certification includes a physical card
     */
    public function wantsPhysicalCard(): bool
    {
        return $this->price_option === 'digital_plus_card';
    }

    public function canBeDeleted(): bool
    {
        return $this->isActive() === false;
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function organizationDisplay(): string
    {
        $code = $this->federation?->member_code ?? '';
        $isInternational = $this->certification?->committee?->isInternational() ?? false;

        if (! $isInternational) {
            return $code;
        }

        $internationalName = (string) config('branding.international.name');

        return trim($code . ($internationalName !== '' ? ' / ' . $internationalName : ''));
    }

    public function getCardUrlAttribute(): string
    {
        $relative = 'img/cards/' . $this->certification->certification_view;

        // If you store cards under the *public* disk:
        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->url($relative);
        }

        // Fallback
        return asset('img/default_certification_card.jpg');
    }

    public function scopeCertificationAttributedStatus(Builder $query, string $status): Builder
    {
        $statusMap = [
            'active' => ActiveCertificationAttributedState::class,
            'pending' => PendingCertificationAttributedState::class,
            'canceled' => CanceledCertificationAttributedState::class,
            'waiting_director' => DirectorApprovalCertificationAttributedState::class,
            'waiting_nf' => DirectorApprovedCertificationAttributedState::class,
            'expired' => ExpiredCertificationAttributedState::class,
            'provisional' => ProvisionalCertificationAttributedState::class,
            'rejected' => RejectedCertificationAttributedState::class,
            'suspended' => SuspendedCertificationAttributedState::class,
        ];

        if (array_key_exists($status, $statusMap)) {
            return $query->where('status_class', $statusMap[$status]);
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

    public function scopeEmissionBefore(Builder $query, string $date): Builder
    {
        return $query->whereDate('activated_at', '>=', Carbon::parse($date));
    }

    public function scopeEmissionAfter(Builder $query, string $date): Builder
    {
        return $query->whereDate('activated_at', '<=', Carbon::parse($date));
    }

    public function scopeEntityName(Builder $query, string $entity_name): Builder
    {
        return $query->where('entity_name', 'like', '%' . $entity_name . '%');
    }

    public function scopeIndividualName(Builder $query, string $individual_name): Builder
    {
        return $query->where('holder_name', 'like', '%' . $individual_name . '%');
    }

    public function scopeFilterStudentName(Builder $query, string $name): Builder
    {
        return $query->where(function (Builder $q) use ($name) {
            $q->whereHas('individual', fn (Builder $iq) => $iq->where('name', 'like', '%' . $name . '%'))
                ->orWhere('holder_name', 'like', '%' . $name . '%');
        });
    }

    public function scopeFilterStudentSurname(Builder $query, string $surname): Builder
    {
        return $query->where(function (Builder $q) use ($surname) {
            $q->whereHas('individual', fn (Builder $iq) => $iq->where('surname', 'like', '%' . $surname . '%'))
                ->orWhere('holder_name', 'like', '%' . $surname . '%');
        });
    }

    public function scopeCertificationName(Builder $query, string $certification_name): Builder
    {
        return $query->where('certification_name', 'like', '%' . $certification_name . '%');
    }

    public function scopeCertificationId(Builder $query, int $certification_id): Builder
    {
        return $query->where('certification_id', $certification_id);
    }

    public function scopeFederation(Builder $query, int $federation_id): Builder
    {
        return $query->where(compact('federation_id'));
    }

    public function scopeEntity(Builder $query, int $entity_id): Builder
    {
        return $query->where(compact('entity_id'));
    }

    /**
     * Scope a query to only include results from date
     */
    public function scopeFilterCommittee(Builder $query, string $code): Builder
    {
        $committeeCodes = CommitteeCodeEnum::certificationFilterValues($code);

        return $query->whereHas('certification', function (Builder $q) use ($committeeCodes) {
            return $q->whereHas('committee', function (Builder $a) use ($committeeCodes) {
                return $a->whereIn('code', $committeeCodes);
            });
        });
    }

    public function scopeSport(Builder $query, int $sport_id): Builder
    {
        return $query->whereHas('certification', function (Builder $q) use ($sport_id) {
            $q->where(function (Builder $inner) use ($sport_id) {
                $inner->whereHas('license', fn (Builder $lq) => $lq->whereHas('sports', fn (Builder $sq) => $sq->where('sports.id', $sport_id))->orWhere('sport_id', $sport_id))
                    ->orWhereHas('sports', fn (Builder $sq) => $sq->where('sports.id', $sport_id));
            });
        });
    }

    public function scopeDirectorCode(Builder $query, string $member_code): Builder
    {
        return $query->whereHas('mainInstructor', function (Builder $query) use ($member_code) {
            return $query->where('member_code', 'like', '%' . $member_code . '%');
        });
    }

    public function scopeFilterDirectorMemberNumber(Builder $query, string $member_number): Builder
    {
        return $query->whereHas('mainInstructor', function (Builder $query) use ($member_number) {
            return $query->where('member_number', 'like', '%' . $member_number . '%');
        });
    }

    public function scopeMemberCode(Builder $query, string $member_code): Builder
    {
        return $query->whereHas('individual', function (Builder $query) use ($member_code) {
            return $query->where('member_code', 'like', '%' . $member_code . '%');
        });
    }

    public function scopeFilterZone(Builder $query, string $geo_zone_id): Builder
    {
        return $query->whereHas('federation', function (Builder $query) use ($geo_zone_id) {
            return $query->whereHas('country', function (Builder $query) use ($geo_zone_id) {
                return $query->where(compact('geo_zone_id'));
            });
        });
    }

    public function scopeFilterProfessional(Builder $query, string $id)
    {
        $query->whereHas('certification', function (Builder $query) use ($id) {
            $query->whereHas('professionalRole', function (Builder $query) use ($id) {
                $query->where('id', $id);
            });
        });
    }

    public function scopeFilterCertificationCategory(Builder $query, string $id)
    {
        $query->whereHas('certification', function (Builder $query) use ($id) {
            $query->where('certification_category', $id);
        });
    }

    /**
     * Scope a query to filter by the related individual's international code.
     */
    public function scopeFilterIndividualMemberCode(Builder $query, string $code): Builder
    {
        return $query->whereHas('individual', function (Builder $q) use ($code) {
            $q->where('member_code', 'like', '%' . $code . '%');
        });
    }

    public function scopeProfessional(Builder $query, string $role)
    {
        $query->whereHas('certification', function (Builder $query) use ($role) {
            $query->whereHas('professionalRole', function (Builder $query) use ($role) {
                $query->where('role', 'like', strtoupper($role));
            });
        });
    }

    public function scopeFilterCountry(Builder $query, int $country_id): Builder
    {
        return $query->whereHas('federation', function (Builder $query) use ($country_id) {
            $query->where('country_id', $country_id);
        });
    }

}
