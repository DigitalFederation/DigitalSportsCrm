<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Compares the installed release against the latest published one.
 *
 * The application never contacts GitHub on its own — this command is the
 * explicit, operator-invoked exception, so a deployment only reaches out when
 * somebody (or their cron) asks it to. Nothing is sent but the request itself:
 * no deployment details, no identifiers.
 *
 * Exit codes are meant for monitoring: 0 up to date, 1 an update is available,
 * 2 the check could not be completed (offline, rate-limited, unexpected
 * payload). A failed check is deliberately distinct from "outdated", so a
 * flaky network does not page anyone about a release that does not exist.
 */
class CheckForUpdatesCommand extends Command
{
    protected $signature = 'version:check
                            {--repository= : GitHub "owner/repo" to check (default from config)}
                            {--timeout=10 : Seconds to wait for GitHub before giving up}';

    protected $description = 'Check whether a newer release of the platform has been published';

    public const UPDATE_AVAILABLE = 1;

    public const CHECK_FAILED = 2;

    public function handle(): int
    {
        $installed = (string) config('app.version');
        $repository = (string) ($this->option('repository') ?: config('app.update_check_repository'));

        if ($repository === '') {
            $this->error('No repository configured. Set UPDATE_CHECK_REPOSITORY or pass --repository=owner/repo.');

            return self::CHECK_FAILED;
        }

        $this->line("Installed version: <info>{$installed}</info>");

        try {
            $response = Http::timeout((int) $this->option('timeout'))
                ->withHeaders(['Accept' => 'application/vnd.github+json'])
                ->get("https://api.github.com/repos/{$repository}/releases/latest");
        } catch (Throwable $e) {
            $this->error('Could not reach GitHub: '.$e->getMessage());

            return self::CHECK_FAILED;
        }

        if (! $response->successful()) {
            $this->error("GitHub returned HTTP {$response->status()} for {$repository}.");

            return self::CHECK_FAILED;
        }

        $latest = ltrim((string) $response->json('tag_name'), 'v');

        if ($latest === '') {
            $this->error('GitHub did not return a release tag.');

            return self::CHECK_FAILED;
        }

        $this->line("Latest release:    <info>{$latest}</info>");

        // version_compare handles pre-release suffixes (1.3.0-rc.1 < 1.3.0) correctly.
        if (version_compare($installed, $latest, '>=')) {
            $this->info('This installation is up to date.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn("An update is available: {$installed} -> {$latest}");
        $this->line("Release notes: https://github.com/{$repository}/releases/tag/v{$latest}");
        $this->line('Upgrade guide: https://digitalfederation.github.io/DigitalSportsCrm/guides/upgrading');

        return self::UPDATE_AVAILABLE;
    }
}
