<?php

use App\Enums\MembershipTargetType;
use Domain\Federations\Models\Federation;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create federations for testing
    $this->federation = Federation::factory()->create();
    $this->otherFederation = Federation::factory()->create();

    // Create affiliation and insurance plans
    $this->affiliationPlan = AffiliationPlan::create([
        'federation_id' => $this->federation->id,
        'name' => 'Test Affiliation Plan',
        'description' => 'Test affiliation plan',
        'duration_months' => 12,
        'individual_fee' => 50.00,
        'entity_fee' => 100.00,
        'type' => 'standard',
    ]);

    $this->otherAffiliationPlan = AffiliationPlan::create([
        'federation_id' => $this->otherFederation->id,
        'name' => 'Other Affiliation Plan',
        'description' => 'Other affiliation plan',
        'duration_months' => 12,
        'individual_fee' => 60.00,
        'entity_fee' => 120.00,
        'type' => 'standard',
    ]);

    $this->insurancePlan = InsurancePlan::create([
        'name' => 'Test Insurance Plan',
        'description' => 'Test insurance plan',
        'individual_fee' => 25.00,
        'entity_fee' => 50.00,
        'target_audience' => 'general',
        'type' => 'liability',
    ]);

    $this->otherInsurancePlan = InsurancePlan::create([
        'name' => 'Other Insurance Plan',
        'description' => 'Other insurance plan',
        'individual_fee' => 30.00,
        'entity_fee' => 60.00,
        'target_audience' => 'general',
        'type' => 'liability',
    ]);

    // Create various types of membership packages
    createTestPackages();
});

function createTestPackages()
{
    // Individual membership package with affiliation plans (should appear in membership filtering)
    test()->individualMembershipPackage = MembershipPackage::create([
        'name' => 'Individual Membership Package',
        'description' => 'Package with affiliation plans for individuals',
        'is_active' => true,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['direct'],
    ]);
    test()->individualMembershipPackage->affiliationPlans()->attach(test()->affiliationPlan);
    test()->individualMembershipPackage->insurancePlans()->attach(test()->insurancePlan);
    test()->individualMembershipPackage->federations()->attach(test()->federation);

    // Individual insurance-only package (should appear in insurance filtering)
    test()->individualInsurancePackage = MembershipPackage::create([
        'name' => 'Individual Insurance Package',
        'description' => 'Insurance-only package for individuals',
        'is_active' => true,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['direct'],
    ]);
    test()->individualInsurancePackage->insurancePlans()->attach(test()->insurancePlan);
    test()->individualInsurancePackage->federations()->attach(test()->federation);

    // Entity membership package with affiliation plans (should appear in entity membership filtering)
    test()->entityMembershipPackage = MembershipPackage::create([
        'name' => 'Entity Membership Package',
        'description' => 'Package with affiliation plans for entities',
        'is_active' => true,
        'target_type' => MembershipTargetType::ENTITY,
        'distribution_methods' => ['direct'],
    ]);
    test()->entityMembershipPackage->affiliationPlans()->attach(test()->affiliationPlan);
    test()->entityMembershipPackage->insurancePlans()->attach(test()->insurancePlan);
    test()->entityMembershipPackage->federations()->attach(test()->federation);

    // Entity insurance-only package (should appear in entity insurance filtering)
    test()->entityInsurancePackage = MembershipPackage::create([
        'name' => 'Entity Insurance Package',
        'description' => 'Insurance-only package for entities',
        'is_active' => true,
        'target_type' => MembershipTargetType::ENTITY,
        'distribution_methods' => ['direct'],
    ]);
    test()->entityInsurancePackage->insurancePlans()->attach(test()->insurancePlan);
    test()->entityInsurancePackage->federations()->attach(test()->federation);

    // BOTH target type membership package with affiliation plans
    test()->bothMembershipPackage = MembershipPackage::create([
        'name' => 'Both Membership Package',
        'description' => 'Package with affiliation plans for both individuals and entities',
        'is_active' => true,
        'target_type' => MembershipTargetType::BOTH,
        'distribution_methods' => ['direct'],
    ]);
    test()->bothMembershipPackage->affiliationPlans()->attach(test()->affiliationPlan);
    test()->bothMembershipPackage->insurancePlans()->attach(test()->insurancePlan);
    test()->bothMembershipPackage->federations()->attach(test()->federation);

    // BOTH target type insurance-only package
    test()->bothInsurancePackage = MembershipPackage::create([
        'name' => 'Both Insurance Package',
        'description' => 'Insurance-only package for both individuals and entities',
        'is_active' => true,
        'target_type' => MembershipTargetType::BOTH,
        'distribution_methods' => ['direct'],
    ]);
    test()->bothInsurancePackage->insurancePlans()->attach(test()->insurancePlan);
    test()->bothInsurancePackage->federations()->attach(test()->federation);

    // Inactive package (should not appear in any filtering)
    test()->inactivePackage = MembershipPackage::create([
        'name' => 'Inactive Package',
        'description' => 'Inactive package for testing',
        'is_active' => false,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['direct'],
    ]);
    test()->inactivePackage->affiliationPlans()->attach(test()->affiliationPlan);
    test()->inactivePackage->federations()->attach(test()->federation);

    // Package from other federation (should not appear in filtering)
    test()->otherFederationPackage = MembershipPackage::create([
        'name' => 'Other Federation Package',
        'description' => 'Package from other federation',
        'is_active' => true,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['direct'],
    ]);
    test()->otherFederationPackage->affiliationPlans()->attach(test()->otherAffiliationPlan);
    test()->otherFederationPackage->federations()->attach(test()->otherFederation);

    // Package without direct distribution method (should not appear)
    test()->nonDirectPackage = MembershipPackage::create([
        'name' => 'Non-Direct Package',
        'description' => 'Package without direct distribution',
        'is_active' => true,
        'target_type' => MembershipTargetType::INDIVIDUAL,
        'distribution_methods' => ['indirect'],
    ]);
    test()->nonDirectPackage->affiliationPlans()->attach(test()->affiliationPlan);
    test()->nonDirectPackage->federations()->attach(test()->federation);
}

