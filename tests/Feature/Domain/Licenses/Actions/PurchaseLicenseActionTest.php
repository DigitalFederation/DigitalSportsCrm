<?php

use App\Models\Committee;
use App\Models\Sport;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Actions\ActivateLicenseAttributedAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create base test data
    $this->user = User::factory()->create();
    $this->federation = Federation::factory()->create(['is_default_federation' => true]);
    $this->committee = Committee::factory()->create(['code' => 'SPORT', 'is_international' => false]);
    $this->divingCommittee = Committee::factory()->create(['code' => 'DIVING', 'is_international' => true]);
    $this->divingServicesCommittee = Committee::factory()->create(['code' => 'DIVINGSERVICES', 'is_international' => false]);
    $this->scientificCommittee = Committee::factory()->create(['code' => 'SCIENTIFIC', 'is_international' => true]);
    $this->sport = Sport::factory()->create();
    $this->professionalRole = ProfessionalRole::factory()->create(['role' => 'ATHLETE']);
    $this->licenseType = LicenseType::factory()->create(['is_individual' => true]);

    // Create membership package with validation plan for license privileges
    $this->membershipPackage = MembershipPackage::factory()->create([
        'name' => 'Test Package',
        'target_type' => 'both',
        'is_active' => true,
    ]);

    $this->affiliationPlan = AffiliationPlan::factory()->create([
        'federation_id' => $this->federation->id,
        'is_validation_plan' => true,
        'type' => 'both',
    ]);

    $this->membershipPackage->affiliationPlans()->attach($this->affiliationPlan);
});

/**
 * Helper to create an individual with active affiliation
 */
function createIndividualWithAffiliation(
    Federation $federation,
    MembershipPackage $package,
    AffiliationPlan $plan
): Individual {
    $individual = Individual::factory()->create();

    // Attach to federation with active status
    $individual->federations()->attach($federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => 1,
    ]);

    // Create active subscription
    $subscription = MemberSubscription::factory()->create([
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'membership_package_id' => $package->id,
        'status_class' => ActiveMemberSubscriptionState::class,
    ]);

    // Create active affiliation
    Affiliation::factory()->create([
        'federation_id' => $federation->id,
        'member_subscription_id' => $subscription->id,
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'status_class' => ActiveAffiliationState::class,
    ]);

    return $individual->fresh();
}

/**
 * Helper to create an entity with active affiliation
 */
function createEntityWithAffiliation(
    Federation $federation,
    MembershipPackage $package,
    AffiliationPlan $plan
): Entity {
    $entity = Entity::factory()->create();

    // Attach to federation with active status
    $entity->federations()->attach($federation->id, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => 1,
    ]);

    // Create active subscription
    $subscription = MemberSubscription::factory()->create([
        'member_type' => 'entity',
        'member_id' => $entity->id,
        'membership_package_id' => $package->id,
        'status_class' => ActiveMemberSubscriptionState::class,
    ]);

    // Create active affiliation
    Affiliation::factory()->create([
        'federation_id' => $federation->id,
        'member_subscription_id' => $subscription->id,
        'member_type' => 'entity',
        'member_id' => $entity->id,
        'status_class' => ActiveAffiliationState::class,
    ]);

    return $entity->fresh();
}

