<?php

namespace Domain\EvtEvents\Models;

use App\Models\User;
use Database\Factories\EnrollmentsFactory;
use Domain\Documents\Models\Document;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $event_id
 * @property int|string|null $document_id
 * @property string|null $enrollable_type
 * @property \Illuminate\Database\Eloquent\Collection<int, IndividualEnrollment> $individualEnrollments
 * @property \Illuminate\Database\Eloquent\Collection<int, AthleteEnrollment> $athleteEnrollments
 * @property \Illuminate\Database\Eloquent\Collection<int, CoachEnrollment> $coachEnrollments
 * @property \Illuminate\Database\Eloquent\Collection<int, RefereeEnrollment> $refereeEnrollments
 * @property \Illuminate\Database\Eloquent\Collection<int, TeamOfficialEnrollment> $teamOfficialEnrollments
 * @property \Illuminate\Database\Eloquent\Collection<int, TeamOfficialEnrollment> $officialsEnrollments
 * @property Event|null $event
 * @property Pricing|null $pricing
 */
class Enrollment extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'evt_enrollments';

    protected $fillable = [
        'user_id',
        'event_id',
        'enrollable_id',
        'enrollable_type',
        'activated_at',
        'payment_status',
        'pricing_id',
        'document_id',
        'total_price',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'total_price' => 'float',
    ];

    protected static function newFactory(): EnrollmentsFactory
    {
        return EnrollmentsFactory::new();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function enrollable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function individualEnrollments(): HasMany
    {
        return $this->hasMany(IndividualEnrollment::class);
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class, 'pricing_id');
    }

    // Method to get the display name of the individual
    public function getDisplayName(): string
    {

        if (! empty($this->enrollable->full_name)) {
            return $this->enrollable->full_name;
        }

        return 'Enrollment';
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function athleteEnrollments(): HasMany
    {
        return $this->hasMany(AthleteEnrollment::class);
    }

    public function coachEnrollments(): HasMany
    {
        return $this->hasMany(CoachEnrollment::class);
    }
    public function refereeEnrollments(): HasMany
    {
        return $this->hasMany(RefereeEnrollment::class);
    }

    public function teamOfficialEnrollments(): HasMany
    {
        return $this->hasMany(TeamOfficialEnrollment::class);
    }

    // Add this method for Activity Log configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'payment_status',
                'document_id',
                'total_price',
                'activated_at', // Log activation changes
            ])
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't create log entries if nothing changed
            ->useLogName('enrollment') // Custom log name
            ->setDescriptionForEvent(fn (string $eventName) => "Enrollment has been {$eventName}"); // Simple description
    }
}