describe('Membership package filtering', function () {
    it('includes only packages with affiliation plans for individual memberships', function () {
        $packages = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('affiliationPlans')
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        expect($packages->count())->toBe(1);
        expect($packages->first()->id)->toBe($this->individualMembershipPackage->id);
        expect($packages->first()->affiliationPlans)->not->toBeEmpty();
    });

    it('includes only packages with affiliation plans for entity subscriptions', function () {
        $packages = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::ENTITY)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('affiliationPlans')
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        expect($packages->count())->toBe(1);
        expect($packages->first()->id)->toBe($this->entityMembershipPackage->id);
        expect($packages->first()->affiliationPlans)->not->toBeEmpty();
    });

    it('excludes insurance-only packages from membership filtering', function () {
        $packages = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('affiliationPlans')
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeFalse();
    });

    it('excludes packages without affiliation plans from membership filtering', function () {
        // Create package with only insurance plans
        $insuranceOnlyPackage = MembershipPackage::create([
            'name' => 'Test Insurance Only Package',
            'description' => 'Package with only insurance plans',
            'is_active' => true,
            'target_type' => MembershipTargetType::INDIVIDUAL,
            'distribution_methods' => ['direct'],
        ]);
        $insuranceOnlyPackage->insurancePlans()->attach($this->insurancePlan);
        $insuranceOnlyPackage->federations()->attach($this->federation);

        $packages = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('affiliationPlans')
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($insuranceOnlyPackage->id, $packageIds))->toBeFalse();
    });
});

describe('Insurance package filtering', function () {
    it('includes only packages with insurance plans and no affiliation plans for individual insurance', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            });

        expect($packages->count())->toBe(2); // individual and both target types
        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->bothInsurancePackage->id, $packageIds))->toBeTrue();
    });

    it('includes only packages with insurance plans and no affiliation plans for entity insurance', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::ENTITY, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            });

        expect($packages->count())->toBe(2); // entity and both target types
        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($this->entityInsurancePackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->bothInsurancePackage->id, $packageIds))->toBeTrue();
    });

    it('excludes packages with affiliation plans from insurance filtering', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            });

        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->bothMembershipPackage->id, $packageIds))->toBeFalse();
    });

    it('excludes packages without insurance plans from insurance filtering', function () {
        // Create package with only affiliation plans
        $affiliationOnlyPackage = MembershipPackage::create([
            'name' => 'Test Affiliation Only Package',
            'description' => 'Package with only affiliation plans',
            'is_active' => true,
            'target_type' => MembershipTargetType::INDIVIDUAL,
            'distribution_methods' => ['direct'],
        ]);
        $affiliationOnlyPackage->affiliationPlans()->attach($this->affiliationPlan);
        $affiliationOnlyPackage->federations()->attach($this->federation);

        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            });

        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($affiliationOnlyPackage->id, $packageIds))->toBeFalse();
    });
});

