<?php

namespace Domain\Insurance\Models;

use App\Enums\InsurancePlansTypeEnum;
use Database\Factories\Domain\Insurance\Models\InsurancePlanFactory;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Enums\VatRate;
use Domain\Memberships\Models\MembershipPackage;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InsurancePlan extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description', // Description of the plan
        'insured_activity', // Activity covered by the insurance
        'territorial_scope', // Territorial scope of the insurance
        'cmas_license_code', // CMAS license code if applicable
        'target_audience',
        'type',
        'individual_fee',
        'entity_fee',
        'moloni_reference',
        'policy_number',
        'policy_number_prefix',
        'policy_number_sequence',
        'policy_number_format',
        'start_date',
        'end_date',
        'period',
        'period_unit',
        'vat_rate',
        'requires_official_document',
        'required_document_type',
        'requires_active_affiliation',
        'insurer_address',
        'insurer_email',
        'insurer_phone',
        'applicable_deductibles',
        'coverage_details',
        'insurance_company_name',
    ];

    protected $casts = [
        'individual_fee' => 'float',
        'entity_fee' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'period' => 'integer',
        'vat_rate' => 'integer',
        'requires_official_document' => 'boolean',
        'requires_active_affiliation' => 'boolean',
        'type' => InsurancePlansTypeEnum::class,
    ];

    protected static function newFactory(): InsurancePlanFactory
    {
        return InsurancePlanFactory::new();
    }

    public function getDurationString(): string
    {
        return "{$this->period} " . trans_choice($this->period_unit, $this->period);
    }

    /**
     * Check if this plan has fixed dates (start_date and end_date defined)
     */
    public function hasFixedDates(): bool
    {
        return $this->start_date !== null && $this->end_date !== null;
    }

    /**
     * Calculate the end date for an insurance.
     * If the plan has fixed dates, returns the plan's end_date.
     * Otherwise, calculates based on period from the given start date.
     */
    public function calculateEndDate(Carbon $startDate): \Carbon\Carbon
    {
        // If plan has fixed dates, use the plan's end_date
        if ($this->hasFixedDates()) {
            return $this->end_date->copy();
        }

        // Otherwise, calculate from the given start date using period
        $period = $this->period ?? 1;
        $unit = $this->period_unit ?? 'year';

        return $startDate->copy()->add($period, $unit);
    }

    /**
     * Get the start date for an insurance.
     * If the plan has fixed dates, returns the plan's start_date.
     * Otherwise, returns the given start date (typically from subscription).
     */
    public function getInsuranceStartDate(Carbon|\Carbon\Carbon $subscriptionStartDate): \Carbon\Carbon
    {
        if ($this->hasFixedDates()) {
            return $this->start_date->copy();
        }

        return Carbon::parse($subscriptionStartDate);
    }

    public function isGroupPlan(): bool
    {
        return ! empty($this->policy_number);
    }

    public function hasSequentialPolicyNumbers(): bool
    {
        return ! empty($this->policy_number_prefix) && ! $this->isGroupPlan();
    }

    public function generateNextPolicyNumber(): string
    {
        if ($this->isGroupPlan()) {
            return $this->policy_number;
        }

        if (! $this->hasSequentialPolicyNumbers()) {
            throw new \Exception('Insurance plan does not have sequential policy number configuration');
        }

        // Increment sequence number
        $this->increment('policy_number_sequence');

        // Generate policy number based on format
        $format = $this->policy_number_format ?? '{prefix}-{sequence}';
        $sequence = str_pad((string) $this->policy_number_sequence, 6, '0', STR_PAD_LEFT);

        return str_replace(['{prefix}', '{sequence}'], [$this->policy_number_prefix, $sequence], $format);
    }

    public function membershipPackages(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPackage::class, 'package_insurance', 'insurance_id', 'package_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('insurance_attachments')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('attachments');
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

    public function scopeAvailableForEntity($query, $entity)
    {
        return $query->whereHas('membershipPackages', function ($query) use ($entity) {
            $query->whereHas('federations', function ($q) use ($entity) {
                $q->whereIn('federation_id', $entity->federations->pluck('id'));
            });
        })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
            });
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

    /**
     * Check if an individual has the required official document for this insurance plan
     */
    public function individualHasRequiredDocument(Individual $individual): bool
    {
        if (! $this->requires_official_document) {
            return true;
        }

        return $individual->officialDocuments()
            ->where('type', $this->required_document_type)
            ->where('status_class', ActiveOfficialDocumentState::class)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', Carbon::now());
            })
            ->exists();
    }

    /**
     * Get the insurance document types that could be required
     */
    public static function getInsuranceDocumentTypes(): array
    {
        return [
            'ProfessionalLiabilityInsurance',
            'InsuranceAthlete',
            'DivingProfessionalInsurance',
        ];
    }
}
