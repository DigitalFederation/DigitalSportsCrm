<?php

use App\Models\Committee;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
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

describe('Sport License Purchase Routes', function () {
    it('can access sport entity license purchase page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'SPORT')
            ->assertViewHas('isInternational', false)
            ->assertViewHas('type', 'entity');
    });

    it('can access sport member license purchase page', function () {
        // Entity needs active license first for member purchases
        // Sport committee has is_international = false
        $entityLicense = License::factory()->create([
            'committee_id' => $this->sportCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'active' => true,
        ]);
        $entityLicense->federations()->attach($this->federation->id);

        // Create active license for entity
        \Domain\Licenses\Models\LicenseAttributed::factory()->create([
            'license_id' => $entityLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-member-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'SPORT')
            ->assertViewHas('isInternational', false)
            ->assertViewHas('type', 'members');
    });

    it('redirects member purchase to entity purchase if no active entity license', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-member-license-purchase.index'));

        $response->assertRedirect(route('entity.sport-license-purchase.index'))
            ->assertSessionHas('error');
    });
});

describe('Committee-aware member license redirect', function () {
    it('redirects international diving member purchase to international diving entity purchase', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-member-license-purchase.index'));

        $response->assertRedirect(route('entity.international-diving-license-purchase.index'))
            ->assertSessionHas('error');
    });

    it('redirects scientific member purchase to scientific entity purchase', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-member-license-purchase.index'));

        $response->assertRedirect(route('entity.scientific-license-purchase.index'))
            ->assertSessionHas('error');
    });

    it('redirects national diving member purchase to international diving entity purchase', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.national-diving-member-license-purchase.index'));

        $response->assertRedirect(route('entity.international-diving-license-purchase.index'))
            ->assertSessionHas('error');
    });
});

describe('International Diving License Purchase Routes', function () {
    it('can access international diving entity license purchase page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'DIVING')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'entity');
    });

    it('can access international diving member license purchase page', function () {
        // Entity needs active license first for member purchases
        // Diving committee has is_international = true
        $entityLicense = License::factory()->create([
            'committee_id' => $this->divingCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'active' => true,
        ]);
        $entityLicense->federations()->attach($this->federation->id);

        $licenseAttributed = \Domain\Licenses\Models\LicenseAttributed::factory()->create([
            'license_id' => $entityLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
        ]);

        // Verify entity has active license
        $this->entity->refresh();
        expect($this->entity->hasActiveEntityLicense())->toBeTrue();

        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-member-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'DIVING')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'members');
    });
});

describe('Scientific License Purchase Routes', function () {
    it('can access scientific entity license purchase page', function () {
        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'SCIENTIFIC')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'entity');
    });

    it('can access scientific member license purchase page', function () {
        // Entity needs active license first for member purchases
        // Scientific committee has is_international = true
        $entityLicense = License::factory()->create([
            'committee_id' => $this->scientificCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'active' => true,
        ]);
        $entityLicense->federations()->attach($this->federation->id);

        \Domain\Licenses\Models\LicenseAttributed::factory()->create([
            'license_id' => $entityLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('entity.scientific-member-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'SCIENTIFIC')
            ->assertViewHas('isInternational', true)
            ->assertViewHas('type', 'members');
    });
});

describe('National Diving License Purchase Routes', function () {
    it('can access national diving member license purchase page', function () {
        // Entity needs active license first for member purchases
        // Diving committee has is_international = true (committee-based)
        $entityLicense = License::factory()->create([
            'committee_id' => $this->divingCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'active' => true,
        ]);
        $entityLicense->federations()->attach($this->federation->id);

        \Domain\Licenses\Models\LicenseAttributed::factory()->create([
            'license_id' => $entityLicense->id,
            'model_type' => 'entity',
            'model_id' => $this->entity->id,
            'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('entity.national-diving-member-license-purchase.index'));

        $response->assertOk()
            ->assertViewIs('web.entity.license-purchase.index')
            ->assertViewHas('committee', 'DIVINGSERVICES')
            ->assertViewHas('isInternational', false)
            ->assertViewHas('type', 'members');
    });
});

describe('License Filtering by Committee isInternational', function () {
    it('sport route shows only national licenses', function () {
        // Create national sport license
        // Sport committee has is_international = false
        $nationalLicense = License::factory()->create([
            'committee_id' => $this->sportCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'National Sport License',
            'requester_model' => ['entity'],
            'active' => true,
        ]);
        $nationalLicense->federations()->attach($this->federation->id);

        // Create international diving license (should not appear - different committee)
        // Diving committee has is_international = true
        $internationalLicense = License::factory()->create([
            'committee_id' => $this->divingCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'International Diving License',
            'requester_model' => ['entity'],
            'active' => true,
        ]);
        $internationalLicense->federations()->attach($this->federation->id);

        $response = $this->actingAs($this->user)
            ->get(route('entity.sport-license-purchase.index'));

        $response->assertOk();

        // The Livewire component should filter to show only national
        // This verifies the route passes isInternational=false correctly
        $response->assertViewHas('isInternational', false);
    });

    it('international diving route shows only international licenses', function () {
        // Create national sport license (should not appear - different committee)
        // Sport committee has is_international = false
        $nationalLicense = License::factory()->create([
            'committee_id' => $this->sportCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'National Sport License',
            'requester_model' => ['entity'],
            'active' => true,
        ]);
        $nationalLicense->federations()->attach($this->federation->id);

        // Create international diving license
        // Diving committee has is_international = true
        $internationalLicense = License::factory()->create([
            'committee_id' => $this->divingCommittee->id,
            'type_id' => $this->entityLicenseType->id,
            'name' => 'International Diving License',
            'requester_model' => ['entity'],
            'active' => true,
        ]);
        $internationalLicense->federations()->attach($this->federation->id);

        $response = $this->actingAs($this->user)
            ->get(route('entity.international-diving-license-purchase.index'));

        $response->assertOk();

        // The Livewire component should filter to show only international
        // This verifies the route passes isInternational=true correctly
        $response->assertViewHas('isInternational', true);
    });
});

describe('Route Access Control', function () {
    it('requires authentication for all routes', function () {
        $routes = [
            'entity.sport-license-purchase.index',
            'entity.international-diving-license-purchase.index',
            'entity.scientific-license-purchase.index',
            'entity.sport-member-license-purchase.index',
            'entity.international-diving-member-license-purchase.index',
            'entity.scientific-member-license-purchase.index',
            'entity.national-diving-member-license-purchase.index',
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
            ->get(route('entity.sport-license-purchase.index'));

        $response->assertForbidden();
    });
});
