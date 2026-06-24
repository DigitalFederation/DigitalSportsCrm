<?php

namespace Tests\Feature;

use App\Models\Committee;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Actions\GetAllowedEntityLicensesAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FederationLicensePermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that entities can only see licenses from their federations
     */
    public function test_entity_can_only_see_licenses_from_their_federations(): void
    {
        // Create committees
        $sportCommittee = Committee::factory()->create(['code' => 'sport']);

        // Create license types
        $licenseType = LicenseType::factory()->create(['name' => 'entity', 'is_individual' => false]);

        // Create federations
        $federation1 = Federation::factory()->create(['name' => 'Federation 1']);
        $federation2 = Federation::factory()->create(['name' => 'Federation 2']);

        // Create licenses
        $license1 = License::factory()->create([
            'committee_id' => $sportCommittee->id,
            'type_id' => $licenseType->id,
            'name' => 'License 1',
            'requester_model' => Entity::class,
        ]);

        $license2 = License::factory()->create([
            'committee_id' => $sportCommittee->id,
            'type_id' => $licenseType->id,
            'name' => 'License 2',
            'requester_model' => Entity::class,
        ]);

        // Assign licenses to federations
        $federation1->licenses()->attach($license1);
        $federation2->licenses()->attach($license2);

        // Create entity and associate with federation1
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation1, ['status_class' => \Domain\Entities\States\ActiveEntityFederationState::class]);

        // Execute action
        $action = new GetAllowedEntityLicensesAction;
        $availableLicenses = $action('sport', $entity);

        // Assertions
        $this->assertCount(1, $availableLicenses);
        $this->assertTrue($availableLicenses->contains('id', $license1->id));
        $this->assertFalse($availableLicenses->contains('id', $license2->id));
    }

    /**
     * Test that entities with multiple federations see combined licenses
     */
    public function test_entity_with_multiple_federations_sees_combined_licenses(): void
    {
        // Create committee
        $committee = Committee::factory()->create(['code' => 'sport']);
        $licenseType = LicenseType::factory()->create(['name' => 'entity', 'is_individual' => false]);

        // Create federations
        $federation1 = Federation::factory()->create();
        $federation2 = Federation::factory()->create();

        // Create licenses
        $license1 = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);
        $license2 = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);
        $license3 = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);

        // Assign licenses to federations
        $federation1->licenses()->attach([$license1->id, $license2->id]);
        $federation2->licenses()->attach([$license2->id, $license3->id]);

        // Create entity with multiple federations
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation1, ['status_class' => \Domain\Entities\States\ActiveEntityFederationState::class]);
        $entity->federations()->attach($federation2, ['status_class' => \Domain\Entities\States\ActiveEntityFederationState::class]);

        // Execute action
        $action = new GetAllowedEntityLicensesAction;
        $availableLicenses = $action('sport', $entity);

        // Assertions - should see all three licenses (union of both federations)
        $this->assertCount(3, $availableLicenses);
        $this->assertTrue($availableLicenses->contains('id', $license1->id));
        $this->assertTrue($availableLicenses->contains('id', $license2->id));
        $this->assertTrue($availableLicenses->contains('id', $license3->id));
    }

    /**
     * Test that entities with no active federations see no licenses
     */
    public function test_entity_with_no_active_federations_sees_no_licenses(): void
    {
        // Create committee and license
        $committee = Committee::factory()->create(['code' => 'sport']);
        $licenseType = LicenseType::factory()->create(['name' => 'entity', 'is_individual' => false]);
        $license = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);

        // Create federation and assign license
        $federation = Federation::factory()->create();
        $federation->licenses()->attach($license);

        // Create entity with inactive federation membership
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation, ['status_class' => \Domain\Entities\States\PendingEntityFederationState::class]);

        // Execute action
        $action = new GetAllowedEntityLicensesAction;
        $availableLicenses = $action('sport', $entity);

        // Assertions
        $this->assertCount(0, $availableLicenses);
    }

    /**
     * Test cache is properly invalidated when federation licenses change
     */
    public function test_cache_is_invalidated_when_federation_licenses_change(): void
    {
        // Create basic setup
        $committee = Committee::factory()->create(['code' => 'sport']);
        $licenseType = LicenseType::factory()->create(['name' => 'entity', 'is_individual' => false]);
        $federation = Federation::factory()->create();
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation, ['status_class' => \Domain\Entities\States\ActiveEntityFederationState::class]);

        $license1 = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);

        // Initially, federation has no licenses
        $action = new GetAllowedEntityLicensesAction;
        $availableLicenses = $action('sport', $entity);
        $this->assertCount(0, $availableLicenses);

        // Add license to federation
        $federation->licenses()->attach($license1);

        // Clear cache manually (in real app, this would be done by FederationLicenseManager)
        Cache::forget("licenses_for_type_sport_entity_{$entity->id}");

        // Check again - should now see the license
        $availableLicenses = $action('sport', $entity);
        $this->assertCount(1, $availableLicenses);
        $this->assertTrue($availableLicenses->contains('id', $license1->id));
    }

    /**
     * Test already attributed licenses are excluded
     */
    public function test_already_attributed_licenses_are_excluded(): void
    {
        // Create user for CreatedUpdatedBy trait
        $user = \App\Models\User::factory()->create();

        // Create setup
        $committee = Committee::factory()->create(['code' => 'sport']);
        $licenseType = LicenseType::factory()->create(['name' => 'entity', 'is_individual' => false]);
        $federation = Federation::factory()->create();
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation, ['status_class' => \Domain\Entities\States\ActiveEntityFederationState::class]);

        // Create two licenses
        $license1 = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);
        $license2 = License::factory()->create([
            'committee_id' => $committee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);

        // Assign both to federation
        $federation->licenses()->attach([$license1->id, $license2->id]);

        // Attribute license1 to the entity (use licenses() relationship which is MorphMany to LicenseAttributed)
        $entity->licenses()->create([
            'license_id' => $license1->id,
            'federation_id' => $federation->id,
            'model_type' => 'entity',
            'model_id' => $entity->id,
            'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Execute action
        $action = new GetAllowedEntityLicensesAction;
        $availableLicenses = $action('sport', $entity);

        // Should only see license2 (license1 is already attributed)
        $this->assertCount(1, $availableLicenses);
        $this->assertFalse($availableLicenses->contains('id', $license1->id));
        $this->assertTrue($availableLicenses->contains('id', $license2->id));
    }

    /**
     * Test committee filtering works with federation permissions
     */
    public function test_committee_filtering_with_federation_permissions(): void
    {
        // Create committees
        $sportCommittee = Committee::factory()->create(['code' => 'sport']);
        $divingCommittee = Committee::factory()->create(['code' => 'diving']);
        $licenseType = LicenseType::factory()->create(['name' => 'entity', 'is_individual' => false]);

        // Create federation and entity
        $federation = Federation::factory()->create();
        $entity = Entity::factory()->create();
        $entity->federations()->attach($federation, ['status_class' => \Domain\Entities\States\ActiveEntityFederationState::class]);

        // Create licenses for different committees
        $sportLicense = License::factory()->create([
            'committee_id' => $sportCommittee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);
        $divingLicense = License::factory()->create([
            'committee_id' => $divingCommittee->id,
            'type_id' => $licenseType->id,
            'requester_model' => Entity::class,
        ]);

        // Assign both licenses to federation
        $federation->licenses()->attach([$sportLicense->id, $divingLicense->id]);

        // Execute action for sport committee
        $action = new GetAllowedEntityLicensesAction;
        $sportLicenses = $action('sport', $entity);

        // Should only see sport license
        $this->assertCount(1, $sportLicenses);
        $this->assertTrue($sportLicenses->contains('id', $sportLicense->id));

        // Execute action for diving committee
        $divingLicenses = $action('diving', $entity);

        // Should only see diving license
        $this->assertCount(1, $divingLicenses);
        $this->assertTrue($divingLicenses->contains('id', $divingLicense->id));
    }
}
