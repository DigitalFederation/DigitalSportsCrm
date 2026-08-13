<?php

use App\Models\LegalPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // The import migration seeds version 1 for every type/locale from the legacy
    // templates, so tests start by clearing it and asserting on their own fixtures.
    LegalPage::query()->delete();
    LegalPage::flushCache();
});

function publishLegalPage(array $attributes = []): LegalPage
{
    return LegalPage::create(array_merge([
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'version' => 1,
        'title' => 'Privacy Policy',
        'body' => '<p>Published body</p>',
        'effective_date' => now()->toDateString(),
        'published_at' => now(),
    ], $attributes));
}

test('the public page serves the published version', function () {
    publishLegalPage(['body' => '<p>We process your data lawfully.</p>']);

    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertSee('We process your data lawfully.', false)
        ->assertSee('Privacy Policy', false);
});

test('an unpublished draft is never shown publicly', function () {
    publishLegalPage(['body' => '<p>Live text</p>']);

    LegalPage::create([
        'type' => LegalPage::TYPE_PRIVACY,
        'locale' => 'en',
        'version' => 2,
        'title' => 'Draft title',
        'body' => '<p>Secret draft text</p>',
        'published_at' => null,
    ]);

    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertSee('Live text', false)
        ->assertDontSee('Secret draft text', false);
});

test('falls back to another locale when the requested one has no version', function () {
    publishLegalPage(['locale' => 'en', 'body' => '<p>English fallback body</p>']);

    app()->setLocale('fr');

    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertSee('English fallback body', false);
});

test('placeholder tokens are replaced at render time', function () {
    publishLegalPage(['body' => '<p>Controller: {{federation_name}} at {{federation_address}}.</p>']);

    $response = $this->get(route('privacy-policy'))->assertOk();

    $response->assertDontSee('{{federation_name}}', false);
    $response->assertSee(e(config('branding.primary.name')), false);
});

test('a hostile branding value cannot inject markup through a placeholder', function () {
    config(['branding.primary.name' => '<script>alert(1)</script>']);

    publishLegalPage(['body' => '<p>Controller: {{federation_name}}</p>']);

    $html = $this->get(route('privacy-policy'))->assertOk()->getContent();

    expect($html)
        ->not->toContain('<script>alert(1)</script>')
        ->toContain('&lt;script&gt;');
});

test('the three legal routes are publicly reachable without authentication', function () {
    publishLegalPage(['type' => LegalPage::TYPE_TERMS, 'title' => 'Terms']);
    publishLegalPage(['type' => LegalPage::TYPE_PRIVACY, 'title' => 'Privacy']);
    publishLegalPage(['type' => LegalPage::TYPE_DATA_SHARING, 'title' => 'Data Sharing']);

    $this->get(route('terms-of-service'))->assertOk();
    $this->get(route('privacy-policy'))->assertOk();
    $this->get(route('data-sharing-policy'))->assertOk();
});
