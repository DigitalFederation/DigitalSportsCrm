<?php

use App\Notifications\SchedulerDailySummaryNotification;
use Domain\Invoicing\Models\MoloniSyncLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

it('skips the scheduler daily summary when no email is configured', function () {
    Config::set('services.scheduler.summary_email', null);
    Notification::fake();

    $this->artisan('scheduler:daily-summary')
        ->expectsOutput('Scheduler summary email is not configured.')
        ->assertExitCode(Command::SUCCESS);

    Notification::assertNothingSent();
});

it('does not send an empty scheduler daily summary by default', function () {
    Config::set('services.scheduler.summary_email', 'admin@example.com');
    Config::set('services.scheduler.summary_send_empty', false);
    Notification::fake();

    $this->artisan('scheduler:daily-summary')
        ->expectsOutput('No scheduler summary activity found; email not sent.')
        ->assertExitCode(Command::SUCCESS);

    Notification::assertNothingSent();
});

it('sends the scheduler daily summary when maintenance activity exists', function () {
    Config::set('services.scheduler.summary_email', 'admin@example.com');
    Notification::fake();
    Storage::fake('r2-backups');

    Activity::query()->create([
        'log_name' => 'Certification',
        'description' => 'Certification expired.',
        'event' => 'expired',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    MoloniSyncLog::query()->create([
        'sync_type' => 'reconciliation',
        'status' => 'success',
        'data' => [
            'documents_processed' => 2,
            'success_count' => 2,
            'failed_count' => 0,
        ],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Storage::disk('r2-backups')->put('backup-test.zip', 'content');

    $this->artisan('scheduler:daily-summary')
        ->expectsOutput('Scheduler summary sent to admin@example.com.')
        ->assertExitCode(Command::SUCCESS);

    Notification::assertSentOnDemand(
        SchedulerDailySummaryNotification::class,
        function (SchedulerDailySummaryNotification $notification, array $channels, object $notifiable): bool {
            $payload = $notification->toArray($notifiable);

            return $notifiable->routes['mail'] === 'admin@example.com'
                && collect($payload['items'])->firstWhere('label', 'Certifications marked expired')['count'] === 1
                && collect($payload['items'])->firstWhere('label', 'Moloni invoices created by reconciliation')['count'] === 2
                && collect($payload['items'])->firstWhere('label', 'Database backups created')['count'] === 1;
        }
    );
});

it('ignores scheduler daily summary activity outside the requested window', function () {
    Config::set('services.scheduler.summary_email', 'admin@example.com');
    Notification::fake();

    Activity::query()->create([
        'log_name' => 'Certification',
        'description' => 'Certification expired.',
        'event' => 'expired',
        'created_at' => Carbon::now()->subHours(48),
        'updated_at' => Carbon::now()->subHours(48),
    ]);

    $this->artisan('scheduler:daily-summary --hours=24')
        ->expectsOutput('No scheduler summary activity found; email not sent.')
        ->assertExitCode(Command::SUCCESS);

    Notification::assertNothingSent();
});

it('can send an empty scheduler daily summary for testing', function () {
    Config::set('services.scheduler.summary_email', 'admin@example.com');
    Notification::fake();

    $this->artisan('scheduler:daily-summary --send-empty')
        ->expectsOutput('Scheduler summary sent to admin@example.com.')
        ->assertExitCode(Command::SUCCESS);

    Notification::assertSentOnDemand(SchedulerDailySummaryNotification::class);
});
