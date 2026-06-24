<?php

namespace Domain\Memberships\Models;

use Domain\Insurance\Models\Insurance;
use Domain\Licenses\Models\License;
use Domain\Memberships\States\MemberSubscriptionState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Support\Traits\HasDocumentPaymentStatus;

/**
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property int $id
 * @property int|string|null $member_id
 * @property string|null $member_type
 * @property class-string<MemberSubscriptionState>|null $status_class
 * @property \Domain\Individuals\Models\Individual|\Domain\Entities\Models\Entity|null $member
 * @property MembershipPackage|null $membershipPackage
 * @property \Illuminate\Database\Eloquent\Collection<int, Affiliation> $affiliations
 * @property \Illuminate\Database\Eloquent\Collection<int, \Domain\Insurance\Models\Insurance> $insurances
 * @property \Illuminate\Database\Eloquent\Collection<int, \Domain\Documents\Models\Document> $documents
 * @property \Domain\Documents\Models\Document|null $pendingDocument
 */
class MemberSubscription extends Model
{
    use HasDocumentPaymentStatus;
    use HasFactory;
    use LogsActivity;

    public function simpleTest()
    {
        return 'Class loaded successfully at ' . now();
    }

    protected static function newFactory()
    {
        return \Database\Factories\Domain\Memberships\Models\MemberSubscriptionFactory::new();
    }
    protected $fillable = [
        'membership_package_id',
        'member_type',
        'member_id',
        'start_date',
        'end_date',
        'status_class',
        'requester_type',
        'requester_id',
        'request_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['membership_package_id', 'member_type', 'member_id', 'start_date', 'end_date', 'status_class'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'subscription_license')->withPivot('start_date', 'end_date', 'status');
    }

    public function membershipPackage(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class);
    }

    public function member(): MorphTo
    {
        return $this->morphTo('member');
    }

    public function affiliations(): HasMany
    {
        return $this->hasMany(Affiliation::class);
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(Insurance::class);
    }

    public function requester(): MorphTo
    {
        return $this->morphTo('requester', 'requester_type', 'requester_id');
    }

    public function getStateAttribute(): MemberSubscriptionState
    {
        return new $this->status_class($this);
    }

    public function isActive(): bool
    {
        return $this->state->isActive();
    }
    public function calculateTotalPrice(): float
    {
        $packagePrice = $this->membershipPackage->calculatePrice();
        $durationInDays = $this->start_date->diffInDays($this->end_date);
        $durationInYears = $durationInDays / 365.25; // Account for leap years

        return $packagePrice * $durationInYears;
    }

    /**
     * Alias for documentsViaDetails for backward compatibility.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Domain\Documents\Models\Document>
     */
    public function documents()
    {
        return $this->documentsViaDetails();
    }

    public function isActiveAndPaid(): bool
    {
        return $this->isActive() && $this->hasPaidDocument();
    }

    /**
     * Alias for documentsViaDetails for backward compatibility.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\Domain\Documents\Models\Document>
     */
    public function relatedDocuments()
    {
        return $this->documentsViaDetails();
    }

    /**
     * Alias for hasUnpaidDocument for backward compatibility.
     */
    public function hasUnpaidRelatedDocument(): bool
    {
        return $this->hasUnpaidDocument();
    }

    /**
     * Alias for hasPaidDocument for backward compatibility.
     */
    public function hasPaidRelatedDocument(): bool
    {
        return $this->hasPaidDocument();
    }

    /**
     * Calculate the end date for an annual subscription.
     * Annual subscriptions end on December 31st of the current year.
     *
     * @param  Carbon|null  $startDate  The start date (defaults to now)
     * @return string The end date in Y-m-d format
     */
    public static function calculateAnnualEndDate(?Carbon $startDate = null): string
    {
        $start = $startDate ?? Carbon::now();

        return $start->copy()->endOfYear()->format('Y-m-d');
    }
}
