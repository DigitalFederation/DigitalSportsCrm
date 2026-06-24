<?php

use App\Enums\UserGroupEnum;
use App\Livewire\Admin\Dashboard\MonthlyPaymentsTable;
use App\Models\Group;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->adminUser = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->adminUser->assignRole('admin');
});

it('renders the monthly payments table component', function () {
    actingAs($this->adminUser);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSuccessful()
        ->assertSee(__('dashboard.category'))
        ->assertSee(__('dashboard.month_jan'));
});

it('defaults to the current year', function () {
    actingAs($this->adminUser);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSet('selectedYear', now()->year);
});

it('updates data when year changes', function () {
    actingAs($this->adminUser);

    Livewire::test(MonthlyPaymentsTable::class)
        ->set('selectedYear', now()->year - 1)
        ->assertSet('selectedYear', now()->year - 1)
        ->assertSuccessful();
});

it('classifies entity affiliations correctly', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $entity = Entity::factory()->create();
    $subscription = MemberSubscription::factory()->forEntity($entity)->create();

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 100.00,
        'created_at' => now()->startOfYear()->addMonths(2),
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiação: Plano Anual',
        'total_value' => 100.00,
        'is_debit' => false,
    ]);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSee('100,00');
});

it('classifies individual licenses correctly', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => 1,
        'total_value' => 250.50,
        'created_at' => now()->startOfYear()->addMonths(5),
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => LicenseAttributed::class,
        'total_value' => 250.50,
        'is_debit' => false,
    ]);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSee('250,50');
});

it('shows totals row summing all categories', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(0); // January

    $entity = Entity::factory()->create();
    $entitySub = MemberSubscription::factory()->forEntity($entity)->create();

    $doc1 = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 50.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $doc1->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $entitySub->id,
        'total_value' => 50.00,
        'is_debit' => false,
    ]);

    $individual = Individual::factory()->create();
    $individualSub = MemberSubscription::factory()->forIndividual($individual)->create();

    $doc2 = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 30.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $doc2->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $individualSub->id,
        'total_value' => 30.00,
        'is_debit' => false,
    ]);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSee('50,00')
        ->assertSee('30,00')
        ->assertSee('80,00');
});

it('shows zeros for a year with no data', function () {
    actingAs($this->adminUser);
    Cache::flush();

    Livewire::test(MonthlyPaymentsTable::class)
        ->set('selectedYear', 2020)
        ->assertSee('0,00');
});

it('excludes non-paid documents', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $document = Document::factory()->create([
        'status_class' => 'Domain\\Documents\\States\\PendingDocumentState',
        'owner_type' => 'entity',
        'owner_id' => 1,
        'total_value' => 999.99,
        'created_at' => now(),
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'total_value' => 999.99,
        'is_debit' => false,
    ]);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertDontSee('999,99');
});

it('displays all nine category labels', function () {
    actingAs($this->adminUser);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSee(__('dashboard.entity_affiliations'))
        ->assertSee(__('dashboard.individual_affiliations'))
        ->assertSee(__('dashboard.entity_licenses'))
        ->assertSee(__('dashboard.individual_licenses'))
        ->assertSee(__('dashboard.event_registrations'))
        ->assertSee(__('dashboard.certifications'))
        ->assertSee(__('dashboard.entity_insurances'))
        ->assertSee(__('dashboard.individual_insurances'))
        ->assertSee(__('dashboard.others'));
});

it('displays all twelve month columns', function () {
    actingAs($this->adminUser);

    Livewire::test(MonthlyPaymentsTable::class)
        ->assertSee(__('dashboard.month_jan'))
        ->assertSee(__('dashboard.month_feb'))
        ->assertSee(__('dashboard.month_mar'))
        ->assertSee(__('dashboard.month_apr'))
        ->assertSee(__('dashboard.month_may'))
        ->assertSee(__('dashboard.month_jun'))
        ->assertSee(__('dashboard.month_jul'))
        ->assertSee(__('dashboard.month_aug'))
        ->assertSee(__('dashboard.month_sep'))
        ->assertSee(__('dashboard.month_oct'))
        ->assertSee(__('dashboard.month_nov'))
        ->assertSee(__('dashboard.month_dec'));
});

it('classifies insurance with Seguro prefix as entity insurance, not affiliation', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(3);

    $entity = Entity::factory()->create();
    $subscription = MemberSubscription::factory()->forEntity($entity)->create();

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 780.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Seguro: Plano Seguro Anual',
        'total_value' => 780.00,
        'is_debit' => false,
    ]);

    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['entity_insurances'][4])->toBe(780.00)
        ->and($monthlyData['entity_affiliations'][4])->toBe(0.0);
});

it('classifies insurance with Insurance prefix as individual insurance, not affiliation', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(5);

    $individual = Individual::factory()->create();
    $subscription = MemberSubscription::factory()->forIndividual($individual)->create();

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 10.80,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Insurance: Annual Insurance Plan',
        'total_value' => 10.80,
        'is_debit' => false,
    ]);

    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['individual_insurances'][6])->toBe(10.80)
        ->and($monthlyData['individual_affiliations'][6])->toBe(0.0);
});

