<?php

namespace App\Console\Commands;

use Domain\Licenses\Actions\ExpireLicenseAttributedAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Console\Command;

class ExpireLicenseAttributedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExpireLicenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to expire licenses when they are past their expiration date.';

    /**
     * Execute the console command.
     */
    public function handle(ExpireLicenseAttributedAction $expireLicense): int
    {
        // Only get active licenses that have expired
        $licenses = LicenseAttributed::where('current_term_ends_at', '<', now())
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->get();

        $this->info(sprintf(
            '🔍 Found %d active license(s) to expire',
            $licenses->count()
        ));

        \Log::info('Found licenses to expire', [
            'count' => $licenses->count(),
            'license_ids' => $licenses->pluck('id')->toArray(),
        ]);

        $successCount = 0;
        $failureCount = 0;

        $licenses->each(function ($license) use ($expireLicense, &$successCount, &$failureCount) {
            try {
                $this->info(sprintf(
                    '⚙️ Processing license ID: %s (Expired on: %s)',
                    $license->id,
                    $license->current_term_ends_at->toDateString()
                ));

                // Add owner validation
                if (! $license->owner()->exists()) {
                    $this->warn(sprintf(
                        '⚠️  License ID: %s has no owner - skipping',
                        $license->id
                    ));

                    return;
                }

                $expireLicense($license);
                $successCount++;

                $this->info(sprintf(
                    '✅ Successfully expired license ID: %s',
                    $license->id
                ));
            } catch (\Exception $e) {
                $failureCount++;
                $this->error(sprintf(
                    '❌ Failed to expire license ID: %s - Error: %s',
                    $license->id,
                    $e->getMessage()
                ));
            }
        });

        $this->info('Finished license expiration process');
        \Log::info('Completed license expiration process', [
            'total_processed' => $licenses->count(),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return Command::SUCCESS;
    }
}
