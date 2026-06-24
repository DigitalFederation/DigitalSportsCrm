<?php

namespace Domain\Memberships\Models;

use App\Enums\MembershipTargetType;
use Database\Factories\Domain\Memberships\Models\MembershipPackageFactory;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\InsurancePlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property float|int|string|null $price
 * @property float|null $calculated_price
 */
class MembershipPackage extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'target_type',
        'distribution_methods',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_type' => MembershipTargetType::class,
        'distribution_methods' => 'array',
    ];

    protected static function newFactory(): MembershipPackageFactory
    {
        return MembershipPackageFactory::new();
    }

    public function federations(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'package_federation', 'package_id', 'federation_id');
    }

    public function affiliationPlans(): BelongsToMany
    {
        return $this->belongsToMany(AffiliationPlan::class, 'package_affiliation', 'package_id', 'affiliation_id');
    }

    public function insurancePlans(): BelongsToMany
    {
        return $this->belongsToMany(InsurancePlan::class, 'package_insurance', 'package_id', 'insurance_id');
    }

    public function memberSubscriptions(): HasMany
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function calculatePrice(): float
    {
        $this->load('affiliationPlans', 'insurancePlans');

        $isIndividual = $this->target_type === MembershipTargetType::INDIVIDUAL;

        $affiliationPrice = $this->calculateAffiliationPrice($isIndividual);
        $insurancePrice = $this->calculateInsurancePrice($isIndividual);

        return (float) ($affiliationPrice + $insurancePrice);
    }

    private function calculateAffiliationPrice(bool $isIndividual): float
    {
        return $this->affiliationPlans
            ->sum(function ($plan) use ($isIndividual) {
                $fee = $isIndividual ? $plan->individual_fee : $plan->entity_fee;

                return $fee ?? 0; // Return 0 if fee is null
            });
    }

    private function calculateInsurancePrice(bool $isIndividual): float
    {
        return $this->insurancePlans
            ->sum(function ($plan) use ($isIndividual) {
                $fee = $isIndividual ? $plan->individual_fee : $plan->entity_fee;

                return $fee ?? 0; // Return 0 if fee is null
            });
    }

    // For more flexible price calculations
    public function calculatePriceForType(string $type): float
    {
        if (! in_array($type, ['individual', 'entity'])) {
            throw new \InvalidArgumentException('Type must be either "individual" or "entity"');
        }

        $this->load('affiliationPlans', 'insurancePlans');

        $affiliationPrice = $this->affiliationPlans
            ->sum(fn ($plan) => $plan->{$type . '_fee'} ?? 0);

        $insurancePrice = $this->insurancePlans
            ->sum(fn ($plan) => $plan->{$type . '_fee'} ?? 0);

        return (float) ($affiliationPrice + $insurancePrice);
    }

    public function calculatePriceFor(string $memberType): float
    {
        $type = $memberType === 'Domain\Entities\Models\Entity' ? 'entity' : 'individual';

        return $this->calculatePriceForType($type);
    }

    /**
     * Check if this package is available for direct subscription
     */
    public function isAvailableDirectly(): bool
    {
        return in_array('direct', $this->distribution_methods ?? []);
    }

    /**
     * Check if this package can be managed by entities for their members
     */
    public function isEntityManaged(): bool
    {
        return in_array('entity_managed', $this->distribution_methods ?? []);
    }

    /**
     * Check if this package is for individuals
     */
    public function isForIndividuals(): bool
    {
        return $this->target_type === MembershipTargetType::INDIVIDUAL;
    }

    /**
     * Check if this package is for entities
     */
    public function isForEntities(): bool
    {
        return $this->target_type === MembershipTargetType::ENTITY;
    }

    /**
     * Check if an individual meets all the official document requirements for this package
     */
    public function individualMeetsDocumentRequirements(Individual $individual): bool
    {
        // Only check for individual packages
        if (! $this->isForIndividuals()) {
            return true;
        }

        // Check all insurance plans in this package
        foreach ($this->insurancePlans as $insurancePlan) {
            if (! $insurancePlan->individualHasRequiredDocument($individual)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing document requirements for an individual
     */
    public function getMissingDocumentRequirements(Individual $individual): array
    {
        $missing = [];

        // Only check for individual packages
        if (! $this->isForIndividuals()) {
            return $missing;
        }

        foreach ($this->insurancePlans as $insurancePlan) {
            if ($insurancePlan->requires_official_document && ! $insurancePlan->individualHasRequiredDocument($individual)) {
                $missing[] = [
                    'insurance_plan' => $insurancePlan->name,
                    'required_document_type' => $insurancePlan->required_document_type,
                ];
            }
        }

        return $missing;
    }

    /**
     * Check if this package has any insurance plans requiring official documents
     */
    public function hasDocumentRequirements(): bool
    {
        return $this->insurancePlans()
            ->where('requires_official_document', true)
            ->exists();
    }
}
