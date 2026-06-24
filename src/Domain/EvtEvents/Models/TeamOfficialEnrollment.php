<?php

namespace Domain\EvtEvents\Models;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Database\Factories\TeamOfficialEnrollmentFactory;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\States\AssignedTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\PendingTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\TeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $event_id
 * @property float|null $price
 * @property string|null $price_type
 * @property Event|null $event
 * @property Enrollment|null $enrollment
 * @property Pricing|null $pricing
 */
class TeamOfficialEnrollment extends Model
{
    use HasFactory;

    protected $table = 'evt_officials_enrollment';

    protected $fillable = [
        'enrollment_id',
        'federation_id',
        'entity_id',
        'individual_id',
        'event_id',
        'price_type',
        'price',
        'pricing_id',
        'status_class',
    ];

    protected static function newFactory()
    {
        return TeamOfficialEnrollmentFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (! $model->federation_id && ! $model->entity_id) {
                Log::error('Attempt to save TeamOfficialEnrollment without federation_id or entity_id', [
                    'enrollment_id' => $model->enrollment_id,
                    'individual_id' => $model->individual_id,
                    'event_id' => $model->event_id,
                ]);
                throw new \InvalidArgumentException('TeamOfficialEnrollment must have either a federation_id or entity_id');
            }
        });

        static::deleting(function (TeamOfficialEnrollment $model) {
            $model->attributes()->delete();
        });
    }

    // Define possible states
    protected $statusClasses = [
        'CanceledTeamOfficialEnrollmentState' => CanceledTeamOfficialEnrollmentState::class,
        'PendingTeamOfficialEnrollmentState' => PendingTeamOfficialEnrollmentState::class,
        'RegisteredTeamOfficialEnrollmentState' => RegisteredTeamOfficialEnrollmentState::class,
        'AssignedTeamOfficialEnrollmentState' => AssignedTeamOfficialEnrollmentState::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class, 'federation_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'individual_id');
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class, 'pricing_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(OfficialsEnrollmentAttributes::class, 'officials_enrollment_id');
    }

    public function getEnrollmentRole(): EvtEventEnrollmentRoleEnum
    {
        return EvtEventEnrollmentRoleEnum::OFFICIAL;
    }

    public function getStateAttribute(): TeamOfficialEnrollmentState
    {
        if (! empty($this->status_class) && class_exists($this->status_class)) {
            return new $this->status_class($this);
        }

        // Default to pending state if no valid state is set
        return new PendingTeamOfficialEnrollmentState($this);
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function cancel(): void
    {
        $this->status_class = CanceledTeamOfficialEnrollmentState::class;
        $this->save();
    }

    public function activate(): void
    {
        $this->status_class = RegisteredTeamOfficialEnrollmentState::class;
        $this->save();
    }

    public function assign(): void
    {
        $this->status_class = AssignedTeamOfficialEnrollmentState::class;
        $this->save();
    }
}
