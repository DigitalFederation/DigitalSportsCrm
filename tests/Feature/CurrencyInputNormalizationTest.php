<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\AffiliationPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * An amount displayed as "1.234,56" must be accepted when typed back. Before
 * this, the fee fields validated as `numeric` and rejected a decimal comma, so
 * a non-eurozone operator could not enter the format the app had just shown.
 */
beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $this->admin = User::factory()->create(['group_id' => UserGroupEnum::ADMIN->value]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $this->federation = Federation::factory()->create();
});

function submitAffiliationPlan(array $overrides = []): array
{
    return array_merge([
        'federation_id' => test()->federation->id,
        'name' => 'Comma Fee Plan',
        'duration_months' => 12,
        'type' => 'individual',
        'vat_rate' => 23,
    ], $overrides);
}

it('accepts a fee typed with a decimal comma', function () {
    $response = $this->post(route('admin.affiliation-plans.store'), submitAffiliationPlan([
        'individual_fee' => '1.234,56',
        'entity_fee' => '250,00',
    ]));

    $response->assertSessionHasNoErrors();

    $plan = AffiliationPlan::where('name', 'Comma Fee Plan')->firstOrFail();

    expect((float) $plan->individual_fee)->toBe(1234.56)
        ->and((float) $plan->entity_fee)->toBe(250.0);
});

it('still accepts a fee typed with a decimal point', function () {
    $this->post(route('admin.affiliation-plans.store'), submitAffiliationPlan([
        'name' => 'Dot Fee Plan',
        'individual_fee' => '1234.56',
    ]))->assertSessionHasNoErrors();

    expect((float) AffiliationPlan::where('name', 'Dot Fee Plan')->firstOrFail()->individual_fee)
        ->toBe(1234.56);
});

it('accepts the amount exactly as the application displays it', function () {
    config([
        'currency.symbol' => 'R$',
        'currency.position' => 'before',
        'currency.space' => true,
        'currency.decimal_separator' => ',',
        'currency.thousands_separator' => '.',
    ]);

    $displayed = money(1234.56); // "R$ 1.234,56"

    $this->post(route('admin.affiliation-plans.store'), submitAffiliationPlan([
        'name' => 'Displayed Fee Plan',
        'individual_fee' => $displayed,
    ]))->assertSessionHasNoErrors();

    expect((float) AffiliationPlan::where('name', 'Displayed Fee Plan')->firstOrFail()->individual_fee)
        ->toBe(1234.56);
});

it('still rejects something that is not an amount', function () {
    $this->post(route('admin.affiliation-plans.store'), submitAffiliationPlan([
        'individual_fee' => 'not a number',
    ]))->assertSessionHasErrors('individual_fee');
});
