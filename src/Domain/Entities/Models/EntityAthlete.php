<?php

namespace Domain\Entities\Models;

use App\Models\Sport;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\EntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $status_class
 * @property mixed $state
 */
class EntityAthlete extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'individual_id',
        'sport_id',
        'entity_name',
        'individual_name',
        'sport_name',
        'status_class',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
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

    public function scopeStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'pending':
                $status = PendingEntityProfessionalRoleState::class;
                break;
            case 'canceled':
                $status = CanceledEntityProfessionalRoleState::class;
                break;
            case 'denied':
                $status = RejectedEntityProfessionalRoleState::class;
                break;
            case 'active':
                $status = ActiveEntityProfessionalRoleState::class;
                break;
        }

        return $query->where('status_class', $status);
    }

    public function scopeFilterSport(Builder $query, int $sportId): Builder
    {
        return $query->where('sport_id', $sportId);
    }

    /**
     * Get the athlete's license for this sport.
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
                $query->where('sport_id', $this->sport_id);
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
                $query->where('sport_id', $this->sport_id);
            })
            ->first();

        if ($license) {
            return $license;
        }

        // Finally try expired or any other status
        return LicenseAttributed::where('model_type', $individualMorphClass)
            ->where('model_id', $this->individual_id)
            ->whereHas('license', function ($query) {
                $query->where('sport_id', $this->sport_id);
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
