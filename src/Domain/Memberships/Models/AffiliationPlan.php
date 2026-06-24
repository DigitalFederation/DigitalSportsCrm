<?php

namespace Domain\Memberships\Models;

use Database\Factories\Domain\Memberships\Models\AffiliationPlanFactory;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Enums\VatRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AffiliationPlan extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'federation_id',
        'name',
        'description',
        'duration_months',
        'individual_fee',
        'entity_fee',
        'moloni_reference',
        'type',
        'start_date',
        'end_date',
        'vat_rate',
        'is_validation_plan',
    ];

    protected $casts = [
        'individual_fee' => 'decimal:2',
        'entity_fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'vat_rate' => 'integer',
        'is_validation_plan' => 'boolean',
    ];

    protected static function newFactory(): AffiliationPlanFactory
    {
        return AffiliationPlanFactory::new();
    }

    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    public function membershipPackages(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPackage::class, 'package_affiliation', 'affiliation_id', 'package_id');
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('affiliation_attachments')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('attachments');
    }

    public function isForEntity(): bool
    {
        return $this->type === 'entity';
    }

    public function isForIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isActive(): bool
    {
        $now = Carbon::now();

        return ($this->start_date === null || $now->gte($this->start_date)) &&
            ($this->end_date === null || $now->lte($this->end_date));
    }

    public function getDurationInDays(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date);
    }

    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now());
    }

    public function getVatRateEnum(): VatRate
    {
        return VatRate::tryFrom($this->vat_rate) ?? VatRate::default();
    }

    public function getVatRateLabel(): string
    {
        return $this->getVatRateEnum()->label();
    }

    public function getVatRatePercentage(): int
    {
        return $this->vat_rate ?? VatRate::default()->value;
    }

    public function isValidationPlan(): bool
    {
        return $this->is_validation_plan === true;
    }

    public function allowsInsuranceRequests(): bool
    {
        return $this->isValidationPlan();
    }

    public function allowsLicenseRequests(): bool
    {
        return $this->isValidationPlan();
    }

    public function allowsEntityMemberLicenseRequests(): bool
    {
        return $this->isValidationPlan() && $this->isForEntity();
    }

    public function allowsEntityMemberSubscriptions(): bool
    {
        return $this->isValidationPlan() && $this->isForEntity();
    }
}