describe('PurchaseLicenseAction', function () {
    describe('Successful purchases', function () {
        test('individual can purchase license with requester_model Individual', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'professional_role_id' => $this->professionalRole->id,
                'name' => 'Individual License',
                'license_code' => 'IND-001',
                'requester_model' => ['Individual'],
                'unit_value_individual' => 100,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('individual')
                ->and($licenseAttributed->model_id)->toBe($individual->id)
                ->and((float) $licenseAttributed->total_value)->toBe(100.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);
        });

        test('entity can purchase license with requester_model Entity', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'professional_role_id' => $this->professionalRole->id,
                'name' => 'Entity License',
                'license_code' => 'ENT-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 200,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $entity);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('entity')
                ->and($licenseAttributed->model_id)->toBe((string) $entity->id)
                ->and((float) $licenseAttributed->total_value)->toBe(200.0);
        });

        test('individual can purchase license with empty requester_model (All)', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Universal License',
                'license_code' => 'ALL-001',
                'requester_model' => [], // Empty means all
                'unit_value_individual' => 150,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed)->toBeInstanceOf(LicenseAttributed::class)
                ->and((float) $licenseAttributed->total_value)->toBe(150.0);
        });
    });

    describe('State determination', function () {
        test('free license goes to Active state immediately', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Free License',
                'license_code' => 'FREE-001',
                'requester_model' => [],
                'unit_value_individual' => 0, // Free
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('paid license without validation goes to Pending state', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Paid License',
                'license_code' => 'PAID-001',
                'requester_model' => [],
                'unit_value_individual' => 100,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);
        });

        test('license requiring validation goes to PendingValidation for individual', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Validation License',
                'license_code' => 'VAL-001',
                'requester_model' => [],
                'unit_value_individual' => 100,
                'requires_admin_validation' => true,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->status_class)->toBe(PendingValidationLicenseAttributedState::class);
        });

        test('diving services license requiring validation goes to TD approval for entity', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingServicesCommittee->id, // DIVINGSERVICES, non-international
                'type_id' => $this->licenseType->id,
                'name' => 'Diving Services License',
                'license_code' => 'DS-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 200,
                'requires_admin_validation' => true,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $entity);

            expect($licenseAttributed->status_class)->toBe(PendingTechnicalDirectorApprovalLicenseAttributedState::class);
        });

        test('international diving license skips TD approval even for entity', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingCommittee->id, // DIVING, international
                'type_id' => $this->licenseType->id,
                'name' => 'International Diving License',
                'license_code' => 'INTL-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 200,
                'requires_admin_validation' => true,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $entity);

            // International licenses skip validation entirely and go directly to payment pending
            expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);
        });
    });

    describe('Validation errors', function () {
        test('throws exception when purchaser cannot request this license type', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            // License only for entities
            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Entity Only License',
                'license_code' => 'ENT-ONLY',
                'requester_model' => ['Entity'], // Only entities
                'unit_value_entity' => 200,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);

            expect(fn () => $action($license, $individual))
                ->toThrow(Exception::class);
        });

        test('throws exception when purchaser has no active affiliation', function () {
            // Individual without affiliation
            $individual = Individual::factory()->create();
            $individual->federations()->attach($this->federation->id, [
                'status_class' => ActiveIndividualFederationState::class,
                'active' => 1,
            ]);

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Test License',
                'license_code' => 'TEST-001',
                'requester_model' => [],
                'unit_value_individual' => 100,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);

            expect(fn () => $action($license, $individual))
                ->toThrow(Exception::class);
        });

        test('throws exception when validation plan does not allow license requests', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Test License',
                'license_code' => 'TEST-002',
                'requester_model' => [],
                'unit_value_individual' => 100,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Mock ValidationPlanPrivilegeService to deny
            $mockService = Mockery::mock(ValidationPlanPrivilegeService::class);
            $mockService->shouldReceive('canRequestLicense')->andReturn(false);
            $mockService->shouldReceive('getValidationPlanReason')->andReturn('Plan does not allow license requests');

            app()->instance(ValidationPlanPrivilegeService::class, $mockService);

            $action = app(PurchaseLicenseAction::class);

            expect(fn () => $action($license, $individual))
                ->toThrow(Exception::class);
        });
    });

    describe('Duplicate prevention', function () {
        test('throws exception when individual already has active license of same type', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id, // SPORT, not diving
                'type_id' => $this->licenseType->id,
                'name' => 'Sport License',
                'license_code' => 'SPORT-001',
                'requester_model' => [],
                'unit_value_individual' => 0,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);

            // First purchase succeeds
            $first = $action($license, $individual);
            expect($first)->toBeInstanceOf(LicenseAttributed::class);

            // Second purchase fails
            expect(fn () => $action($license, $individual))
                ->toThrow(Exception::class);
        });

        test('throws exception when individual has pending license of same type', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Paid License',
                'license_code' => 'PAID-DUP',
                'requester_model' => [],
                'unit_value_individual' => 100, // Paid, so will be pending
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);

            // First purchase succeeds (goes to pending)
            $first = $action($license, $individual);
            expect($first->status_class)->toBe(PendingLicenseAttributedState::class);

            // Second purchase fails
            expect(fn () => $action($license, $individual))
                ->toThrow(Exception::class);
        });

        test('allows duplicate for DIVING committee licenses', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingCommittee->id, // DIVING allows duplicates
                'type_id' => $this->licenseType->id,
                'name' => 'Diving License',
                'license_code' => 'DIVE-001',
                'requester_model' => [],
                'unit_value_individual' => 0,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);

            // First purchase succeeds
            $first = $action($license, $individual);
            expect($first)->toBeInstanceOf(LicenseAttributed::class);

            // Second purchase also succeeds for DIVING
            $second = $action($license, $individual);
            expect($second)->toBeInstanceOf(LicenseAttributed::class);
            expect($second->id)->not->toBe($first->id);
        });

        test('does not allow duplicate for DIVINGSERVICES committee licenses', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingServicesCommittee->id, // DIVINGSERVICES does NOT allow duplicates
                'type_id' => $this->licenseType->id,
                'name' => 'Diving Services License',
                'license_code' => 'DS-DUP',
                'requester_model' => [],
                'unit_value_individual' => 0,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);

            // First purchase succeeds
            $first = $action($license, $individual);
            expect($first)->toBeInstanceOf(LicenseAttributed::class);

            // Second purchase fails for DIVINGSERVICES
            expect(fn () => $action($license, $individual))
                ->toThrow(Exception::class);
        });
    });

    describe('Price calculation', function () {
        test('uses individual price for individual purchaser', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Multi-price License',
                'license_code' => 'MULTI-001',
                'requester_model' => [],
                'unit_value' => 1000,
                'unit_value_individual' => 100,
                'unit_value_entity' => 200,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect((float) $licenseAttributed->total_value)->toBe(100.0);
        });

        test('uses entity price for entity purchaser', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Multi-price License',
                'license_code' => 'MULTI-002',
                'requester_model' => ['Entity'],
                'unit_value' => 1000,
                'unit_value_individual' => 100,
                'unit_value_entity' => 200,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $entity);

            expect((float) $licenseAttributed->total_value)->toBe(200.0);
        });
    });

    describe('Federation determination', function () {
        test('uses purchasers active federation', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Test License',
                'license_code' => 'FED-001',
                'requester_model' => [],
                'unit_value_individual' => 0,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->federation_id)->toBe($this->federation->id);
        });

        test('falls back to main federation if purchaser has no active federation', function () {
            $individual = Individual::factory()->create();

            // Create subscription and affiliation without federation attachment
            $subscription = MemberSubscription::factory()->create([
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'membership_package_id' => $this->membershipPackage->id,
                'status_class' => ActiveMemberSubscriptionState::class,
            ]);

            Affiliation::factory()->create([
                'federation_id' => $this->federation->id,
                'member_subscription_id' => $subscription->id,
                'member_type' => 'individual',
                'member_id' => $individual->id,
                'status_class' => ActiveAffiliationState::class,
            ]);

            // Attach to federation but NOT active
            $individual->federations()->attach($this->federation->id, [
                'status_class' => ActiveIndividualFederationState::class,
                'active' => 0, // Not active
            ]);

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Test License',
                'license_code' => 'FED-002',
                'requester_model' => [],
                'unit_value_individual' => 0,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual->fresh());

            // Should fall back to using first federation or main federation
            expect($licenseAttributed->federation_id)->not->toBeNull();
        });
    });

    describe('Validity dates', function () {
        test('sets validity dates on license creation', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Yearly License',
                'license_code' => 'YEAR-001',
                'requester_model' => [],
                'unit_value_individual' => 100,
                'interval' => 1,
                'interval_unit' => 'years',
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->current_term_starts_at)->not->toBeNull()
                ->and($licenseAttributed->current_term_ends_at)->not->toBeNull();
        });

        test('sets activated_at for free licenses', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Free License',
                'license_code' => 'FREE-002',
                'requester_model' => [],
                'unit_value_individual' => 0,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('does not set activated_at for paid licenses', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id,
                'type_id' => $this->licenseType->id,
                'name' => 'Paid License',
                'license_code' => 'PAID-002',
                'requester_model' => [],
                'unit_value_individual' => 100,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            $action = app(PurchaseLicenseAction::class);
            $licenseAttributed = $action($license, $individual);

            expect($licenseAttributed->activated_at)->toBeNull();
        });
    });

    describe('Committee-specific license purchase and activation (Entity)', function () {
        test('entity purchases SPORT committee license, generates payment, and activates after payment', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id, // SPORT
                'type_id' => $this->licenseType->id,
                'name' => 'Sport Entity License',
                'license_code' => 'SPORT-ENT-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 150,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $entity);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('entity')
                ->and($licenseAttributed->model_id)->toBe((string) $entity->id)
                ->and((float) $licenseAttributed->total_value)->toBe(150.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->committee->id);
            expect($licenseAttributed->license->committee->code)->toBe('SPORT');

            // Step 2: Activate after payment (bypass payment check since no document exists in test)
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('entity purchases SCIENTIFIC committee license, generates payment, and activates after payment', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->scientificCommittee->id, // SCIENTIFIC (international)
                'type_id' => $this->licenseType->id,
                'name' => 'Scientific Entity License',
                'license_code' => 'SCIENT-ENT-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 250,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $entity);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('entity')
                ->and($licenseAttributed->model_id)->toBe((string) $entity->id)
                ->and((float) $licenseAttributed->total_value)->toBe(250.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->scientificCommittee->id);
            expect($licenseAttributed->license->committee->code)->toBe('SCIENTIFIC');

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('entity purchases DIVING committee license, generates payment, and activates after payment', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingCommittee->id, // DIVING (international)
                'type_id' => $this->licenseType->id,
                'name' => 'International Diving Entity License',
                'license_code' => 'DIVING-ENT-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 300,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $entity);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('entity')
                ->and($licenseAttributed->model_id)->toBe((string) $entity->id)
                ->and((float) $licenseAttributed->total_value)->toBe(300.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->divingCommittee->id);
            expect($licenseAttributed->license->committee->code)->toBe('DIVING');
            expect($licenseAttributed->license->committee->is_international)->toBeTrue();

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('entity purchases DIVINGSERVICES committee license, generates payment, and activates after payment', function () {
            $entity = createEntityWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingServicesCommittee->id, // DIVINGSERVICES (non-international)
                'type_id' => $this->licenseType->id,
                'name' => 'Diving Services Entity License',
                'license_code' => 'DIVSERV-ENT-001',
                'requester_model' => ['Entity'],
                'unit_value_entity' => 180,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $entity);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('entity')
                ->and($licenseAttributed->model_id)->toBe((string) $entity->id)
                ->and((float) $licenseAttributed->total_value)->toBe(180.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->divingServicesCommittee->id);
            expect($licenseAttributed->license->committee->code)->toBe('DIVINGSERVICES');
            expect($licenseAttributed->license->committee->is_international)->toBeFalse();

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });
    });

    describe('Committee-specific license purchase and activation (Individual)', function () {
        test('individual purchases SPORT committee license, generates payment, and activates after payment', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->committee->id, // SPORT
                'type_id' => $this->licenseType->id,
                'name' => 'Sport Individual License',
                'license_code' => 'SPORT-IND-001',
                'requester_model' => ['Individual'],
                'unit_value_individual' => 75,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $individual);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('individual')
                ->and($licenseAttributed->model_id)->toBe($individual->id)
                ->and((float) $licenseAttributed->total_value)->toBe(75.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->committee->id);
            expect($licenseAttributed->license->committee->code)->toBe('SPORT');

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('individual purchases SCIENTIFIC committee license, generates payment, and activates after payment', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->scientificCommittee->id, // SCIENTIFIC (international)
                'type_id' => $this->licenseType->id,
                'name' => 'Scientific Individual License',
                'license_code' => 'SCIENT-IND-001',
                'requester_model' => ['Individual'],
                'unit_value_individual' => 120,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $individual);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('individual')
                ->and($licenseAttributed->model_id)->toBe($individual->id)
                ->and((float) $licenseAttributed->total_value)->toBe(120.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->scientificCommittee->id);
            expect($licenseAttributed->license->committee->code)->toBe('SCIENTIFIC');

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('individual purchases DIVING committee license, generates payment, and activates after payment', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingCommittee->id, // DIVING (international)
                'type_id' => $this->licenseType->id,
                'name' => 'International Diving Individual License',
                'license_code' => 'DIVING-IND-001',
                'requester_model' => ['Individual'],
                'unit_value_individual' => 200,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $individual);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('individual')
                ->and($licenseAttributed->model_id)->toBe($individual->id)
                ->and((float) $licenseAttributed->total_value)->toBe(200.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->divingCommittee->id);
            expect($licenseAttributed->license->committee->code)->toBe('DIVING');
            expect($licenseAttributed->license->committee->is_international)->toBeTrue();

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });

        test('individual purchases DIVINGSERVICES committee license, generates payment, and activates after payment', function () {
            $individual = createIndividualWithAffiliation(
                $this->federation,
                $this->membershipPackage,
                $this->affiliationPlan
            );

            $license = License::factory()->create([
                'committee_id' => $this->divingServicesCommittee->id, // DIVINGSERVICES (non-international)
                'type_id' => $this->licenseType->id,
                'name' => 'Diving Services Individual License',
                'license_code' => 'DIVSERV-IND-001',
                'requester_model' => ['Individual'],
                'unit_value_individual' => 90,
                'requires_admin_validation' => false,
                'active' => true,
            ]);
            $license->federations()->attach($this->federation->id);

            // Step 1: Purchase license
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($license, $individual);

            expect($licenseAttributed)
                ->toBeInstanceOf(LicenseAttributed::class)
                ->and($licenseAttributed->license_id)->toBe($license->id)
                ->and($licenseAttributed->model_type)->toBe('individual')
                ->and($licenseAttributed->model_id)->toBe($individual->id)
                ->and((float) $licenseAttributed->total_value)->toBe(90.0)
                ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

            // Verify license is associated to correct committee
            expect($licenseAttributed->license->committee_id)->toBe($this->divingServicesCommittee->id);
            expect($licenseAttributed->license->committee->code)->toBe('DIVINGSERVICES');
            expect($licenseAttributed->license->committee->is_international)->toBeFalse();

            // Step 2: Activate after payment
            $activateAction = app(ActivateLicenseAttributedAction::class);
            $activateAction($licenseAttributed, null, bypassPaymentCheck: true);

            $licenseAttributed->refresh();

            expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
                ->and($licenseAttributed->activated_at)->not->toBeNull();
        });
    });
});
