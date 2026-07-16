<?php

use App\Models\Group;
use App\Models\SiteSetting;
use App\Models\User;
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

    SiteSetting::flushCache();
});

test('admin can change the installation currency', function () {
    $this->actingAs($this->admin);

    $this->put(route('admin.homepage-settings.update'), ['currency' => 'BRL'])
        ->assertRedirect(route('admin.homepage-settings.index'));

    SiteSetting::flushCache();
    expect(SiteSetting::get('currency'))->toBe('BRL');
});

test('an invalid currency code is rejected', function () {
    $this->actingAs($this->admin);

    $this->put(route('admin.homepage-settings.update'), ['currency' => 'XYZ'])
        ->assertSessionHasErrors('currency');
});

test('the settings form lists all supported currencies', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.homepage-settings.index'));

    $response->assertSuccessful();
    foreach (['EUR', 'BRL', 'CLP', 'MYR'] as $code) {
        $response->assertSee($code);
    }
});

test('a corrupted stored currency is ignored by the config overlay', function () {
    SiteSetting::set('currency', 'NOPE');
    SiteSetting::flushCache();

    $original = config('app.currency');

    // Re-run the overlay the way the provider does at boot.
    $provider = new \App\Providers\AppServiceProvider(app());
    $method = new ReflectionMethod($provider, 'applySiteSettingsOverrides');
    $method->invoke($provider);

    expect(config('app.currency'))->toBe($original);
});

test('a valid stored currency overrides the config at boot', function () {
    SiteSetting::set('currency', 'MYR');
    SiteSetting::flushCache();

    $provider = new \App\Providers\AppServiceProvider(app());
    $method = new ReflectionMethod($provider, 'applySiteSettingsOverrides');
    $method->invoke($provider);

    expect(config('app.currency'))->toBe('MYR');
});
