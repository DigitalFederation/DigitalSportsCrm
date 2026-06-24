<?php

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Federation\InsuranceController;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Actions\CreateSubscriptionDocumentAction;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create(['is_local' => true]);
    $this->user = User::factory()->create([
        'group_id' => \App\Enums\UserGroupEnum::FEDERATION->value,
    ]);
    $this->user->federations()->attach($this->federation);

    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->federation, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    Affiliation::factory()->create([
        'member_type' => 'entity',
        'member_id' => $this->entity->id,
        'federation_id' => $this->federation->id,
        'status_class' => ActiveAffiliationState::class,
    ]);

    $this->membershipPackage = MembershipPackage::create([
        'name' => 'Test Entity Membership Package',
        'description' => 'Test package with affiliation plans',
        'is_active' => true,
        'target_type' => MembershipTargetType::ENTITY,
        'distribution_methods' => ['direct'],
    ]);

    $this->insuranceOnlyPackage = MembershipPackage::create([
        'name' => 'Test Entity Insurance Package',
        'description' => 'Test insurance-only package',
        'is_active' => true,
        'target_type' => MembershipTargetType::ENTITY,
        'distribution_methods' => ['direct'],
    ]);

    $this->affiliationPlan = AffiliationPlan::create([
        'federation_id' => $this->federation->id,
        'name' => 'Test Affiliation Plan',
        'description' => 'Test affiliation plan',
        'duration_months' => 12,
        'individual_fee' => 50.00,
        'entity_fee' => 100.00,
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

    $this->membershipPackage->affiliationPlans()->attach($this->affiliationPlan);
    $this->membershipPackage->insurancePlans()->attach($this->insurancePlan);
    $this->membershipPackage->federations()->attach($this->federation);

    $this->insuranceOnlyPackage->insurancePlans()->attach($this->insurancePlan);
    $this->insuranceOnlyPackage->federations()->attach($this->federation);

    Auth::login($this->user);
});

function createStoreMocks(): array
{
    $mockCreateAction = Mockery::mock(CreateMemberSubscriptionAction::class);
    $mockDocumentAction = Mockery::mock(CreateSubscriptionDocumentAction::class);
    $mockValidationService = Mockery::mock(SubscriptionValidationService::class);
    $mockValidationService->shouldReceive('validateSubscription')->andReturn(['valid' => true, 'error' => null]);

    return [$mockCreateAction, $mockDocumentAction, $mockValidationService];
}

describe('InsuranceController index method', function () {
    it('returns correct data without creation forms', function () {
        $subscription = MemberSubscription::factory()->create([
            'member_type' => 'entity',
            'member_id' => $this->entity->id,
            'membership_package_id' => $this->insuranceOnlyPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->addYear(),
        ]);

        Insurance::factory()->create([
            'member_type' => 'entity',
            'member_id' => $this->entity->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'member_subscription_id' => $subscription->id,
            'request_type' => 'federation_facilitated',
        ]);

        $controller = new InsuranceController;
        $response = $controller->index();

        expect($response)->toBeInstanceOf(\Illuminate\View\View::class)
            ->and($response->getData())->toHaveKey('insurances')
            ->and($response->getData()['insurances']->count())->toBe(1);
    });

    it('only shows insurance-only subscriptions', function () {
        MemberSubscription::factory()->create([
            'member_type' => Entity::class,
            'member_id' => $this->entity->id,
            'membership_package_id' => $this->membershipPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->addYear(),
        ]);

        $insuranceSubscription = MemberSubscription::factory()->create([
            'member_type' => Entity::class,
            'member_id' => $this->entity->id,
            'membership_package_id' => $this->insuranceOnlyPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->addYear(),
        ]);

        $insurance = Insurance::factory()->create([
            'member_type' => 'entity',
            'member_id' => $this->entity->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'member_subscription_id' => $insuranceSubscription->id,
            'request_type' => 'federation_facilitated',
        ]);

        $controller = new InsuranceController;
        $response = $controller->index();

        $insurances = $response->getData()['insurances'];
        expect($insurances->count())->toBe(1)
            ->and($insurances->first()->id)->toBe($insurance->id);
    });

    it('filters subscriptions by federation association', function () {
        $otherFederation = Federation::factory()->create();
        $otherEntity = Entity::factory()->create();
        $otherEntity->federations()->attach($otherFederation, [
            'status_class' => ActiveEntityFederationState::class,
        ]);

        $otherSubscription = MemberSubscription::factory()->create([
            'member_type' => 'entity',
            'member_id' => $otherEntity->id,
            'membership_package_id' => $this->insuranceOnlyPackage->id,
            'status_class' => ActiveMemberSubscriptionState::class,
            'end_date' => now()->addYear(),
        ]);

        Insurance::factory()->create([
            'member_type' => 'entity',
            'member_id' => $otherEntity->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'member_subscription_id' => $otherSubscription->id,
            'request_type' => 'federation_facilitated',
        ]);

        $controller = new InsuranceController;
        $response = $controller->index();

        expect($response->getData()['insurances']->count())->toBe(0);
    });
});

