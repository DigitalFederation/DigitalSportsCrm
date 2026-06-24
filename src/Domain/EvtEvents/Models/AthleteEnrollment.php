<?php

namespace Domain\EvtEvents\Models;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use Database\Factories\AthleteEnrollmentFactory;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $event_id
 * @property float|null $total_price
 * @property float|null $price
 * @property float|null $per_person_price
 * @property float|null $discipline_price
 * @property Event|null $event
 * @property Enrollment|null $enrollment
 * @property Pricing|null $pricing
 */
class AthleteEnrollment extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'evt_athletes_enrollment';

    protected $fillable = [
        'enrollment_id',
        'event_id',
        'federation_id',
        'entity_id',
        'individual_id',
        'discipline_id',
        'per_person_price',
        'discipline_price',
        'event_fee',
        'total_price',
        'per_person_pricing_id',
        'discipline_pricing_id',
        'event_fee_pricing_id',
        'status_class',
        'team_identifier',
    ];

    protected $casts = [
        'per_person_price' => 'float',
        'discipline_price' => 'float',
        'event_fee' => 'float',
        'total_price' => 'float',
        'status_class' => EvtAthleteEnrollmentStatusEnum::class,
    ];

    protected static function newFactory(): AthleteEnrollmentFactory
    {
        return AthleteEnrollmentFactory::new();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class, 'federation_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
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
        return $this->hasMany(AthleteEnrollmentAttributes::class, 'athlete_enrollment_id');
    }

    public function getStateAttribute()
    {
        if (! empty($this->status_class) && class_exists($this->status_class)) {
            return new $this->status_class($this);
        }

        // Optionally, return a default state or null if class does not exist
        return null;
    }

    public function stateName(): string
    {
        return $this->state ? $this->state->name() : '';
    }

    public function calculateTotalPrice()
    {
        $this->total_price = $this->per_person_price + $this->discipline_price + $this->event_fee;
        $this->save();
    }

    public function perPersonPricing()
    {
        return $this->belongsTo(Pricing::class, 'per_person_pricing_id');
    }

    public function disciplinePricing()
    {
        return $this->belongsTo(Pricing::class, 'discipline_pricing_id');
    }

    public function eventFeePricing()
    {
        return $this->belongsTo(Pricing::class, 'event_fee_pricing_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'discipline_id',
                'status_class',
                'per_person_price',
                'discipline_price',
                'event_fee',
                'total_price',
                'team_identifier',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('athlete_enrollment')
            ->setDescriptionForEvent(fn (string $eventName) => "Athlete Enrollment has been {$eventName}");
    }
}
