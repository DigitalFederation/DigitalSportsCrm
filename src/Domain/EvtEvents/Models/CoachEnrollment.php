<?php

namespace Domain\EvtEvents\Models;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Database\Factories\CoachEnrollmentFactory;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\States\AssignedCoachEnrollmentState;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $event_id
 * @property float|null $price
 * @property string|null $price_type
 * @property Event|null $event
 * @property Enrollment|null $enrollment
 * @property Pricing|null $pricing
 */
class CoachEnrollment extends Model
{
    use HasFactory;

    protected static function newFactory(): CoachEnrollmentFactory
    {
        return CoachEnrollmentFactory::new();
    }
    protected $table = 'evt_coaches_enrollment';

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

    protected $casts = [
        'price' => 'float',
        'individual_id' => 'string',
    ];

    // Define possible states
    protected $statusClasses = [
        'RegisteredCoachEnrollmentState' => RegisteredCoachEnrollmentState::class,
        'CanceledCoachEnrollmentState' => CanceledCoachEnrollmentState::class,
        'PendingCoachEnrollmentState' => PendingCoachEnrollmentState::class,
        'AssignedCoachEnrollmentState' => AssignedCoachEnrollmentState::class,
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
        return $this->hasMany(CoachEnrollmentAttributes::class, 'coach_enrollment_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function getEnrollmentRole(): EvtEventEnrollmentRoleEnum
    {
        return EvtEventEnrollmentRoleEnum::COACH;
    }

    public function getStateAttribute()
    {
        if (! empty($this->status_class) && class_exists($this->status_class)) {
            return new $this->status_class($this);
        }

        // Default to pending state if no valid state is set
        return new PendingCoachEnrollmentState($this);
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
        $this->status_class = CanceledCoachEnrollmentState::class;
        $this->save();
    }

    public function activate(): void
    {
        $this->status_class = RegisteredCoachEnrollmentState::class;
        $this->save();
    }

    public function assign(): void
    {
        $this->status_class = AssignedCoachEnrollmentState::class;
        $this->save();
    }
}
