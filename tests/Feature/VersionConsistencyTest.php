<?php

/**
 * Guards the single source of truth for the release version.
 *
 * `config/app.php` → `version` is what the sidebar and the Version & Changelog
 * page display. If it drifts from the newest released section in CHANGELOG.md,
 * installed instances report a version that does not correspond to any release —
 * which is exactly what makes "do I have the latest version?" unanswerable.
 */
function latestReleasedChangelogVersion(): string
{
    $changelog = file_get_contents(base_path('CHANGELOG.md'));

    // Released sections look like "## [1.2.0] — 2026-09-04"; "## [Unreleased]" is skipped.
    preg_match_all('/^## \[(\d+\.\d+\.\d+)\]/m', $changelog, $matches);

    expect($matches[1])->not->toBeEmpty('CHANGELOG.md has no released "## [x.y.z]" section.');

    return $matches[1][0];
}

it('reports a version that matches the newest CHANGELOG entry', function () {
    // A packager may pin a different string via APP_VERSION; only the repo default is guarded.
    if (env('APP_VERSION') !== null) {
        $this->markTestSkipped('APP_VERSION is overridden in this environment.');
    }

    expect(config('app.version'))->toBe(latestReleasedChangelogVersion());
});

it('has no competing version config file', function () {
    expect(file_exists(config_path('version.php')))->toBeFalse(
        'config/version.php duplicates config/app.php → version; remove it.'
    );
});