describe('InsuranceController create method', function () {
    it('loads properly filtered insurance-only packages', function () {
        $controller = new InsuranceController;
        $response = $controller->create();

        expect($response)->toBeInstanceOf(\Illuminate\View\View::class)
            ->and($response->getData())->toHaveKeys(['availableInsurancePackages', 'entities']);

        $availablePackages = $response->getData()['availableInsurancePackages'];
        expect($availablePackages->count())->toBe(1)
            ->and($availablePackages->first()->id)->toBe($this->insuranceOnlyPackage->id)
            ->and($availablePackages->contains('id', $this->membershipPackage->id))->toBeFalse();
    });

    it('loads entities associated with federation', function () {
        $controller = new InsuranceController;
        $response = $controller->create();

        $entities = $response->getData()['entities'];
        expect($entities->count())->toBe(1)
            ->and($entities->first()->id)->toBe($this->entity->id);
    });

    it('excludes inactive packages', function () {
        $this->insuranceOnlyPackage->update(['is_active' => false]);

        $controller = new InsuranceController;
        $response = $controller->create();

        expect($response->getData()['availableInsurancePackages']->count())->toBe(0);
    });
});

describe('InsuranceController store method', function () {
    it('validates package is insurance-only', function () {
        $request = Request::create('/test', 'POST', [
            'membership_package_id' => $this->membershipPackage->id,
            'entity_id' => $this->entity->id,
        ]);

        [$mockCreateAction, $mockDocumentAction, $mockValidationService] = createStoreMocks();

        $controller = new InsuranceController;
        $response = $controller->store($request, $mockCreateAction, $mockDocumentAction, $mockValidationService);

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
            ->and($response->getSession()->has('error'))->toBeTrue();
    });

    it('validates entity belongs to federation', function () {
        $otherEntity = Entity::factory()->create();

        $request = Request::create('/test', 'POST', [
            'membership_package_id' => $this->insuranceOnlyPackage->id,
            'entity_id' => $otherEntity->id,
        ]);

        [$mockCreateAction, $mockDocumentAction, $mockValidationService] = createStoreMocks();

        $controller = new InsuranceController;
        $response = $controller->store($request, $mockCreateAction, $mockDocumentAction, $mockValidationService);

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
            ->and($response->getSession()->has('error'))->toBeTrue();
    });
});

describe('InsuranceController package filtering', function () {
    it('filters packages to include only insurance-only packages', function () {
        $controller = new InsuranceController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getAvailableInsurancePackages');
        $method->setAccessible(true);

        $packages = $method->invoke($controller, $this->federation);

        expect($packages->count())->toBe(1)
            ->and($packages->first()->id)->toBe($this->insuranceOnlyPackage->id)
            ->and($packages->first()->insurancePlans)->not->toBeEmpty()
            ->and($packages->first()->affiliationPlans)->toBeEmpty();
    });

    it('excludes packages with affiliation plans', function () {
        $controller = new InsuranceController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getAvailableInsurancePackages');
        $method->setAccessible(true);

        $packages = $method->invoke($controller, $this->federation);

        expect($packages->pluck('id'))->not->toContain($this->membershipPackage->id);
    });

    it('filters by federation association', function () {
        $otherFederation = Federation::factory()->create();
        $otherPackage = MembershipPackage::factory()->create([
            'is_active' => true,
            'target_type' => MembershipTargetType::ENTITY,
            'distribution_methods' => ['direct'],
        ]);
        $otherInsurancePlan = InsurancePlan::factory()->create();
        $otherPackage->insurancePlans()->attach($otherInsurancePlan);
        $otherPackage->federations()->attach($otherFederation);

        $controller = new InsuranceController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getAvailableInsurancePackages');
        $method->setAccessible(true);

        $packages = $method->invoke($controller, $this->federation);

        expect($packages->pluck('id'))->not->toContain($otherPackage->id);
    });

    it('includes packages with target type BOTH', function () {
        $bothPackage = MembershipPackage::factory()->create([
            'is_active' => true,
            'target_type' => MembershipTargetType::BOTH,
            'distribution_methods' => ['direct'],
        ]);
        $bothPackage->insurancePlans()->attach($this->insurancePlan);
        $bothPackage->federations()->attach($this->federation);

        $controller = new InsuranceController;
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getAvailableInsurancePackages');
        $method->setAccessible(true);

        $packages = $method->invoke($controller, $this->federation);

        expect($packages->pluck('id'))->toContain($bothPackage->id);
    });
});
