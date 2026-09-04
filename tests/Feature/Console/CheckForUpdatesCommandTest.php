<?php

use App\Console\Commands\CheckForUpdatesCommand;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('app.update_check_repository', 'DigitalFederation/DigitalSportsCrm');
});

function fakeLatestRelease(string $tag): void
{
    Http::fake([
        'api.github.com/*' => Http::response(['tag_name' => $tag], 200),
    ]);
}

it('reports up to date when the installed version matches the latest release', function () {
    config()->set('app.version', '1.2.0');
    fakeLatestRelease('v1.2.0');

    $this->artisan('version:check')
        ->expectsOutputToContain('This installation is up to date.')
        ->assertExitCode(0);
});

it('reports an update and exits non-zero when a newer release exists', function () {
    config()->set('app.version', '1.2.0');
    fakeLatestRelease('v1.3.0');

    $this->artisan('version:check')
        ->expectsOutputToContain('An update is available: 1.2.0 -> 1.3.0')
        ->assertExitCode(CheckForUpdatesCommand::UPDATE_AVAILABLE);
});

it('treats an installation ahead of the latest release as up to date', function () {
    // Running unreleased code from main: newer than the release, not outdated.
    config()->set('app.version', '1.3.0');
    fakeLatestRelease('v1.2.0');

    $this->artisan('version:check')->assertExitCode(0);
});

it('orders pre-releases below their final release', function () {
    config()->set('app.version', '1.3.0-rc.1');
    fakeLatestRelease('v1.3.0');

    $this->artisan('version:check')
        ->expectsOutputToContain('An update is available: 1.3.0-rc.1 -> 1.3.0')
        ->assertExitCode(CheckForUpdatesCommand::UPDATE_AVAILABLE);
});

it('distinguishes a failed check from an outdated installation', function () {
    config()->set('app.version', '1.2.0');
    Http::fake(['api.github.com/*' => Http::response('rate limited', 403)]);

    // Must not be UPDATE_AVAILABLE: a flaky network should never look like a release.
    $this->artisan('version:check')
        ->expectsOutputToContain('GitHub returned HTTP 403')
        ->assertExitCode(CheckForUpdatesCommand::CHECK_FAILED);
});

it('fails the check when GitHub cannot be reached', function () {
    config()->set('app.version', '1.2.0');
    Http::fake(fn () => throw new ConnectionException('offline'));

    $this->artisan('version:check')
        ->expectsOutputToContain('Could not reach GitHub')
        ->assertExitCode(CheckForUpdatesCommand::CHECK_FAILED);
});

it('fails the check when no repository is configured', function () {
    config()->set('app.update_check_repository', '');

    $this->artisan('version:check')
        ->expectsOutputToContain('No repository configured')
        ->assertExitCode(CheckForUpdatesCommand::CHECK_FAILED);
});

it('does not contact the network when no repository is configured', function () {
    config()->set('app.update_check_repository', '');
    Http::fake();

    $this->artisan('version:check');

    Http::assertNothingSent();
});
