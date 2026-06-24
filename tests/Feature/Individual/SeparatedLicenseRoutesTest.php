<?php

use App\Livewire\Individual\LicensePurchaseForm;
use App\Models\Committee;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=CommitteeSeeder');
    artisan('db:seed --class=UserGroupSeeder');

    // Get the INDIVIDUAL group
    $individualGroup = \App\Models\Group::where('code', 'INDIVIDUAL')->first();

    // Create a user with INDIVIDUAL group
    $this->user = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create(['user_id' => $this->user->id]);
    $this->individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');

    // Create main federation
    $this->federation = Federation::factory()->create([
        'name' => 'Primary Federation',
        'is_default_federation' => true,
    ]);

    // Attach individual to federation
    $this->individual->federations()->attach($this->federation->id, [
        'status_class' => 'Domain\Individuals\States\ActiveIndividualFederationState',
    ]);

    // Get committees
    $this->sportCommittee = Committee::where('code', 'sport')->first();
    $this->divingCommittee = Committee::where('code', 'diving')->first();
    $this->scientificCommittee = Committee::where('code', 'scientific')->first();

    // Create licenses for each type
    // Sport committee is national (is_international = false on committee)
    $this->sportLicense = License::factory()->create([
        'name' => 'Sport Professional License',
        'requester_model' => ['Individual'],
        'active' => true,
        'committee_id' => $this->sportCommittee?->id,
    ]);

    // Diving committee is international (is_international = true on committee)
    // This national license uses the diving committee but will be treated as international.
    $this->nationalDivingLicense = License::factory()->create([
        'name' => 'National Diving Professional License',
        'requester_model' => ['Individual'],
        'active' => true,
        'committee_id' => $this->divingCommittee?->id,
    ]);

    // Diving committee is international (is_international = true on committee)
    $this->internationalDivingLicense = License::factory()->create([
        'name' => 'CMAS Recreational Diving License',
        'requester_model' => ['Individual'],
        'active' => true,
        'committee_id' => $this->divingCommittee?->id,
    ]);

    // Scientific committee is international (is_international = true on committee)
    $this->scientificLicense = License::factory()->create([
        'name' => 'CMAS Scientific Diving License',
        'requester_model' => ['Individual'],
        'active' => true,
        'committee_id' => $this->scientificCommittee?->id,
    ]);

    // Attach licenses to federation
    $this->federation->licenses()->attach([
        $this->sportLicense->id,
        $this->nationalDivingLicense->id,
        $this->internationalDivingLicense->id,
        $this->scientificLicense->id,
    ]);
});

// Route access tests
test('individual can access sport license purchase page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.sport-license-purchase.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'SPORT')
        ->assertViewHas('isInternational', false)
        ->assertViewHas('pageTitle');
});

test('individual can access national diving license purchase page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.national-diving-license-purchase.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'DIVINGSERVICES')
        ->assertViewHas('isInternational', false)
        ->assertViewHas('pageTitle');
});

test('individual can access international diving license purchase page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.international-diving-license-purchase.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'DIVING')
        ->assertViewHas('isInternational', true)
        ->assertViewHas('pageTitle');
});

test('individual can access scientific license purchase page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.scientific-license-purchase.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'SCIENTIFIC')
        ->assertViewHas('isInternational', true)
        ->assertViewHas('pageTitle');
});

// Livewire component tests
test('sport license purchase page only shows sport national licenses', function () {
    Livewire::actingAs($this->user)
        ->test(LicensePurchaseForm::class, [
            'individual' => $this->individual,
            'committee' => 'sport',
            'isInternational' => false,
        ])
        ->assertSet('committee', 'sport')
        ->assertSet('isInternational', false);
});

test('national diving license purchase page only shows national diving licenses', function () {
    Livewire::actingAs($this->user)
        ->test(LicensePurchaseForm::class, [
            'individual' => $this->individual,
            'committee' => 'diving',
            'isInternational' => false,
        ])
        ->assertSet('committee', 'diving')
        ->assertSet('isInternational', false);
});

test('international diving license purchase page only shows international diving licenses', function () {
    Livewire::actingAs($this->user)
        ->test(LicensePurchaseForm::class, [
            'individual' => $this->individual,
            'committee' => 'diving',
            'isInternational' => true,
        ])
        ->assertSet('committee', 'diving')
        ->assertSet('isInternational', true);
});

test('scientific license purchase page only shows international scientific licenses', function () {
    Livewire::actingAs($this->user)
        ->test(LicensePurchaseForm::class, [
            'individual' => $this->individual,
            'committee' => 'scientific',
            'isInternational' => true,
        ])
        ->assertSet('committee', 'scientific')
        ->assertSet('isInternational', true);
});

// Licenses attributed tests
test('individual can access sport licenses attributed page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.sport-licenses-attributed.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'SPORT')
        ->assertViewHas('isInternational', false);
});

test('individual can access national diving licenses attributed page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.national-diving-licenses-attributed.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'DIVINGSERVICES')
        ->assertViewHas('isInternational', false);
});

test('individual can access international diving licenses attributed page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.international-diving-licenses-attributed.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'DIVING')
        ->assertViewHas('isInternational', true);
});

test('individual can access scientific licenses attributed page', function () {
    $response = actingAs($this->user)
        ->get(route('individual.scientific-licenses-attributed.index'));

    $response->assertSuccessful()
        ->assertViewHas('committee', 'SCIENTIFIC')
        ->assertViewHas('isInternational', true);
});

// Authorization tests
test('user without individual profile cannot access separated license routes', function () {
    $userWithoutIndividual = User::factory()->create();

    actingAs($userWithoutIndividual)
        ->get(route('individual.sport-license-purchase.index'))
        ->assertForbidden();

    actingAs($userWithoutIndividual)
        ->get(route('individual.sport-licenses-attributed.index'))
        ->assertForbidden();
});

test('unauthenticated user cannot access separated license routes', function () {
    $this->get(route('individual.sport-license-purchase.index'))
        ->assertRedirect(route('login'));

    $this->get(route('individual.sport-licenses-attributed.index'))
        ->assertRedirect(route('login'));
});

// Page title tests
test('separated routes have correct page titles', function () {
    // Sport
    actingAs($this->user)
        ->get(route('individual.sport-license-purchase.index'))
        ->assertSee(__('licenses.individual_sport_license_title'));

    // National diving services
    actingAs($this->user)
        ->get(route('individual.national-diving-license-purchase.index'))
        ->assertSee(__('licenses.individual_national_diving_license_title'));

    // International Diving
    actingAs($this->user)
        ->get(route('individual.international-diving-license-purchase.index'))
        ->assertSee(__('licenses.individual_cmas_diving_license_title'));

    // Scientific
    actingAs($this->user)
        ->get(route('individual.scientific-license-purchase.index'))
        ->assertSee(__('licenses.individual_scientific_license_title'));
});
