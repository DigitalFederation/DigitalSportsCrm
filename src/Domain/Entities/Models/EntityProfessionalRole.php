<?php

namespace Domain\Entities\Models;

use Database\Factories\EntityProfessionalRoleFactory;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\EntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\EvtEvents\Models\Sport;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $status_class
 * @property mixed $state
 *
 * @method static create(array $array)
 * @method static find(int $id)
 * @method static role(string $role)
 * @method static select(string ...$columns)
 */
class EntityProfessionalRole extends Model
{
    use HasFactory;

    protected $table = 'entity_professional_role';

    protected $fillable = [
        'entity_id',
        'individual_id',
        'professional_role_id',
        'sport_id',
        'entity_name',
        'individual_name',
        'role_name',
        'status_class',
        'deactivated_at',
        'deactivation_reason',
        'deactivated_by',
    ];

    protected $casts = [
        'deactivated_at' => 'datetime',
    ];

    protected static function newFactory(): EntityProfessionalRoleFactory
    {
        return EntityProfessionalRoleFactory::new();
    }
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function professionalRole(): BelongsTo
    {
        return $this->belongsTo(ProfessionalRole::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function getStateAttribute(): EntityProfessionalRoleState
    {
        return new $this->status_class($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive();
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function scopeRole(Builder $query, string ...$role): void
    {
        $query->whereHas('professionalRole', function (Builder $query) use ($role) {
            $query->whereIn('role', $role);
        });
    }

    public function scopeStatus(Builder $query, string $status): void
    {
        switch ($status) {
            case 'active':
                $status = ActiveEntityProfessionalRoleState::class;
                break;
            case 'pending':
                $status = PendingEntityProfessionalRoleState::class;
                break;
            case 'canceled':
                $status = CanceledEntityProfessionalRoleState::class;
                break;
            case 'denied':
                $status = RejectedEntityProfessionalRoleState::class;
                break;
        }

        $query->where('status_class', $status);
    }

    /**
     * Deactivate the professional role relationship
     *
     * @param  string  $reason  The reason for deactivation
     * @param  string  $deactivatedBy  Who initiated the deactivation ('entity' or 'individual')
     */
    public function deactivate(string $reason, string $deactivatedBy): bool
    {
        if ($this->status_class !== ActiveEntityProfessionalRoleState::class) {
            return false;
        }

        $result = $this->update([
            'status_class' => RejectedEntityProfessionalRoleState::class,
            'deactivated_at' => now(),
            'deactivation_reason' => $reason,
            'deactivated_by' => $deactivatedBy,
        ]);

        if ($result) {
            // Remove individual from entity's local federations (if no other active entity in that federation)
            $syncAction = new SyncIndividualLocalFederationsAction;
            $syncAction->removeOnDeactivation($this->individual, $this->entity);
        }

        return $result;
    }

    /**
     * Check if the role can be deactivated
     */
    public function canBeDeactivated(): bool
    {
        return $this->status_class === ActiveEntityProfessionalRoleState::class;
    }

    /**
     * Reactivate a previously deactivated professional role
     */
    public function reactivate(): bool
    {
        if ($this->status_class !== RejectedEntityProfessionalRoleState::class) {
            return false;
        }

        return $this->update([
            'status_class' => ActiveEntityProfessionalRoleState::class,
            'deactivated_at' => null,
            'deactivation_reason' => null,
            'deactivated_by' => null,
        ]);
    }

    /**
     * Accept the professional role invitation
     * Creates IndividualEntity relationship automatically
     */
    public function accept(): bool
    {
        if ($this->status_class !== PendingEntityProfessionalRoleState::class) {
            return false;
        }

        return \DB::transaction(function () {
            // Update to active state
            $this->update([
                'status_class' => ActiveEntityProfessionalRoleState::class,
            ]);

            // Create or activate IndividualEntity relationship
            $individualEntity = \Domain\Individuals\Models\IndividualEntity::firstOrCreate([
                'individual_id' => $this->individual_id,
                'entity_id' => $this->entity_id,
            ], [
                'status_class' => \Domain\Individuals\States\ActiveIndividualEntityState::class,
            ]);

            // If relationship existed but was inactive, activate it
            if ($individualEntity->wasRecentlyCreated === false &&
                $individualEntity->status_class !== \Domain\Individuals\States\ActiveIndividualEntityState::class) {
                $individualEntity->update([
                    'status_class' => \Domain\Individuals\States\ActiveIndividualEntityState::class,
                ]);
            }

            // Sync individual to entity's local federations
            $syncAction = new SyncIndividualLocalFederationsAction;
            $syncAction->execute($this->individual, $this->entity);

            return true;
        });
    }

    /**
     * Reject the professional role invitation
     */
    public function reject(?string $reason = null): bool
    {
        if ($this->status_class !== PendingEntityProfessionalRoleState::class) {
            return false;
        }

        return $this->update([
            'status_class' => RejectedEntityProfessionalRoleState::class,
            'deactivated_at' => now(),
            'deactivation_reason' => $reason ?? 'Invitation rejected by individual',
            'deactivated_by' => 'individual',
        ]);
    }

    /**
     * Cancel the professional role invitation
     */
    public function cancel(?string $reason = null): bool
    {
        if ($this->status_class !== PendingEntityProfessionalRoleState::class) {
            return false;
        }

        return $this->update([
            'status_class' => CanceledEntityProfessionalRoleState::class,
            'deactivated_at' => now(),
            'deactivation_reason' => $reason ?? 'Invitation canceled by entity',
            'deactivated_by' => 'entity',
        ]);
    }

    /**
     * Get the coach's license for this sport.
     * Returns the most relevant license (active > pending > expired).
     */
    public function getSportLicense(): ?LicenseAttributed
    {
        if (! $this->individual_id || ! $this->sport_id) {
            return null;
        }

        // Use the morph alias from the morph map (defined in AppServiceProvider)
        $individualMorphClass = (new Individual)->getMorphClass();

        // Try to find active license first
        $license = LicenseAttributed::where('model_type', $individualMorphClass)
            ->where('model_id', $this->individual_id)
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('sports', fn ($sq) => $sq->where('sports.id', $this->sport_id))
                        ->orWhere('sport_id', $this->sport_id);
                });
            })
            ->first();

        if ($license) {
            return $license;
        }

        // Then try pending
        $license = LicenseAttributed::where('model_type', $individualMorphClass)
            ->where('model_id', $this->individual_id)
            ->where('status_class', PendingLicenseAttributedState::class)
            ->whereHas('license', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('sports', fn ($sq) => $sq->where('sports.id', $this->sport_id))
                        ->orWhere('sport_id', $this->sport_id);
                });
            })
            ->first();

        if ($license) {
            return $license;
        }

        // Finally try expired or any other status
        return LicenseAttributed::where('model_type', $individualMorphClass)
            ->where('model_id', $this->individual_id)
            ->whereHas('license', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('sports', fn ($sq) => $sq->where('sports.id', $this->sport_id))
                        ->orWhere('sport_id', $this->sport_id);
                });
            })
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Get the license status name for display.
     */
    public function getLicenseStatusName(): string
    {
        $license = $this->getSportLicense();

        if (! $license) {
            return __('licenses.states.no_license');
        }

        return $license->state->name();
    }

    /**
     * Get the license status color for display.
     */
    public function getLicenseStatusColor(): string
    {
        $license = $this->getSportLicense();

        if (! $license) {
            return 'gray';
        }

        return $license->state->color();
    }
}
