<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permission
    Permission::findOrCreate('access international licenses', 'web');

    // Create FEDERATION group
    $this->federationGroup = Group::firstOrCreate(['code' => 'FEDERATION'], ['name' => 'Federation']);

    // Create committees and supporting data
    // DIVING and SCIENTIFIC committees are international (is_international = true on committee)
    $this->divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);
    $this->scientificCommittee = Committee::factory()->create([
        'code' => 'SCIENTIFIC',
        'name' => 'Scientific',
        'is_international' => true,
    ]);
    $this->sport = Sport::factory()->create();
    $this->licenseType = LicenseType::factory()->create();

    // Create international license using diving committee
    // Internationality is now determined by the committee's is_international flag
    $this->internationalLicense = License::factory()->create([
        'committee_id' => $this->divingCommittee->id,
        'type_id' => $this->licenseType->id,
        'sport_id' => $this->sport->id,
        'name' => 'International License',
    ]);

    // Create international certification using diving committee
    // Internationality is now determined by the committee's is_international flag
    $this->internationalCertification = Certification::factory()->create([
        'committee_id' => $this->divingCommittee->id,
        'name' => 'International Diving Certification',
    ]);
});

test('federation can only see licenses from their federation and child federations', function () {
    // Create federation hierarchy
    $parentFederation = Federation::factory()->create(['name' => 'Parent Federation']);
    $childFederation = Federation::factory()->create([
        'name' => 'Child Federation',
        'parent_id' => $parentFederation->id,
    ]);
    $otherFederation = Federation::factory()->create(['name' => 'Other Federation']);

    // Create user for parent federation
    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($parentFederation->id);
    $user->givePermissionTo('access international licenses');

    $individual = Individual::factory()->create();

    // Create licenses for all federations
    $parentLicense = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'federation_id' => $parentFederation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $childLicense = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'federation_id' => $childFederation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $otherLicense = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'federation_id' => $otherFederation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Parent federation should see parent and child licenses, not other
    $response = $this->actingAs($user)->get(route('international.federation.licenses-attributed.index'));
    $response->assertSuccessful();
});

test('federation can only see certifications from their federation and child federations', function () {
    // Create federation hierarchy
    $parentFederation = Federation::factory()->create(['name' => 'Parent Federation']);
    $childFederation = Federation::factory()->create([
        'name' => 'Child Federation',
        'parent_id' => $parentFederation->id,
    ]);
    $otherFederation = Federation::factory()->create(['name' => 'Other Federation']);

    // Create user for parent federation
    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($parentFederation->id);
    $user->givePermissionTo('access international licenses');

    $individual = Individual::factory()->create();

    // Create certifications for all federations
    $parentCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $individual->id,
        'federation_id' => $parentFederation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => 'Parent Member',
    ]);

    $childCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $individual->id,
        'federation_id' => $childFederation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => 'Child Member',
    ]);

    $otherCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $individual->id,
        'federation_id' => $otherFederation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => 'Other Member',
    ]);

    // Parent federation should see parent and child, not other
    $response = $this->actingAs($user)->get(route('international.federation.certifications-attributed.index'));
    $response->assertSuccessful();
    $response->assertSee('Parent Member');
    $response->assertSee('Child Member');
    $response->assertDontSee('Other Member');
});

test('federation requires permission to access international routes', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($federation->id);
    // No permission given

    // Should be forbidden
    $response = $this->actingAs($user)->get(route('international.federation.licenses-attributed.index'));
    $response->assertForbidden();

    $response = $this->actingAs($user)->get(route('international.federation.certifications-attributed.index'));
    $response->assertForbidden();
});

test('federation with permission can access international routes', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($federation->id);
    $user->givePermissionTo('access international licenses');

    // Should be successful
    $response = $this->actingAs($user)->get(route('international.federation.licenses-attributed.index'));
    $response->assertSuccessful();

    $response = $this->actingAs($user)->get(route('international.federation.certifications-attributed.index'));
    $response->assertSuccessful();
});

test('federation can view license details for licenses in their scope', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($federation->id);
    $user->givePermissionTo('access international licenses');

    $individual = Individual::factory()->create();

    // Create license in their federation
    $license = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Should be able to view
    $response = $this->actingAs($user)->get(route('international.federation.licenses-attributed.show', $license->id));
    $response->assertSuccessful();
});

test('federation cannot view license details for licenses outside their scope', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();

    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($federation1->id);
    $user->givePermissionTo('access international licenses');

    $individual = Individual::factory()->create();

    // Create license in federation2
    $license = LicenseAttributed::factory()->create([
        'license_id' => $this->internationalLicense->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'federation_id' => $federation2->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Federation1 user tries to view federation2's license
    $response = $this->actingAs($user)->get(route('international.federation.licenses-attributed.show', $license->id));
    $response->assertRedirect();
});

test('federation certifications view only shows diving and scientific committees', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create(['group_id' => $this->federationGroup->id]);
    $user->federations()->attach($federation->id);
    $user->givePermissionTo('access international licenses');

    $individual = Individual::factory()->create();

    // Create diving certification
    $divingCert = CertificationAttributed::factory()->create([
        'certification_id' => $this->internationalCertification->id,
        'individual_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => 'Diving Member',
    ]);

    // Create scientific certification using scientific committee (international)
    // Internationality is now determined by the committee's is_international flag
    $scientificCertification = Certification::factory()->create([
        'committee_id' => $this->scientificCommittee->id,
        'name' => 'CMAS Scientific Certification',
    ]);

    $scientificCert = CertificationAttributed::factory()->create([
        'certification_id' => $scientificCertification->id,
        'individual_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => 'Scientific Member',
    ]);

    // Should see both diving and scientific
    $response = $this->actingAs($user)->get(route('international.federation.certifications-attributed.index'));
    $response->assertSuccessful();
    $response->assertSee('Diving Member');
    $response->assertSee('Scientific Member');
});
