<?php

use App\Models\Committee;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create ENTITY group
    $entityGroup = \App\Models\Group::firstOrCreate(
        ['code' => 'ENTITY'],
        ['name' => 'Entity']
    );

    $this->user = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->federation = Federation::factory()->create();

    // Create entity and associate with user
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->user->id);
    $this->entity->federations()->attach($this->federation, [
        'status_class' => 'Domain\\Entities\\States\\ActiveEntityFederationState',
    ]);

    // Create committees with appropriate is_international flags
    $this->sportCommittee = Committee::factory()->create([
        'code' => 'sport',
        'name' => 'Sport Committee',
        'is_international' => false,
    ]);

    $this->divingCommittee = Committee::factory()->create([
        'code' => 'diving',
        'name' => 'Technical Committee',
        'is_international' => true,
    ]);

    $this->scientificCommittee = Committee::factory()->create([
        'code' => 'scientific',
        'name' => 'Scientific Committee',
        'is_international' => true,
    ]);

    // Create license types
    $this->entityLicenseType = LicenseType::factory()->create([
        'name' => 'Entity License Type',
        'is_individual' => false,
    ]);

    $this->individualLicenseType = LicenseType::factory()->create([
        'name' => 'Individual License Type',
        'is_individual' => true,
    ]);
});

describe('Sport Licenses Attributed Routes', function () {
    it('can access sport entity licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'SPORT')
            ->assertViewHas('isInternational', false)
            ->assertViewHas('type', 'entity');
    });

    it('can access sport member licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'SPORT')
            ->assertViewHas('isInternational', false)
            ->assertViewHas('type', 'members');
    });
});

describe('International Diving Licenses Attributed Routes', function () {
    it('can access international diving entity licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'DIVING')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'entity');
    });

    it('can access international diving member licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'DIVING')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'members');
    });
});

describe('Scientific Licenses Attributed Routes', function () {
    it('can access scientific entity licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'SCIENTIFIC')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'entity');
    });

    it('can access scientific member licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'SCIENTIFIC')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'members');
    });
});

describe('National Diving Licenses Attributed Routes', function () {
    it('can access national diving member licenses attributed page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.national-diving-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license_attributed.index')
            ->assertViewHas('committee', 'DIVINGSERVICES')
            ->assertViewHas('isInternational', false)
            ->assertViewHas('type', 'members');
    });
});

describe('License Filtering by Committee and isInternational', function () {
    it('sport route shows only national sport licenses', function () {
        // Create national sport license attributed to entity
        // Sport committee has is_international = false, so this license is national
        $nationalLicense = License::factory()->create([
            'committee_id' => $this->sportCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'National Sport License',
            'active' => true,
        ]);
        $nationalLicense->federations()->attach($this->federation->id);

        LicenseAttributed::factory()->create([
            'license_id' => $nationalLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Create international diving license (should not appear)
        // Diving committee has is_international = true, so this license is international
        $internationalLicense = License::factory()->create([
            'committee_id' => $this->divingCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'International Diving License',
            'active' => true,
        ]);
        $internationalLicense->federations()->attach($this->federation->id);

        LicenseAttributed::factory()->create([
            'license_id' => $internationalLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-licenses-attributed.index'));

        $response->assertOk();

        // Verify only national sport license appears
        $licenses = $response->viewData('licenses');
        expect($licenses)->toHaveCount(1);
        expect($licenses->first()->license->name)->toBe('National Sport License');
    });

    it('international diving route shows only international diving licenses', function () {
        // Create international diving license
        // Diving committee has is_international = true, so this license is international
        $internationalLicense = License::factory()->create([
            'committee_id' => $this->divingCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'International Diving License',
            'active' => true,
        ]);
        $internationalLicense->federations()->attach($this->federation->id);

        LicenseAttributed::factory()->create([
            'license_id' => $internationalLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        // Create national sport license (should not appear)
        // Sport committee has is_international = false, so this license is national
        $nationalLicense = License::factory()->create([
            'committee_id' => $this->sportCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'National Sport License',
            'active' => true,
        ]);
        $nationalLicense->federations()->attach($this->federation->id);

        LicenseAttributed::factory()->create([
            'license_id' => $nationalLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-licenses-attributed.index'));

        $response->assertOk();

        // Verify only international diving license appears
        $licenses = $response->viewData('licenses');
        expect($licenses)->toHaveCount(1);
        expect($licenses->first()->license->name)->toBe('International Diving License');
    });
});

describe('Purchase License Action Button', function () {
    it('sport entity page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.sport-license-purchase.index'));
    });

    it('sport members page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.sport-member-license-purchase.index'));
    });

    it('international diving entity page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.international-diving-license-purchase.index'));
    });

    it('international diving members page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.international-diving-member-license-purchase.index'));
    });

    it('scientific entity page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.scientific-license-purchase.index'));
    });

    it('scientific members page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.scientific-member-license-purchase.index'));
    });

    it('national diving members page has correct purchase button link', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.national-diving-member-licenses-attributed.index'));

        $response->assertOk()
            ->assertSee(route('entity.national-diving-member-license-purchase.index'));
    });
});

describe('Route Access Control', function () {
    it('requires authentication for all routes', function () {
        $routes = [
            'entity.sport-licenses-attributed.index',
            'entity.international-diving-licenses-attributed.index',
            'entity.scientific-licenses-attributed.index',
            'entity.sport-member-licenses-attributed.index',
            'entity.international-diving-member-licenses-attributed.index',
            'entity.scientific-member-licenses-attributed.index',
            'entity.national-diving-member-licenses-attributed.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('login'));
        }
    });

    it('requires entity association for all routes', function () {
        // Create user without entity
        $userWithoutEntity = User::factory()->create();

        $response = $this->actingAs($userWithoutEntity)
            ->get(route('entity.sport-licenses-attributed.index'));

        $response->assertForbidden();
    });
});
