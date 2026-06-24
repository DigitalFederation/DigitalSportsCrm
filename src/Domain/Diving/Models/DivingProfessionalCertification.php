<?php

namespace Domain\Diving\Models;

use App\Models\User;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\DivingCertificationState;
use Domain\Diving\States\ExpiredDivingCertificationState;
use Domain\Diving\States\PendingValidationDivingCertificationState;
use Domain\Diving\States\RevokedDivingCertificationState;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $rejection_reason
 * @property string|null $revocation_reason
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property int|string|null $revoked_by
 */
class DivingProfessionalCertification extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'individual_id',
        'certification_name',
        'certification_system',
        'document_type',
        'certification_level',
        'certification_number',
        'national_equivalency',
        'issue_date',
        'expiration_date',
        'status_class',
        'validation_notes',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiration_date' => 'date',
        'validated_at' => 'datetime',
    ];

    public static function getAvailableStates(): array
    {
        return [
            PendingValidationDivingCertificationState::class,
            ActiveDivingCertificationState::class,
            ExpiredDivingCertificationState::class,
            RevokedDivingCertificationState::class,
        ];
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getStateAttribute(): DivingCertificationState
    {
        return new $this->status_class($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive() &&
               ($this->expiration_date === null || $this->expiration_date->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expiration_date !== null && $this->expiration_date->isPast();
    }

    public function isPendingValidation(): bool
    {
        return $this->status_class === PendingValidationDivingCertificationState::class;
    }

    public function canBeValidated(): bool
    {
        return $this->state->canBeValidated();
    }

    public function canBeRevoked(): bool
    {
        return $this->state->canBeRevoked();
    }

    public function transitionTo(string $stateClass): void
    {
        if (! in_array($stateClass, self::getAvailableStates())) {
            throw new \InvalidArgumentException("Invalid state class: {$stateClass}");
        }

        $this->status_class = $stateClass;
        $this->save();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificate_documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->singleFile()
            ->useDisk('secure-media');
    }

    public function scopeActive($query)
    {
        return $query->where('status_class', ActiveDivingCertificationState::class)
            ->where(function ($q) {
                $q->whereNull('expiration_date')
                    ->orWhere('expiration_date', '>', now());
            });
    }

    public function scopePendingValidation($query)
    {
        return $query->where('status_class', PendingValidationDivingCertificationState::class);
    }

    public function scopeForSystem($query, string $system)
    {
        return $query->where('certification_system', $system);
    }

    public function scopeForIndividual($query, $individualId)
    {
        return $query->where('individual_id', $individualId);
    }
}
