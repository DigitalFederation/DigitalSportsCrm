<?php

use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Actions\FilterInsuranceOnlyPackagesAction;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Illuminate\Support\Collection;

test('it filters out packages with only insurance items', function () {
    // Create mock packages
    $packageWithOnlyInsurance = new MembershipPackage;
    $packageWithOnlyInsurance->id = 1;
    $packageWithOnlyInsurance->setRelation('affiliationPlans', collect());
    $packageWithOnlyInsurance->setRelation('insurancePlans', collect([new InsurancePlan]));

    $packageWithOnlyAffiliation = new MembershipPackage;
    $packageWithOnlyAffiliation->id = 2;
    $packageWithOnlyAffiliation->setRelation('affiliationPlans', collect([new AffiliationPlan]));
    $packageWithOnlyAffiliation->setRelation('insurancePlans', collect());

    $packageWithBoth = new MembershipPackage;
    $packageWithBoth->id = 3;
    $packageWithBoth->setRelation('affiliationPlans', collect([new AffiliationPlan]));
    $packageWithBoth->setRelation('insurancePlans', collect([new InsurancePlan]));

    // Create collection of packages
    $packages = Collection::make([
        $packageWithOnlyInsurance,
        $packageWithOnlyAffiliation,
        $packageWithBoth,
    ]);

    // Execute action
    $action = new FilterInsuranceOnlyPackagesAction;
    $filtered = $action->execute($packages);

    // Assert results
    expect($filtered)->toHaveCount(2);
    expect($filtered->contains($packageWithOnlyInsurance))->toBeFalse();
    expect($filtered->contains($packageWithOnlyAffiliation))->toBeTrue();
    expect($filtered->contains($packageWithBoth))->toBeTrue();
});

test('it excludes packages with no affiliation plans', function () {
    $packageWithNoPlans = new MembershipPackage;
    $packageWithNoPlans->id = 1;
    $packageWithNoPlans->setRelation('affiliationPlans', collect());
    $packageWithNoPlans->setRelation('insurancePlans', collect());

    $packages = Collection::make([$packageWithNoPlans]);

    $action = new FilterInsuranceOnlyPackagesAction;
    $filtered = $action->execute($packages);

    expect($filtered)->toBeEmpty();
});

test('it excludes packages with exactly one item that is insurance', function () {
    $package = new MembershipPackage;
    $package->id = 1;
    $package->setRelation('affiliationPlans', collect());
    $package->setRelation('insurancePlans', collect([new InsurancePlan]));

    $packages = Collection::make([$package]);

    $action = new FilterInsuranceOnlyPackagesAction;
    $filtered = $action->execute($packages);

    expect($filtered)->toBeEmpty();
});

test('it includes packages with multiple items even if one is insurance', function () {
    $package = new MembershipPackage;
    $package->id = 1;
    $package->setRelation('affiliationPlans', collect([new AffiliationPlan, new AffiliationPlan]));
    $package->setRelation('insurancePlans', collect([new InsurancePlan]));

    $packages = Collection::make([$package]);

    $action = new FilterInsuranceOnlyPackagesAction;
    $filtered = $action->execute($packages);

    expect($filtered)->toHaveCount(1);
    expect($filtered->contains($package))->toBeTrue();
});
