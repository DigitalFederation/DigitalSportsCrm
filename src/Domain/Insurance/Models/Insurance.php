<?php

namespace Domain\Insurance\Models;

use Database\Factories\Domain\Insurance\Models\InsuranceFactory;
use Domain\Insurance\States\InactiveInsuranceState;
use Domain\Insurance\States\InsuranceState;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property InsurancePlan $insurancePlan
 * @property \Domain\Individuals\Models\Individual|\Domain\Entities\Models\Entity|null $member
 */
class Insurance extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'insurance_plan_id',
        'member_type',
        'member_id',
        'start_date',
        'end_date',
        'individual_fee',
        'entity_fee',
        'policy_number',
        'is_external',
        'status_class',
        'member_subscription_id',
        'requester_type',
        'requester_id',
        'request_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'individual_fee' => 'decimal:2',
        'entity_fee' => 'decimal:2',
        'is_external' => 'boolean',
    ];

    protected static function newFactory(): InsuranceFactory
    {
        return InsuranceFactory::new();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['insurance_plan_id', 'member_type', 'member_id', 'start_date', 'end_date', 'policy_number', 'individual_fee', 'entity_fee', 'is_external', 'status_class', 'requester_type', 'requester_id', 'request_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function needsPolicyNumber(): bool
    {
        return empty($this->policy_number) && ! $this->insurancePlan->isGroupPlan();
    }

    public function insurancePlan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class);
    }

    public function member(): MorphTo
    {
        return $this->morphTo();
    }

    public function memberSubscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class);
    }

    public function requester(): MorphTo
    {
        return $this->morphTo('requester', 'requester_type', 'requester_id');
    }

    // Legacy method for backward compatibility
    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requester_id')
            ->where('requester_type', \App\Models\User::class);
    }

    public function getStateAttribute(): InsuranceState
    {
        return new $this->status_class($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive();
    }

    public function isExpired(): bool
    {
        return $this->end_date < now();
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function isActiveAndPaid(): bool
    {
        return $this->isActive()
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    /**
     * Determine if the insurance document can be downloaded.
     * Documents are only available for active insurances.
     */
    public function canDownloadDocument(): bool
    {
        return $this->isActive();
    }

    protected static function booted()
    {
        static::creating(function ($insurance) {
            if (empty($insurance->status_class)) {
                $insurance->status_class = InactiveInsuranceState::class;
            }
        });
    }

    // Legacy method for backward compatibility - now uses state classes
    public function getStatusAttribute(): string
    {
        return $this->state->name();
    }
}
