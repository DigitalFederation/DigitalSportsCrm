<?php

use App\Models\Group;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('admin can view the home page settings form', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.homepage-settings.index'));

    $response->assertSuccessful();
    $response->assertSee('app_name', false);
    $response->assertSee('hero_background', false);
});

test('guest is redirected away from home page settings', function () {
    $this->get(route('admin.homepage-settings.index'))->assertRedirect();
});

test('admin can update text settings', function () {
    $this->actingAs($this->admin);

    $response = $this->put(route('admin.homepage-settings.update'), [
        'app_name' => 'CBES Portal',
        'federation_name' => 'Confederação Brasileira',
        'federation_about' => 'Portal da CBES',
        'federation_address' => 'Rua Exemplo 1, São Paulo',
    ]);

    $response->assertRedirect(route('admin.homepage-settings.index'));

    SiteSetting::flushCache();
    expect(SiteSetting::get('app_name'))->toBe('CBES Portal');
    expect(SiteSetting::get('federation_address'))->toBe('Rua Exemplo 1, São Paulo');
});

test('support email is saved and drives the public footer support link', function () {
    $this->actingAs($this->admin);

    $this->put(route('admin.homepage-settings.update'), [
        'federation_support_email' => 'suporte@cbes.org.br',
    ])->assertRedirect(route('admin.homepage-settings.index'));

    SiteSetting::flushCache();
    expect(SiteSetting::get('federation_support_email'))->toBe('suporte@cbes.org.br');

    auth()->logout();
    $response = $this->get('/');
    $response->assertSee('mailto:suporte@cbes.org.br', false);
});

test('invalid support email is rejected', function () {
    $this->actingAs($this->admin);

    $this->put(route('admin.homepage-settings.update'), [
        'federation_support_email' => 'not-an-email',
    ])->assertSessionHasErrors('federation_support_email');
});

test('fields removed from the public page are no longer persisted', function () {
    $this->actingAs($this->admin);

    $this->put(route('admin.homepage-settings.update'), [
        'federation_phone' => '+55 11 0000 0000',
        'federation_email' => 'geral@cbes.org.br',
        'federation_website_url' => 'https://cbes.org.br',
    ])->assertRedirect(route('admin.homepage-settings.index'));

    SiteSetting::flushCache();
    expect(SiteSetting::get('federation_phone'))->toBeNull();
    expect(SiteSetting::get('federation_email'))->toBeNull();
    expect(SiteSetting::get('federation_website_url'))->toBeNull();
});

test('admin can upload hero background and logo', function () {
    Storage::fake('public');
    $this->actingAs($this->admin);

    $response = $this->put(route('admin.homepage-settings.update'), [
        'hero_background' => UploadedFile::fake()->image('hero.jpg', 1920, 1080),
        'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
    ]);

    $response->assertRedirect(route('admin.homepage-settings.index'));

    SiteSetting::flushCache();
    $hero = SiteSetting::get('hero_background_path');
    $logo = SiteSetting::get('logo_path');

    expect($hero)->toStartWith('storage/homepage/');
    expect($logo)->toStartWith('storage/homepage/');
    Storage::disk('public')->assertExists(substr($hero, strlen('storage/')));
    Storage::disk('public')->assertExists(substr($logo, strlen('storage/')));
});

test('admin can remove an uploaded image', function () {
    Storage::fake('public');
    $this->actingAs($this->admin);

    $this->put(route('admin.homepage-settings.update'), [
        'hero_background' => UploadedFile::fake()->image('hero.jpg'),
    ]);

    SiteSetting::flushCache();
    $stored = SiteSetting::get('hero_background_path');
    expect($stored)->not->toBeNull();

    $this->put(route('admin.homepage-settings.update'), [
        'remove_hero_background' => '1',
    ]);

    SiteSetting::flushCache();
    expect(SiteSetting::get('hero_background_path'))->toBeNull();
    Storage::disk('public')->assertMissing(substr($stored, strlen('storage/')));
});

test('empty setting falls back to the provided default', function () {
    SiteSetting::set('federation_support_email', null);
    SiteSetting::flushCache();

    expect(SiteSetting::get('federation_support_email', 'fallback@example.test'))
        ->toBe('fallback@example.test');
});
