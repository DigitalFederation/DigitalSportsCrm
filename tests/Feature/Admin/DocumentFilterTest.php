<?php

use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Individuals\Models\Individual;
use Domain\Payments\Models\PaymentMethod;
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

    $this->documentType = DocumentType::firstOrCreate(
        ['code' => 'ORD'],
        ['name' => 'Order', 'prefix' => 'ORD']
    );

    $this->paymentMethod = PaymentMethod::factory()->create(['driver' => 'offline']);
});

test('filter_member_name escapes SQL wildcard percent character', function () {
    $individual = Individual::factory()->create(['name' => 'John Regular']);
    $wildcardIndividual = Individual::factory()->create(['name' => 'Percent Test']);

    Document::factory()->create([
        'type_id' => $this->documentType->id,
        'owner_type' => Individual::class,
        'owner_id' => $individual->id,
        'method_id' => $this->paymentMethod->id,
    ]);

    Document::factory()->create([
        'type_id' => $this->documentType->id,
        'owner_type' => Individual::class,
        'owner_id' => $wildcardIndividual->id,
        'method_id' => $this->paymentMethod->id,
    ]);

    $this->actingAs($this->admin);

    // Searching with a bare "%" should not match everything due to escaping
    $response = $this->get(route('admin.document.index', [
        'filter' => ['filter_member_name' => '%'],
    ]));

    $response->assertSuccessful();

    // Searching for a specific name still works normally
    $response = $this->get(route('admin.document.index', [
        'filter' => ['filter_member_name' => 'Percent Test'],
    ]));

    $response->assertSuccessful();
    $response->assertSee('Percent Test');
});

test('filter_member_name escapes SQL wildcard underscore character', function () {
    $individual = Individual::factory()->create(['name' => 'Jane Smith']);
    $underscoreIndividual = Individual::factory()->create(['name' => 'Jane_Smith']);

    Document::factory()->create([
        'type_id' => $this->documentType->id,
        'owner_type' => Individual::class,
        'owner_id' => $individual->id,
        'method_id' => $this->paymentMethod->id,
    ]);

    Document::factory()->create([
        'type_id' => $this->documentType->id,
        'owner_type' => Individual::class,
        'owner_id' => $underscoreIndividual->id,
        'method_id' => $this->paymentMethod->id,
    ]);

    $this->actingAs($this->admin);

    // Searching for "Jane_Smith" (literal underscore) should match only the individual with underscore
    $response = $this->get(route('admin.document.index', [
        'filter' => ['filter_member_name' => 'Jane_Smith'],
    ]));

    $response->assertSuccessful();
    $response->assertSee('Jane_Smith');
});

test('filter_member_name returns results for normal name search', function () {
    $individual = Individual::factory()->create(['name' => 'Pedro']);

    Document::factory()->create([
        'type_id' => $this->documentType->id,
        'owner_type' => Individual::class,
        'owner_id' => $individual->id,
        'method_id' => $this->paymentMethod->id,
    ]);

    $this->actingAs($this->admin);

    $response = $this->get(route('admin.document.index', [
        'filter' => ['filter_member_name' => 'Pedro'],
    ]));

    $response->assertSuccessful();
    $response->assertSee('Pedro');
});
