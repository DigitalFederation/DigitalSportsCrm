<?php

namespace Domain\Memberships\Models;

use Domain\Documents\States\PaidDocumentState;
use Domain\Federations\Models\Federation;
use Domain\Memberships\States\AffiliationState;
use Domain\Memberships\States\InactiveAffiliationState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Affiliation extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\Domain\Memberships\Models\AffiliationFactory::new();
    }

    protected $fillable = [
        'federation_id',
        'member_subscription_id',
        'member_type',
        'member_id',
        'start_date',
        'end_date',
        'individual_fee',
        'entity_fee',
        'status_class',
        'requester_type',
        'requester_id',
        'request_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fee' => 'decimal:2',
        'individual_fee' => 'decimal:2',
        'entity_fee' => 'decimal:2',
    ];

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
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

    /**
     * Get the affiliation plan that matches this affiliation's federation
     * from the subscription's membership package
     */
    public function getAffiliationPlanAttribute()
    {
        return $this->memberSubscription
            ->membershipPackage
            ->affiliationPlans
            ->where('federation_id', $this->federation_id)
            ->first();
    }

    public function getStateAttribute(): AffiliationState
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

    public function isActiveAndPaid(): bool
    {
        return $this->isActive()
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    protected static function booted()
    {
        static::creating(function ($affiliation) {
            if (empty($affiliation->status_class)) {
                $affiliation->status_class = InactiveAffiliationState::class;
            }
        });
    }

    // Legacy method for backward compatibility - now uses state classes
    public function getStatusAttribute(): string
    {
        return $this->state->name();
    }

    /**
     * Get the activation date from the paid document's transaction
     */
    public function getActivationDateAttribute(): ?\Carbon\Carbon
    {
        $subscription = $this->memberSubscription;

        if (! $subscription) {
            return null;
        }

        $paidDocument = $subscription->documents()
            ->where('status_class', PaidDocumentState::class)
            ->with('transactions')
            ->first();

        if (! $paidDocument || $paidDocument->transactions->isEmpty()) {
            return null;
        }

        return $paidDocument->transactions->sortByDesc('created_at')->first()->created_at;
    }
}
