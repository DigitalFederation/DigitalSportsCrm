<?php

use App\Models\LegalPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The upgrade-safety guarantee.
 *
 * An installation that customized its legal text did so by editing
 * lang/{locale}/legal.php or the legal Blade views — the only way that existed.
 * The import migration reads those files as they stand on the deployment being
 * upgraded, so nothing is silently replaced with generic text.
 *
 * These assertions run against whatever the migration produced during
 * RefreshDatabase, i.e. the real thing.
 */
test('the import migration publishes a version for every type and locale', function () {
    expect(LegalPage::count())->toBe(count(LegalPage::types()) * count(config('app.locales')));

    foreach (LegalPage::types() as $type) {
        foreach (config('app.locales') as $locale) {
            $page = LegalPage::where('type', $type)->where('locale', $locale)->first();

            expect($page)->not->toBeNull("missing {$type}/{$locale}")
                ->and($page->version)->toBe(1)
                ->and($page->published_at)->not->toBeNull()
                ->and(trim(strip_tags($page->body)))->not->toBe('');
        }
    }
});

test('imported content keeps the wording from the installation templates', function () {
    $page = LegalPage::where('type', LegalPage::TYPE_PRIVACY)->where('locale', 'en')->first();

    // Sections carried over from the legacy template, not a generic stub.
    expect(strip_tags($page->body))
        ->toContain('Responsible Entity')
        ->and(strlen($page->body))->toBeGreaterThan(1000);
});

test('imported content is tokenized so rebranding still propagates', function () {
    $page = LegalPage::where('type', LegalPage::TYPE_PRIVACY)->where('locale', 'en')->first();

    // The federation's literal name/address were swapped back to placeholders.
    expect($page->body)->toContain('{{federation_name}}');

    $rendered = $this->get(route('privacy-policy'))->assertOk()->getContent();

    expect($rendered)->not->toContain('{{federation_name}}');
});

test('imported content is sanitized on the way in', function () {
    foreach (LegalPage::all() as $page) {
        expect(strtolower($page->body))
            ->not->toContain('<script')
            ->not->toContain('javascript:')
            ->not->toContain('onerror');
    }
});

test('re-running the import neither duplicates nor overwrites', function () {
    $before = LegalPage::orderBy('id')->get()
        ->map->only(['id', 'type', 'locale', 'version', 'body'])->toArray();

    // Run the migration's up() a second time; the per-(type, locale) guard should
    // make it a no-op rather than inserting duplicates.
    $migration = require database_path('migrations/2026_08_13_100001_import_legacy_legal_content.php');
    $migration->up();

    $after = LegalPage::orderBy('id')->get()
        ->map->only(['id', 'type', 'locale', 'version', 'body'])->toArray();

    expect($after)->toEqual($before);
});

test('the import does not touch a type and locale that already has content', function () {
    $existing = LegalPage::where('type', LegalPage::TYPE_TERMS)->where('locale', 'de')->first();
    $existing->update(['body' => '<p>Hand edited by this federation.</p>']);

    $migration = require database_path('migrations/2026_08_13_100001_import_legacy_legal_content.php');
    $migration->up();

    expect($existing->fresh()->body)->toContain('Hand edited by this federation.')
        ->and(LegalPage::where('type', LegalPage::TYPE_TERMS)->where('locale', 'de')->count())->toBe(1);
});
