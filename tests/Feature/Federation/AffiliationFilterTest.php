<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ExpiredAffiliationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    Group::forceCreate([
        'id' => UserGroupEnum::FEDERATION->value,
        'name' => 'Federation',
        'code' => 'FEDERATION',
    ]);

    $this->federation = Federation::factory()->create([
        'parent_id' => null,
        'is_local' => false,
    ]);

    $this->user = User::factory()->create([
        'group_id' => UserGroupEnum::FEDERATION->value,
    ]);
    $this->user->federations()->attach($this->federation->id);

    $this->actingAs($this->user);
});

function createEntityAffiliation(
    Federation $federation,
    ?string $entityName = null,
    ?string $statusClass = null,
    ?AffiliationPlan $affiliationPlan = null,
    ?string $startDate = null,
    ?string $endDate = null,
): Affiliation {
    $entity = Entity::factory()->create(
        $entityName ? ['name' => $entityName] : []
    );

    $entity->federations()->attach($federation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $package = MembershipPackage::factory()->entity()->create();

    if ($affiliationPlan) {
        $package->affiliationPlans()->attach($affiliationPlan->id);
    }

    $subscription = MemberSubscription::factory()
        ->forEntity($entity)
        ->create([
            'membership_package_id' => $package->id,
        ]);

    return Affiliation::factory()
        ->forEntity($entity)
        ->create([
            'federation_id' => $federation->id,
            'member_subscription_id' => $subscription->id,
            'status_class' => $statusClass ?? ActiveAffiliationState::class,
            'start_date' => $startDate ?? now()->subMonth()->format('Y-m-d'),
            'end_date' => $endDate ?? now()->addYear()->format('Y-m-d'),
        ]);
}

test('no filter shows all entity affiliations', function () {
    $affiliation1 = createEntityAffiliation($this->federation, 'Club Alpha');
    $affiliation2 = createEntityAffiliation($this->federation, 'Club Beta');

    $response = $this->get(route('federation.entity-affiliations.index'));

    $response->assertOk();
    $response->assertSee('Club Alpha');
    $response->assertSee('Club Beta');
});

test('entity name filter returns matching affiliations only', function () {
    createEntityAffiliation($this->federation, 'Diving Club North');
    createEntityAffiliation($this->federation, 'Swimming Club South');

    $response = $this->get(route('federation.entity-affiliations.index', [
        'filter' => ['filter_entity_name' => 'Diving'],
    ]));

    $response->assertOk();
    $response->assertSee('Diving Club North');
    $response->assertDontSee('Swimming Club South');
});

test('status filter returns matching affiliations only', function () {
    createEntityAffiliation($this->federation, 'Active Club', ActiveAffiliationState::class);
    createEntityAffiliation($this->federation, 'Expired Club', ExpiredAffiliationState::class);

    $response = $this->get(route('federation.entity-affiliations.index', [
        'filter' => ['filter_status_class' => ActiveAffiliationState::class],
    ]));

    $response->assertOk();
    $response->assertSee('Active Club');
    $response->assertDontSee('Expired Club');
});

test('affiliation plan filter returns matching affiliations only', function () {
    $planA = AffiliationPlan::factory()->create([
        'federation_id' => $this->federation->id,
        'name' => 'Plan A',
    ]);
    $planB = AffiliationPlan::factory()->create([
        'federation_id' => $this->federation->id,
        'name' => 'Plan B',
    ]);

    createEntityAffiliation($this->federation, 'Club With Plan A', null, $planA);
    createEntityAffiliation($this->federation, 'Club With Plan B', null, $planB);

    $response = $this->get(route('federation.entity-affiliations.index', [
        'filter' => ['filter_affiliation_plan_id' => $planA->id],
    ]));

    $response->assertOk();
    $response->assertSee('Club With Plan A');
    $response->assertDontSee('Club With Plan B');
});

test('start date filter returns affiliations starting on or after the given date', function () {
    createEntityAffiliation(
        $this->federation,
        'Recent Club',
        startDate: now()->format('Y-m-d'),
    );
    createEntityAffiliation(
        $this->federation,
        'Old Club',
        startDate: now()->subYear()->format('Y-m-d'),
    );

    $response = $this->get(route('federation.entity-affiliations.index', [
        'filter' => ['filter_start_date' => now()->subWeek()->format('Y-m-d')],
    ]));

    $response->assertOk();
    $response->assertSee('Recent Club');
    $response->assertDontSee('Old Club');
});

test('end date filter returns affiliations ending on or before the given date', function () {
    createEntityAffiliation(
        $this->federation,
        'Short Term Club',
        endDate: now()->addMonth()->format('Y-m-d'),
    );
    createEntityAffiliation(
        $this->federation,
        'Long Term Club',
        endDate: now()->addYears(2)->format('Y-m-d'),
    );

    $response = $this->get(route('federation.entity-affiliations.index', [
        'filter' => ['filter_end_date' => now()->addMonths(6)->format('Y-m-d')],
    ]));

    $response->assertOk();
    $response->assertSee('Short Term Club');
    $response->assertDontSee('Long Term Club');
});

test('combined filters work together', function () {
    createEntityAffiliation(
        $this->federation,
        'Active Diving Club',
        ActiveAffiliationState::class,
        startDate: now()->format('Y-m-d'),
    );
    createEntityAffiliation(
        $this->federation,
        'Expired Swimming Club',
        ExpiredAffiliationState::class,
        startDate: now()->subYear()->format('Y-m-d'),
    );
    createEntityAffiliation(
        $this->federation,
        'Active Swimming Club',
        ActiveAffiliationState::class,
        startDate: now()->subYear()->format('Y-m-d'),
    );

    $response = $this->get(route('federation.entity-affiliations.index', [
        'filter' => [
            'filter_entity_name' => 'Diving',
            'filter_status_class' => ActiveAffiliationState::class,
            'filter_start_date' => now()->subWeek()->format('Y-m-d'),
        ],
    ]));

    $response->assertOk();
    $response->assertSee('Active Diving Club');
    $response->assertDontSee('Expired Swimming Club');
    $response->assertDontSee('Active Swimming Club');
});

test('index page passes activation dates to view', function () {
    createEntityAffiliation($this->federation, 'Test Club');

    $response = $this->get(route('federation.entity-affiliations.index'));

    $response->assertOk();
    $response->assertViewHas('activationDates');
    $response->assertViewHas('affiliationPlans');
});