it('keeps affiliation description under affiliations, not insurances', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(1);

    $entity = Entity::factory()->create();
    $subscription = MemberSubscription::factory()->forEntity($entity)->create();

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 150.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiação: Plano Anual Entidade',
        'total_value' => 150.00,
        'is_debit' => false,
    ]);

    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['entity_affiliations'][2])->toBe(150.00)
        ->and($monthlyData['entity_insurances'][2])->toBe(0.0);
});

it('classifies entity-paid individual insurance as individual_insurances, not entity_insurances', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(3);

    $individual = Individual::factory()->create();
    $subscription = MemberSubscription::factory()->forIndividual($individual)->create();

    // Document is paid by entity, but the subscription is for an individual
    $entity = Entity::factory()->create();
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 780.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Seguro: Plano Seguro Individual',
        'total_value' => 780.00,
        'is_debit' => false,
    ]);

    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['individual_insurances'][4])->toBe(780.00)
        ->and($monthlyData['entity_insurances'][4])->toBe(0.0);
});

it('classifies entity-paid individual affiliation as individual_affiliations, not entity_affiliations', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(4);

    $individual = Individual::factory()->create();
    $subscription = MemberSubscription::factory()->forIndividual($individual)->create();

    // Document is paid by entity, but the subscription is for an individual
    $entity = Entity::factory()->create();
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 200.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiação: Plano Anual Individual',
        'total_value' => 200.00,
        'is_debit' => false,
    ]);

    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['individual_affiliations'][5])->toBe(200.00)
        ->and($monthlyData['entity_affiliations'][5])->toBe(0.0);
});

it('invalidates monthly payments cache when document is marked as paid', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $year = now()->year;
    $cacheKey = "admin_monthly_payments_{$year}";

    $federation = Federation::factory()->create(['is_default_federation' => true]);
    $billingCacheKey = "entity_billing_total_{$federation->id}_{$year}";

    $entity = Entity::factory()->create();
    $subscription = MemberSubscription::factory()->forEntity($entity)->create();

    // Create a paid document so cache has data
    $paidDoc = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 100.00,
        'created_at' => now()->startOfYear()->addMonths(1),
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $paidDoc->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiacao: Plano Anual',
        'total_value' => 100.00,
        'is_debit' => false,
    ]);

    // Render once to populate the cache
    Livewire::test(MonthlyPaymentsTable::class);
    expect(Cache::has($cacheKey))->toBeTrue();

    // Simulate entity billing cache being populated
    Cache::put($billingCacheKey, 'billing-data', 3600);
    expect(Cache::has($billingCacheKey))->toBeTrue();

    // Create a pending document (cache still stale)
    $pendingDoc = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 200.00,
        'created_at' => now()->startOfYear()->addMonths(1),
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $pendingDoc->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiacao: Plano Anual Extra',
        'total_value' => 200.00,
        'is_debit' => false,
    ]);

    // Cache should still exist (pending doc does not invalidate it)
    expect(Cache::has($cacheKey))->toBeTrue();

    // Mark the pending document as paid (triggers DocumentObserver)
    $pendingDoc->update(['status_class' => PaidDocumentState::class]);

    // Cache should have been invalidated by the observer (via DashboardCacheService)
    expect(Cache::has($cacheKey))->toBeFalse()
        ->and(Cache::has($billingCacheKey))->toBeFalse();

    // Re-render: fresh query should now include the new payment
    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['entity_affiliations'][2])->toBe(300.00);
});

it('invalidates cache when document is created directly as paid', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $year = now()->year;
    $cacheKey = "admin_monthly_payments_{$year}";

    $entity = Entity::factory()->create();
    $subscription = MemberSubscription::factory()->forEntity($entity)->create();

    // Render once to populate the cache with empty data
    Livewire::test(MonthlyPaymentsTable::class);
    expect(Cache::has($cacheKey))->toBeTrue();

    // Create a document directly as paid (triggers DocumentObserver::created)
    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 150.00,
        'created_at' => now()->startOfYear()->addMonths(2),
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiacao: Plano Anual',
        'total_value' => 150.00,
        'is_debit' => false,
    ]);

    // Cache should have been invalidated by the created observer
    expect(Cache::has($cacheKey))->toBeFalse();

    // Re-render: fresh query should include the new payment
    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['entity_affiliations'][3])->toBe(150.00);
});

it('excludes debit entries from all categories', function () {
    actingAs($this->adminUser);
    Cache::flush();

    $month = now()->startOfYear()->addMonths(1);

    $entity = Entity::factory()->create();
    $subscription = MemberSubscription::factory()->forEntity($entity)->create();

    $document = Document::factory()->create([
        'status_class' => PaidDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => -100.00,
        'created_at' => $month,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_type' => MemberSubscription::class,
        'owner_id' => $subscription->id,
        'description' => 'Filiação: Plano Anual',
        'total_value' => -100.00,
        'is_debit' => true,
    ]);

    $component = Livewire::test(MonthlyPaymentsTable::class);
    $monthlyData = $component->viewData('monthlyData');

    expect($monthlyData['entity_affiliations'][2])->toEqual(0)
        ->and($monthlyData['individual_affiliations'][2])->toEqual(0)
        ->and($monthlyData['entity_insurances'][2])->toEqual(0)
        ->and($monthlyData['individual_insurances'][2])->toEqual(0)
        ->and($monthlyData['others'][2])->toEqual(0);
});
