<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Invoicing\Actions\SyncMoloniDataAction;
use Domain\Invoicing\Models\MoloniSetting;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Invoicing\Models\MoloniToken;
use Domain\Invoicing\Services\MoloniClient;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $this->admin = User::factory()->create([
        'group_id' => UserGroupEnum::ADMIN->value,
    ]);
    $this->admin->assignRole('admin');
});

test('admin can view moloni settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.index'))
        ->assertOk()
        ->assertViewIs('web.admin.moloni-settings.index');
});

test('non-admin cannot view moloni settings page', function () {
    $user = User::factory()->create([
        'group_id' => UserGroupEnum::INDIVIDUAL->value,
    ]);

    $this->actingAs($user)
        ->get(route('admin.moloni-settings.index'))
        ->assertForbidden();
});

test('settings page shows disconnected state when no token', function () {
    config(['invoicing.providers.moloni.enabled' => true]);
    config(['invoicing.providers.moloni.client_id' => 'test_id']);
    config(['invoicing.providers.moloni.client_secret' => 'test_secret']);

    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.index'))
        ->assertOk()
        ->assertSee(__('moloni.not_connected'));
});

test('settings page shows connected state when valid token exists', function () {
    config(['invoicing.providers.moloni.enabled' => true]);
    config(['invoicing.providers.moloni.client_id' => 'test_id']);
    config(['invoicing.providers.moloni.client_secret' => 'test_secret']);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.index'))
        ->assertOk()
        ->assertSee(__('moloni.connected'));
});

test('authorize redirects to moloni when credentials are configured', function () {
    config(['invoicing.providers.moloni.enabled' => true]);
    config(['invoicing.providers.moloni.client_id' => 'test_id']);
    config(['invoicing.providers.moloni.client_secret' => 'test_secret']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.authorize'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('api.moloni.pt');
});

test('authorize shows error when credentials not configured', function () {
    config(['invoicing.providers.moloni.client_id' => null]);
    config(['invoicing.providers.moloni.client_secret' => null]);

    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.authorize'))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('error');
});

test('callback handles error from moloni', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('error');
});

test('callback handles missing code', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.callback'))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('error');
});

test('disconnect removes token', function () {
    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.disconnect'))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('success');

    expect(MoloniToken::count())->toBe(0);
});

test('save configuration stores settings', function () {
    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.save'), [
            'document_set_id' => 1,
            'default_tax_id' => 2,
            'default_unit_id' => 3,
            'default_category_id' => 4,
            'payment_method_id' => 5,
        ])
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('success');

    expect(MoloniSetting::getValue('document_set_id'))->toEqual(1);
    expect(MoloniSetting::getValue('default_tax_id'))->toEqual(2);
});

test('save configuration validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.save'), [])
        ->assertSessionHasErrors([
            'document_set_id',
            'default_tax_id',
        ]);
});

test('settings page displays sync logs', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniSyncLog::create([
        'sync_type' => 'data_sync',
        'status' => 'success',
        'data' => ['count' => 10],
        'duration_ms' => 1500,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.index'))
        ->assertOk()
        ->assertSee(__('moloni.sync_completed_title'));
});

test('sync data action is called when syncing', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $mockAction = $this->mock(SyncMoloniDataAction::class);
    $mockAction->shouldReceive('__invoke')
        ->once()
        ->andReturn([
            'document_sets' => 5,
            'taxes' => 3,
            'units' => 4,
            'categories' => 2,
            'payment_methods' => 6,
        ]);

    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.sync-data'))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('success');
});

test('test connection calls moloni client', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('testConnection')
        ->once()
        ->andReturn(true);

    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.test'))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('success');
});

test('test connection shows error on failure', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $mockClient = $this->mock(MoloniClient::class);
    $mockClient->shouldReceive('testConnection')
        ->once()
        ->andReturn(false);

    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.test'))
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('error');
});

test('admin can save invoice generation rules', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.invoice-generation-rules'), [
            'enabled_types' => [
                'license' => '1',
                'membership' => '0',
                'member_subscription' => '1',
                'certification' => '0',
                'enrollment' => '0',
                'individual_enrollment' => '0',
                'athlete_enrollment' => '0',
                'insurance' => '1',
            ],
            'require_all' => '0',
        ])
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('success');

    $rules = MoloniSetting::getValue('invoice_generation_rules');

    expect($rules['enabled_detail_types']['license'])->toBeTrue();
    expect($rules['enabled_detail_types']['membership'])->toBeFalse();
    expect($rules['enabled_detail_types']['member_subscription'])->toBeTrue();
    expect($rules['require_all_details_enabled'])->toBeFalse();
});

test('admin can enable require all details option', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.moloni-settings.invoice-generation-rules'), [
            'enabled_types' => [
                'license' => '0',
                'membership' => '0',
                'member_subscription' => '1',
                'certification' => '0',
                'enrollment' => '0',
                'individual_enrollment' => '0',
                'athlete_enrollment' => '0',
                'insurance' => '1',
            ],
            'require_all' => '1',
        ])
        ->assertRedirect(route('admin.moloni-settings.index'))
        ->assertSessionHas('success');

    $rules = MoloniSetting::getValue('invoice_generation_rules');
    expect($rules['require_all_details_enabled'])->toBeTrue();
});

test('settings page displays invoice generation rules form', function () {
    config(['invoicing.providers.moloni.enabled' => true]);

    MoloniToken::create([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'access_token_expires_at' => now()->addHour(),
        'refresh_token_expires_at' => now()->addDays(14),
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.moloni-settings.index'))
        ->assertOk()
        ->assertSee(__('moloni.invoice_generation_rules'));
});
