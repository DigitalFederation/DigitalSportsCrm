<?php

use App\Enums\UserGroupEnum;
use App\Models\Committee;
use App\Models\Group;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminGroup = Group::firstOrCreate(['id' => UserGroupEnum::ADMIN->value], ['code' => 'ADMIN', 'name' => 'Admin']);
    $entityGroup = Group::firstOrCreate(['id' => UserGroupEnum::ENTITY->value], ['code' => 'ENTITY', 'name' => 'Entity']);
    $federationGroup = Group::firstOrCreate(['id' => UserGroupEnum::FEDERATION->value], ['code' => 'FEDERATION', 'name' => 'Federation']);

    $this->divingCommittee = Committee::factory()->create(['code' => 'DIVING']);
    $this->otherCommittee = Committee::factory()->create(['code' => 'OTHER']);

    $this->divingLicense = License::factory()->create(['committee_id' => $this->divingCommittee->id]);
    $this->otherLicense = License::factory()->create(['committee_id' => $this->otherCommittee->id]);

    $this->federation = Federation::factory()->create();

    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->federation, ['status_class' => ActiveEntityFederationState::class]);

    $this->otherEntity = Entity::factory()->create();
    $this->otherEntity->federations()->attach($this->federation, ['status_class' => ActiveEntityFederationState::class]);

    $this->divingLicenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $this->otherLicenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $this->otherLicense->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $this->entityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entityUser->entities()->attach($this->entity);

    $this->otherEntityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->otherEntityUser->entities()->attach($this->otherEntity);

    $this->federationUser = User::factory()->create(['group_id' => $federationGroup->id]);
    $this->federationUser->federations()->attach($this->federation);
    $federationRole = Role::firstOrCreate(['name' => 'federation-admin'], ['guard_name' => 'web']);
    $this->federationUser->assignRole($federationRole);

    $this->adminUser = User::factory()->create(['group_id' => $adminGroup->id]);
    $adminRole = Role::firstOrCreate(['name' => 'association-admin'], ['guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'access diving certifications attributed', 'guard_name' => 'web']);
    $adminRole->givePermissionTo($permission);
    $this->adminUser->assignRole($adminRole);
});

describe('Entity namespace authorization', function () {
    test('entity user can download their own diving license PDF', function () {
        $this->actingAs($this->entityUser)
            ->get(route('entity.diving_licenses.pdf', $this->divingLicenseAttributed))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    });

    test('entity user cannot download another entity diving license PDF', function () {
        $otherEntityLicense = LicenseAttributed::factory()->create([
            'license_id' => $this->divingLicense->id,
            'federation_id' => $this->federation->id,
            'model_type' => 'entity',
            'model_id' => $this->otherEntity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        $this->actingAs($this->entityUser)
            ->get(route('entity.diving_licenses.pdf', $otherEntityLicense))
            ->assertForbidden();
    });

    test('entity user cannot download non-diving license PDF', function () {
        $this->actingAs($this->entityUser)
            ->get(route('entity.diving_licenses.pdf', $this->otherLicenseAttributed))
            ->assertForbidden();
    });

    test('unauthenticated user cannot download PDF', function () {
        $this->get(route('entity.diving_licenses.pdf', $this->divingLicenseAttributed))
            ->assertRedirect(route('login'));
    });
});

describe('Federation namespace authorization', function () {
    test('federation user can download diving licenses from their federation', function () {
        $this->actingAs($this->federationUser)
            ->get(route('federation.license-attributed.pdf', $this->divingLicenseAttributed))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    });

    test('federation user cannot download licenses from other federations', function () {
        $otherFederation = Federation::factory()->create();
        $otherFederationLicense = LicenseAttributed::factory()->create([
            'license_id' => $this->divingLicense->id,
            'federation_id' => $otherFederation->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        $this->actingAs($this->federationUser)
            ->get(route('federation.license-attributed.pdf', $otherFederationLicense))
            ->assertForbidden();
    });

    test('federation user cannot download non-diving licenses', function () {
        $this->actingAs($this->federationUser)
            ->get(route('federation.license-attributed.pdf', $this->otherLicenseAttributed))
            ->assertForbidden();
    });
});

describe('Admin namespace authorization', function () {
    test('admin user can download any diving license PDF', function () {
        $this->actingAs($this->adminUser)
            ->get(route('admin.entity_diving_license_validation.pdf', $this->divingLicenseAttributed))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    });

    test('admin user cannot download non-diving license PDF', function () {
        $this->actingAs($this->adminUser)
            ->get(route('admin.entity_diving_license_validation.pdf', $this->otherLicenseAttributed))
            ->assertForbidden();
    });

    test('non-admin user cannot access admin PDF route', function () {
        $this->actingAs($this->entityUser)
            ->get(route('admin.entity_diving_license_validation.pdf', $this->divingLicenseAttributed))
            ->assertForbidden();
    });
});

describe('Rate limiting', function () {
    test('PDF download is rate limited to 10 requests per minute', function () {
        $this->actingAs($this->entityUser);

        for ($i = 0; $i < 10; $i++) {
            $this->get(route('entity.diving_licenses.pdf', $this->divingLicenseAttributed))
                ->assertOk();
        }

        $this->get(route('entity.diving_licenses.pdf', $this->divingLicenseAttributed))
            ->assertTooManyRequests();
    });
});

describe('Authorization bypass prevention', function () {
    test('entity user cannot bypass authorization by using admin route', function () {
        $this->actingAs($this->entityUser)
            ->get(route('admin.entity_diving_license_validation.pdf', $this->divingLicenseAttributed))
            ->assertForbidden();
    });

    test('entity user cannot bypass authorization by using federation route', function () {
        $otherEntityLicense = LicenseAttributed::factory()->create([
            'license_id' => $this->divingLicense->id,
            'federation_id' => $this->federation->id,
            'model_type' => 'entity',
            'model_id' => $this->otherEntity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        $this->actingAs($this->entityUser)
            ->get(route('federation.license-attributed.pdf', $otherEntityLicense))
            ->assertForbidden();
    });
});
