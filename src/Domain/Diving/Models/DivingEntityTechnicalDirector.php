<?php

namespace Domain\Diving\Models;

use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Diving\States\DivingTechnicalDirectorState;
use Domain\Diving\States\RemovedDivingTechnicalDirectorState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivingEntityTechnicalDirector extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'diving_entity_technical_directors';

    protected $fillable = [
        'entity_id',
        'individual_id',
        'license_attributed_id',
        'license_id',
        'certification_systems',
        'status_class',
        'message',
        'assigned_at',
        'approved_at',
        'approval_notes',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'certification_systems' => 'array',
        'assigned_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public static function getAvailableStates(): array
    {
        return [
            AssignedDivingTechnicalDirectorState::class,
            RemovedDivingTechnicalDirectorState::class,
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function licenseAttributed(): BelongsTo
    {
        return $this->belongsTo(LicenseAttributed::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function getStateAttribute(): DivingTechnicalDirectorState
    {
        return new $this->status_class($this);
    }

    public function isAssigned(): bool
    {
        return $this->status_class === AssignedDivingTechnicalDirectorState::class;
    }

    public function isRemoved(): bool
    {
        return $this->status_class === RemovedDivingTechnicalDirectorState::class;
    }

    public function canBeRemoved(): bool
    {
        return $this->state->canBeRemoved();
    }

    public function assign(?string $notes = null): void
    {
        $this->update([
            'status_class' => AssignedDivingTechnicalDirectorState::class,
            'assigned_at' => now(),
            'message' => $notes,
        ]);

        // Check if license should be activated
        $this->checkAndUpdateLicenseStatus();
    }

    public function remove(?string $notes = null): void
    {
        if (! $this->canBeRemoved()) {
            throw new \Exception('This technical director cannot be removed in its current state.');
        }

        $this->update([
            'status_class' => RemovedDivingTechnicalDirectorState::class,
            'message' => $notes,
        ]);

        // Check if this was the only director and update license status
        $this->checkAndUpdateLicenseStatus();
    }

    public function hasApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function hasRejected(): bool
    {
        return $this->rejected_at !== null;
    }

    public function isPendingApproval(): bool
    {
        return $this->isAssigned() && ! $this->hasApproved() && ! $this->hasRejected();
    }

    public function scopeAssigned($query)
    {
        return $query->where('status_class', AssignedDivingTechnicalDirectorState::class);
    }

    public function scopeRemoved($query)
    {
        return $query->where('status_class', RemovedDivingTechnicalDirectorState::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status_class', AssignedDivingTechnicalDirectorState::class);
    }

    public function scopeForEntity($query, $entityId)
    {
        return $query->where('entity_id', $entityId);
    }

    public function scopeForIndividual($query, $individualId)
    {
        return $query->where('individual_id', $individualId);
    }

    public function scopeForSystems($query, array $systems)
    {
        return $query->whereJsonContains('certification_systems', $systems);
    }

    /**
     * Check and update license status after director removal
     */
    private function checkAndUpdateLicenseStatus(): void
    {
        if (! $this->licenseAttributed) {
            return;
        }

        // Count total directors assigned for this license
        $totalDirectors = self::where('license_attributed_id', $this->license_attributed_id)->count();

        // Count removed directors
        $removedDirectors = self::where('license_attributed_id', $this->license_attributed_id)
            ->where('status_class', RemovedDivingTechnicalDirectorState::class)
            ->count();

        // Count assigned directors
        $assignedDirectors = self::where('license_attributed_id', $this->license_attributed_id)
            ->where('status_class', AssignedDivingTechnicalDirectorState::class)
            ->count();

        // If only one director was assigned and they were removed, cancel the license
        if ($totalDirectors === 1 && $removedDirectors === 1) {
            $this->licenseAttributed->status_class = \Domain\Licenses\States\CanceledLicenseAttributedState::class;
            $this->licenseAttributed->notes = 'License canceled: Technical director was removed';
            $this->licenseAttributed->save();

            // Log this action
            activity('diving_license')
                ->performedOn($this->licenseAttributed)
                ->withProperties([
                    'reason' => 'Single technical director was removed',
                    'director_id' => $this->individual_id,
                ])
                ->log('License automatically canceled due to director removal');
        }
        // If all directors were removed, cancel the license
        elseif ($totalDirectors > 1 && $totalDirectors === $removedDirectors) {
            $this->licenseAttributed->status_class = \Domain\Licenses\States\CanceledLicenseAttributedState::class;
            $this->licenseAttributed->notes = 'License canceled: All technical directors were removed';
            $this->licenseAttributed->save();

            // Log this action
            activity('diving_license')
                ->performedOn($this->licenseAttributed)
                ->withProperties([
                    'reason' => 'All technical directors were removed',
                    'total_directors' => $totalDirectors,
                ])
                ->log('License automatically canceled due to all directors being removed');
        }
        // If at least one director is assigned, activate the license if it's pending
        elseif ($assignedDirectors > 0 && $this->licenseAttributed->status_class === \Domain\Licenses\States\PendingLicenseAttributedState::class) {
            $this->licenseAttributed->status_class = \Domain\Licenses\States\ActiveLicenseAttributedState::class;
            $this->licenseAttributed->activated_at = now();
            $this->licenseAttributed->save();

            // Log this action
            activity('diving_license')
                ->performedOn($this->licenseAttributed)
                ->withProperties([
                    'assigned_directors' => $assignedDirectors,
                    'total_directors' => $totalDirectors,
                ])
                ->log('License activated with technical director assignment');
        }
    }
}
