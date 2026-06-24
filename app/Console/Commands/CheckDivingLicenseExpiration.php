<?php

namespace App\Console\Commands;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDivingLicenseExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diving:check-license-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired diving licenses based on their validity period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking diving license expirations...');

        // Get active diving licenses
        $activeLicenses = LicenseAttributed::where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function ($query) {
                $query->whereHas('committee', function ($q) {
                    $q->where('code', 'DIVING');
                })
                    ->whereNotNull('interval')
                    ->whereNotNull('interval_unit');
            })
            ->with('license')
            ->get();

        $expiredCount = 0;
        $checkedCount = 0;

        foreach ($activeLicenses as $licenseAttributed) {
            $checkedCount++;

            // Calculate expiration date based on activation date and interval
            if (! $licenseAttributed->activated_at) {
                continue;
            }

            $activatedAt = $licenseAttributed->activated_at->copy();
            $interval = $licenseAttributed->license->interval;
            $intervalUnit = $licenseAttributed->license->interval_unit;

            // Calculate expiration date
            $expirationDate = match ($intervalUnit) {
                'days' => $activatedAt->addDays($interval),
                'months' => $activatedAt->addMonths($interval),
                'years' => $activatedAt->addYears($interval),
                default => null,
            };

            if (! $expirationDate) {
                continue;
            }

            // Check if license has expired
            if ($expirationDate->isPast()) {
                // Update license status to expired
                $licenseAttributed->status_class = ExpiredLicenseAttributedState::class;
                $licenseAttributed->current_term_ends_at = $expirationDate;
                $licenseAttributed->save();

                // Log the expiration
                activity('diving_license')
                    ->performedOn($licenseAttributed)
                    ->withProperties([
                        'expiration_date' => $expirationDate->toDateString(),
                        'interval' => $interval,
                        'interval_unit' => $intervalUnit,
                    ])
                    ->log('Diving license automatically expired');

                $expiredCount++;

                $this->warn("License {$licenseAttributed->id} expired on {$expirationDate->toDateString()}");
            }
        }

        $this->info("Checked {$checkedCount} licenses, {$expiredCount} expired");

        // Check for licenses expiring soon (within 30 days) and send notifications
        $this->checkExpiringLicenses();

        return Command::SUCCESS;
    }

    /**
     * Check for licenses expiring soon and send notifications
     */
    private function checkExpiringLicenses()
    {
        $this->info('Checking for licenses expiring soon...');

        $expiringLicenses = LicenseAttributed::where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function ($query) {
                $query->whereHas('committee', function ($q) {
                    $q->where('code', 'DIVING');
                });
            })
            ->whereNotNull('current_term_ends_at')
            ->whereBetween('current_term_ends_at', [now(), now()->addDays(30)])
            ->get();

        foreach ($expiringLicenses as $license) {
            // Log upcoming expiration
            activity('diving_license')
                ->performedOn($license)
                ->withProperties([
                    'expiration_date' => $license->current_term_ends_at->toDateString(),
                    'days_until_expiration' => now()->diffInDays($license->current_term_ends_at),
                ])
                ->log('Diving license expiring soon notification');

            $this->info("License {$license->id} expires on {$license->current_term_ends_at->toDateString()}");

            // TODO: Send notification to entity about expiring license
            // This could be implemented with a notification class
        }

        $this->info("Found {$expiringLicenses->count()} licenses expiring within 30 days");
    }
}
