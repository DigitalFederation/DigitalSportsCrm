<?php

namespace Tests\Helpers;

use App\Models\Committee;
use App\Models\User;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Diving\States\ActiveDivingCertificationState;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;

class DivingTestHelpers
{
    /**
     * Create a complete entity setup with user and diving license
     */
    public static function createEntityWithDivingLicense(): array
    {
        $entityGroup = \App\Models\Group::where('code', 'ENTITY')->first();
        $user = User::factory()->create(['group_id' => $entityGroup->id]);
        $entity = Entity::factory()->create();
        $entity->users()->attach($user);

        // Create federation and attach entity with active status
        $federation = \Domain\Federations\Models\Federation::factory()->create([
            'name' => 'Test Federation',
            'is_default_federation' => true,
        ]);

        $entity->federations()->attach($federation, [
            'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
            'active' => true,
        ]);

        // Create membership package for entities
        $membershipPackage = \Domain\Memberships\Models\MembershipPackage::factory()->create([
            'name' => 'Entity Test Package',
            'target_type' => 'entity',
            'is_active' => true,
        ]);

        // Create affiliation plan
        $affiliationPlan = \Domain\Memberships\Models\AffiliationPlan::factory()->create([
            'name' => 'Entity Affiliation',
            'entity_fee' => 100,
        ]);

        $membershipPackage->affiliationPlans()->attach($affiliationPlan);
        $membershipPackage->federations()->attach($federation);

        // Create member subscription
        $memberSubscription = \Domain\Memberships\Models\MemberSubscription::create([
            'membership_package_id' => $membershipPackage->id,
            'member_type' => 'entity',
            'member_id' => $entity->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status_class' => \Domain\Memberships\States\ActiveMemberSubscriptionState::class,
        ]);

        // Create affiliation
        \Domain\Memberships\Models\Affiliation::create([
            'federation_id' => $federation->id,
            'member_subscription_id' => $memberSubscription->id,
            'member_type' => 'entity',
            'member_id' => $entity->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status_class' => \Domain\Memberships\States\ActiveAffiliationState::class,
        ]);

        // Get or create diving school license
        $divingLicense = License::where('name', 'Licença de Escola de Mergulho')->first();
        if (! $divingLicense) {
            $divingCommittee = Committee::firstOrCreate(['code' => 'DIVING'], ['name' => 'Diving Committee']);
            $entityLicenseType = LicenseType::firstOrCreate(['name' => 'entity']);

            $divingLicense = License::create([
                'name' => 'Licença de Escola de Mergulho',
                'license_code' => 'LEM',
                'committee_id' => $divingCommittee->id,
                'type_id' => $entityLicenseType->id,
                'unit_value' => 250.00,
                'unit_value_entity' => 250.00,
                'active' => true,
                'interval' => 1,
                'interval_unit' => 'years',
                'requester_model' => ['Entity'],
                'requires_official_documents' => false,
                'required_document_types' => [],
            ]);

            // Attach license to federation
            $divingLicense->federations()->attach($federation);
        } else {
            // Update existing license to ensure it has license_code and proper requester_model
            $updates = [];
            if (! $divingLicense->license_code) {
                $updates['license_code'] = 'LEM';
            }
            if (! is_array($divingLicense->requester_model)) {
                $updates['requester_model'] = ['Entity'];
            }
            // Ensure required_document_types is an array
            if (! is_array($divingLicense->required_document_types)) {
                $updates['required_document_types'] = [];
            }
            if (! empty($updates)) {
                $divingLicense->update($updates);
            }

            // Ensure license is attached to federation
            if (! $divingLicense->federations()->where('federation.id', $federation->id)->exists()) {
                $divingLicense->federations()->attach($federation);
            }
        }

        return [
            'user' => $user,
            'entity' => $entity,
            'license' => $divingLicense,
            'federation' => $federation,
        ];
    }

    /**
     * Create an individual with diving certifications
     */
    public static function createCertifiedDivingInstructor(array $certificationSystems = ['CMAS', 'SSI']): array
    {
        $individualGroup = \App\Models\Group::where('code', 'INDIVIDUAL')->first();
        $user = User::factory()->create(['group_id' => $individualGroup->id]);
        $individual = Individual::factory()->create([
            'user_id' => $user->id,
            'member_code' => 'INST' . fake()->numerify('####'),
        ]);

        $certifications = [];
        foreach ($certificationSystems as $system) {
            $certifications[] = DivingProfessionalCertification::factory()->create([
                'individual_id' => $individual->id,
                'certification_system' => $system,
                'certification_level' => 'Instructor',
                'status_class' => ActiveDivingCertificationState::class,
            ]);
        }

        return [
            'user' => $user,
            'individual' => $individual,
            'certifications' => $certifications,
        ];
    }

    /**
     * Create a diving license with technical director assignment
     */
    public static function createDivingLicenseWithAssignment(Entity $entity, Individual $technicalDirector): array
    {
        $divingLicense = License::where('name', 'Licença de Escola de Mergulho')->first();

        $licenseAttributed = LicenseAttributed::factory()->create([
            'model_type' => 'entity',
            'model_id' => $entity->id,
            'license_id' => $divingLicense->id,
            'status_class' => \Domain\Licenses\States\PendingValidationLicenseAttributedState::class,
        ]);

        $assignment = DivingEntityTechnicalDirector::factory()->create([
            'entity_id' => $entity->id,
            'individual_id' => $technicalDirector->id,
            'license_attributed_id' => $licenseAttributed->id,
            'license_id' => $divingLicense->id,
            'certification_systems' => ['CMAS', 'SSI'],
            'status_class' => AssignedDivingTechnicalDirectorState::class,
            'assigned_at' => now(),
        ]);

        return [
            'licenseAttributed' => $licenseAttributed,
            'assignment' => $assignment,
        ];
    }

    /**
     * Run all diving module seeders
     */
    public static function seedDivingModule(): void
    {
        // Use the actual seeder instead of direct insert
        if (class_exists(\Database\Seeders\UserGroupSeeder::class)) {
            app(\Database\Seeders\UserGroupSeeder::class)->run();
        }

        if (class_exists(\Database\Seeders\CommitteeSeeder::class)) {
            app(\Database\Seeders\CommitteeSeeder::class)->run();
        }

        if (class_exists(\Database\Seeders\DivingEntityLicenseSeeder::class)) {
            app(\Database\Seeders\DivingEntityLicenseSeeder::class)->run();
        }

        if (class_exists(\Database\Seeders\DivingProfessionalRoleSeeder::class)) {
            app(\Database\Seeders\DivingProfessionalRoleSeeder::class)->run();
        }
    }
}
