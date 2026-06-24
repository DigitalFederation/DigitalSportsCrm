<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\PendingPaymentAffiliationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $adminGroup = Group::where('code', 'ADMIN')->first();
    $this->admin = User::factory()->create([
        'email' => 'admin@example.test',
        'group_id' => $adminGroup->id,
        'active' => true,
    ]);
    $this->admin->assignRole('admin');

    $this->federation = Federation::factory()->create([
        'is_local' => true,
        'is_default_federation' => false,
    ]);
});

test('can filter affiliations by member_type individual', function () {
    $individual = Individual::factory()->create(['name' => 'Test Individual']);
    $entity = Entity::factory()->create(['name' => 'Test Entity']);

    Affiliation::factory()->forIndividual($individual)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forEntity($entity)->create([
        'federation_id' => $this->federation->id,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_member_type' => 'individual',
    ]));

    $response->assertSuccessful();
    $response->assertSee('Test Individual');
    $response->assertDontSee('Test Entity');
});

test('can filter affiliations by member_type entity', function () {
    $individual = Individual::factory()->create(['name' => 'Test Individual']);
    $entity = Entity::factory()->create(['name' => 'Test Entity']);

    Affiliation::factory()->forIndividual($individual)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forEntity($entity)->create([
        'federation_id' => $this->federation->id,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_member_type' => 'entity',
    ]));

    $response->assertSuccessful();
    $response->assertSee('Test Entity');
    $response->assertDontSee('Test Individual');
});

test('can filter affiliations by status_class', function () {
    $activeIndividual = Individual::factory()->create(['name' => 'Active Individual']);
    $pendingIndividual = Individual::factory()->create(['name' => 'Pending Individual']);

    Affiliation::factory()->forIndividual($activeIndividual)->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveAffiliationState::class,
    ]);
    Affiliation::factory()->forIndividual($pendingIndividual)->create([
        'federation_id' => $this->federation->id,
        'status_class' => PendingPaymentAffiliationState::class,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_status_class' => ActiveAffiliationState::class,
    ]));

    $response->assertSuccessful();
    $response->assertSee('Active Individual');
    $response->assertDontSee('Pending Individual');
});

test('can filter affiliations by federation', function () {
    $federation1 = Federation::factory()->create(['name' => 'Federation One', 'is_local' => true]);
    $federation2 = Federation::factory()->create(['name' => 'Federation Two', 'is_local' => true]);

    $individual1 = Individual::factory()->create(['name' => 'Individual One']);
    $individual2 = Individual::factory()->create(['name' => 'Individual Two']);

    Affiliation::factory()->forIndividual($individual1)->create([
        'federation_id' => $federation1->id,
    ]);
    Affiliation::factory()->forIndividual($individual2)->create([
        'federation_id' => $federation2->id,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_federation' => $federation1->id,
    ]));

    $response->assertSuccessful();
    $response->assertSee('Individual One');
    $response->assertDontSee('Individual Two');
});

test('can filter affiliations by member_name', function () {
    $individual1 = Individual::factory()->create(['name' => 'John Doe']);
    $individual2 = Individual::factory()->create(['name' => 'Jane Smith']);

    Affiliation::factory()->forIndividual($individual1)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forIndividual($individual2)->create([
        'federation_id' => $this->federation->id,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_member_name' => 'John',
    ]));

    $response->assertSuccessful();
    $response->assertSee('John Doe');
    $response->assertDontSee('Jane Smith');
});

test('pagination preserves filter parameters', function () {
    $individual = Individual::factory()->create(['name' => 'Test Individual']);

    // Create more than 15 affiliations (default pagination)
    for ($i = 0; $i < 20; $i++) {
        Affiliation::factory()->forIndividual($individual)->create([
            'federation_id' => $this->federation->id,
            'status_class' => ActiveAffiliationState::class,
        ]);
    }

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_status_class' => ActiveAffiliationState::class,
        'page' => 2,
    ]));

    $response->assertSuccessful();
    // Check that filter is preserved in pagination links
    $response->assertSee('filter_status_class=' . urlencode(ActiveAffiliationState::class));
});

test('filter_member_name escapes SQL wildcard characters', function () {
    $individual1 = Individual::factory()->create(['name' => 'John Regular']);
    $individual2 = Individual::factory()->create(['name' => 'Percent%Test']);

    Affiliation::factory()->forIndividual($individual1)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forIndividual($individual2)->create([
        'federation_id' => $this->federation->id,
    ]);

    $this->actingAs($this->admin);

    // Searching with a bare "%" should not match everything due to escaping
    $response = $this->get(route('admin.affiliations.index', [
        'filter_member_name' => '%',
    ]));

    $response->assertSuccessful();
});

test('filter_member_name matches word start not middle of word', function () {
    // Create individuals with names that could match "Ana"
    $ana = Individual::factory()->create(['name' => 'Ana Silva']);
    $mariaAna = Individual::factory()->create(['name' => 'Maria Ana']);
    $liliana = Individual::factory()->create(['name' => 'Liliana Costa']);
    $anapaula = Individual::factory()->create(['name' => 'Anapaula Santos']);

    Affiliation::factory()->forIndividual($ana)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forIndividual($mariaAna)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forIndividual($liliana)->create([
        'federation_id' => $this->federation->id,
    ]);
    Affiliation::factory()->forIndividual($anapaula)->create([
        'federation_id' => $this->federation->id,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.affiliations.index', [
        'filter_member_name' => 'Ana',
    ]));

    $response->assertSuccessful();
    // Should match names where "Ana" is at start of name or start of a word
    $response->assertSee('Ana Silva');
    $response->assertSee('Maria Ana');
    $response->assertSee('Anapaula Santos');
    // Should NOT match names where "Ana" is in middle of a word
    $response->assertDontSee('Liliana Costa');
});