describe('Federation association filtering', function () {
    it('includes only packages associated with the specific federation', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();

        // Should include packages from this federation
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->entityMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->entityInsurancePackage->id, $packageIds))->toBeTrue();

        // Should exclude packages from other federation
        expect(in_array($this->otherFederationPackage->id, $packageIds))->toBeFalse();
    });

    it('excludes packages not associated with any federation', function () {
        // Create package without federation association
        $unassociatedPackage = MembershipPackage::create([
            'name' => 'Unassociated Package',
            'description' => 'Package without federation association',
            'is_active' => true,
            'target_type' => MembershipTargetType::INDIVIDUAL,
            'distribution_methods' => ['direct'],
        ]);
        $unassociatedPackage->affiliationPlans()->attach($this->affiliationPlan);

        $packages = MembershipPackage::where('is_active', true)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($unassociatedPackage->id, $packageIds))->toBeFalse();
    });

    it('filters affiliation plans by federation ownership', function () {
        // Test that affiliation plans belong to the correct federation
        $packages = MembershipPackage::with(['affiliationPlans'])
            ->where('is_active', true)
            ->whereHas('affiliationPlans', function ($query) {
                $query->where('federation_id', $this->federation->id);
            })
            ->get();

        foreach ($packages as $package) {
            foreach ($package->affiliationPlans as $plan) {
                expect($plan->federation_id)->toBe($this->federation->id);
            }
        }
    });
});

describe('Target type filtering', function () {
    it('filters packages by individual target type', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();

        // Should include individual packages
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeTrue();

        // Should exclude entity packages
        expect(in_array($this->entityMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->entityInsurancePackage->id, $packageIds))->toBeFalse();

        // Should exclude BOTH packages when filtering specifically for INDIVIDUAL
        expect(in_array($this->bothMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->bothInsurancePackage->id, $packageIds))->toBeFalse();
    });

    it('filters packages by entity target type', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->where('target_type', MembershipTargetType::ENTITY)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();

        // Should include entity packages
        expect(in_array($this->entityMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->entityInsurancePackage->id, $packageIds))->toBeTrue();

        // Should exclude individual packages
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeFalse();

        // Should exclude BOTH packages when filtering specifically for ENTITY
        expect(in_array($this->bothMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->bothInsurancePackage->id, $packageIds))->toBeFalse();
    });

    it('includes BOTH target type packages for individual filtering', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();

        // Should include individual and BOTH packages
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->bothMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->bothInsurancePackage->id, $packageIds))->toBeTrue();

        // Should exclude entity-only packages
        expect(in_array($this->entityMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->entityInsurancePackage->id, $packageIds))->toBeFalse();
    });

    it('includes BOTH target type packages for entity filtering', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::ENTITY, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();

        // Should include entity and BOTH packages
        expect(in_array($this->entityMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->entityInsurancePackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->bothMembershipPackage->id, $packageIds))->toBeTrue();
        expect(in_array($this->bothInsurancePackage->id, $packageIds))->toBeTrue();

        // Should exclude individual-only packages
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeFalse();
        expect(in_array($this->individualInsurancePackage->id, $packageIds))->toBeFalse();
    });
});

describe('Additional filtering criteria', function () {
    it('excludes inactive packages from all filtering', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();
        expect(in_array($this->inactivePackage->id, $packageIds))->toBeFalse();
    });

    it('filters by distribution method', function () {
        $packages = MembershipPackage::where('is_active', true)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->get();

        $packageIds = $packages->pluck('id')->toArray();

        // Should include packages with direct distribution
        expect(in_array($this->individualMembershipPackage->id, $packageIds))->toBeTrue();

        // Should exclude packages without direct distribution
        expect(in_array($this->nonDirectPackage->id, $packageIds))->toBeFalse();
    });

    it('combines multiple filtering criteria correctly', function () {
        // Test complex filtering: individual insurance-only packages with direct distribution
        $packages = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            });

        expect($packages->count())->toBe(2); // individual and both insurance packages

        foreach ($packages as $package) {
            expect($package->is_active)->toBeTrue();
            expect($package->target_type)->toBeIn([MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH]);
            expect($package->distribution_methods)->toContain('direct');
            expect($package->insurancePlans)->not->toBeEmpty();
            expect($package->affiliationPlans)->toBeEmpty();
        }
    });
});
