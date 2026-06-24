<?php

namespace App\Console;

use App\Models\BackupSetting;
use App\Services\DashboardCacheService;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('memberships:inform-expiration')->yearlyOn(12, 1, '00:00');
        $this->scheduledCommand($schedule, 'memberships:cancel-expiration')->yearly();
        // $schedule->command('exports:cleanup')->daily();
        $this->scheduledCommand($schedule, 'official-documents:suspend-expired')->daily();
        $this->scheduledCommand($schedule, 'command:ExpireLicenses')
            ->dailyAt('00:05') // Run shortly after midnight
            ->withoutOverlapping()
            ->onOneServer();

        $this->scheduledCommand($schedule, 'command:ExpireCertifications')
            ->dailyAt('00:10')
            ->withoutOverlapping()
            ->onOneServer();

        $this->scheduledCommand($schedule, 'command:ExpireMemberSubscriptions')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->onOneServer();

        $this->scheduledCommand($schedule, 'command:ExpireInsurances')
            ->dailyAt('00:20')
            ->withoutOverlapping()
            ->onOneServer();

        // Check diving license expirations daily
        $this->scheduledCommand($schedule, 'diving:check-license-expiration')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Send event application deadline notifications daily
        $this->scheduledCommand($schedule, 'event-applications:send-deadline-notifications')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Reconcile Moloni invoices - retry failed invoice generation
        // Runs twice daily to catch any documents that slipped through
        if (config('invoicing.providers.moloni.reconciliation.enabled', false)) {
            $this->scheduledCommand($schedule, 'moloni:reconcile')
                ->twiceDaily(6, 18) // 6 AM and 6 PM
                ->withoutOverlapping()
                ->onOneServer()
                ->runInBackground();
        }

        // Generate weekly Insurance reports (Sunday at 23:59)
        $this->scheduledCommand($schedule, 'reports:generate-weekly-insurance')
            ->weeklyOn(0, '23:59')
            ->withoutOverlapping()
            ->onOneServer();

        // Clear dashboard caches daily so data is fresh each morning
        $schedule->call(function () {
            app(DashboardCacheService::class)->invalidateAll();
        })->dailyAt('23:55')
            ->name('dashboard-cache-clear')
            ->withoutOverlapping()
            ->onOneServer();

        // Database backups (configurable via admin panel)
        try {
            $backupEnabled = BackupSetting::getValue('backup_enabled', true);
            $backupFrequency = BackupSetting::getValue('backup_frequency', 'daily');
            $backupTime = BackupSetting::getValue('backup_time', '02:00');
        } catch (\Throwable) {
            $backupEnabled = true;
            $backupFrequency = 'daily';
            $backupTime = '02:00';
        }

        if (config('services.scheduler_heartbeat.url')) {
            $this->scheduledCommand($schedule, 'scheduler:heartbeat')
                ->name('scheduler-heartbeat')
                ->everyFiveMinutes();
        }

        if (config('services.scheduler.summary_email')) {
            $this->scheduledCommand($schedule, 'scheduler:daily-summary')
                ->dailyAt('10:05')
                ->withoutOverlapping()
                ->onOneServer();
        }

        if ($backupEnabled) {
            $backupCommand = $this->scheduledCommand($schedule, 'backup:run --only-db')
                ->withoutOverlapping()
                ->onOneServer();

            match ($backupFrequency) {
                'twice_daily' => $backupCommand->twiceDaily(
                    (int) substr($backupTime, 0, 2),
                    ((int) substr($backupTime, 0, 2) + 12) % 24
                ),
                'every_six_hours' => $backupCommand->everySixHours(),
                'weekly' => $backupCommand->weeklyOn(1, $backupTime),
                default => $backupCommand->dailyAt($backupTime),
            };

            $this->scheduledCommand($schedule, 'backup:clean')
                ->dailyAt('03:00')
                ->withoutOverlapping()
                ->onOneServer();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Register a scheduled Artisan command with optional failure email alerts.
     *
     * @param  array<string, mixed>  $parameters
     */
    protected function scheduledCommand(Schedule $schedule, string $command, array $parameters = []): Event
    {
        $event = $schedule->command($command, $parameters);
        $failureEmail = config('services.scheduler.failure_email');

        if (is_string($failureEmail) && $failureEmail !== '') {
            $event->emailOutputOnFailure($failureEmail);
        }

        return $event;
    }
}
