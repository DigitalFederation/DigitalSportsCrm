<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Notifications\SchedulerDailySummaryNotification;
use Carbon\CarbonImmutable;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Reports\Models\GeneratedReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class SendSchedulerDailySummary extends Command
{
    protected $signature = 'scheduler:daily-summary
                            {--email= : Override the configured summary recipient}
                            {--hours=24 : Number of past hours to summarize}
                            {--send-empty : Send the email even when no activity is found}';

    protected $description = 'Email a digest of successful scheduler maintenance activity';

    public function handle(): int
    {
        $email = $this->option('email') ?: config('services.scheduler.summary_email');

        if (! $email) {
            $this->info('Scheduler summary email is not configured.');

            return self::SUCCESS;
        }

        $hours = max(1, (int) $this->option('hours'));
        $to = CarbonImmutable::now();
        $from = $to->subHours($hours);

        $summary = $this->buildSummary($from, $to);
        $hasActivity = collect($summary['items'])->contains(fn (array $item): bool => $item['count'] > 0);
        $sendEmpty = (bool) $this->option('send-empty') || (bool) config('services.scheduler.summary_send_empty', false);

        if (! $hasActivity && ! $sendEmpty) {
            $this->info('No scheduler summary activity found; email not sent.');

            return self::SUCCESS;
        }

        Notification::route('mail', $email)
            ->notify(new SchedulerDailySummaryNotification($from, $to, $summary));

        $this->info("Scheduler summary sent to {$email}.");

        return self::SUCCESS;
    }

    /**
     * @return array{items: array<int, array{label: string, count: int}>, notes: array<int, string>}
     */
    protected function buildSummary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $items = [
            ['label' => 'Licenses marked expired', 'count' => $this->activityCount($from, $to, 'License', 'expired')],
            ['label' => 'Certifications marked expired', 'count' => $this->activityCount($from, $to, 'Certification', 'expired')],
            ['label' => 'Member subscriptions marked expired', 'count' => $this->activityCount($from, $to, 'MemberSubscription', 'expired')],
            ['label' => 'Insurances marked expired', 'count' => $this->activityCount($from, $to, 'Insurance', 'expired')],
            ['label' => 'Memberships canceled by expiration date', 'count' => $this->descriptionCount($from, $to, 'Membership', 'Membership canceled by expiration date%')],
            ['label' => 'Official documents marked expired', 'count' => $this->activityCount($from, $to, 'OfficialDocument', 'expired')],
            ['label' => 'Diving licenses automatically expired', 'count' => $this->descriptionCount($from, $to, 'diving_license', 'Diving license automatically expired')],
            ['label' => 'Diving license expiry reminders logged', 'count' => $this->descriptionCount($from, $to, 'diving_license', 'Diving license expiring soon notification')],
            ['label' => 'Event application deadline notifications sent', 'count' => $this->descriptionCount($from, $to, 'event_application_deadline', 'Deadline notification sent')],
            ['label' => 'Event application deadline notification failures', 'count' => $this->descriptionCount($from, $to, 'event_application_deadline', 'Failed to send deadline notification')],
            ['label' => 'Database backups created', 'count' => $this->backupCount($from, $to)],
            ['label' => 'Moloni reconciliation runs', 'count' => $this->moloniReconciliationRuns($from, $to)],
            ['label' => 'Moloni invoices created by reconciliation', 'count' => $this->moloniReconciliationSum($from, $to, 'success_count')],
            ['label' => 'Moloni reconciliation failures', 'count' => $this->moloniReconciliationSum($from, $to, 'failed_count')],
            ['label' => 'Scheduled insurance reports queued', 'count' => $this->scheduledReportCount($from, $to)],
        ];

        return [
            'items' => $items,
            'notes' => [],
        ];
    }

    protected function activityCount(CarbonImmutable $from, CarbonImmutable $to, string $logName, string $event): int
    {
        return Activity::query()
            ->where('log_name', $logName)
            ->where('event', $event)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    protected function descriptionCount(CarbonImmutable $from, CarbonImmutable $to, string $logName, string $description): int
    {
        return Activity::query()
            ->where('log_name', $logName)
            ->where('description', 'like', $description)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    protected function backupCount(CarbonImmutable $from, CarbonImmutable $to): int
    {
        try {
            $disk = Storage::disk('r2-backups');

            return collect($disk->files('', true))
                ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
                ->filter(function (string $file) use ($disk, $from, $to): bool {
                    $modifiedAt = CarbonImmutable::createFromTimestamp($disk->lastModified($file));

                    return $modifiedAt->betweenIncluded($from, $to);
                })
                ->count();
        } catch (\Throwable $e) {
            Log::warning('Unable to count scheduler summary backups', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    protected function moloniReconciliationRuns(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return MoloniSyncLog::query()
            ->where('sync_type', 'reconciliation')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    protected function moloniReconciliationSum(CarbonImmutable $from, CarbonImmutable $to, string $key): int
    {
        return MoloniSyncLog::query()
            ->where('sync_type', 'reconciliation')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->sum(fn (MoloniSyncLog $log): int => (int) data_get($log->data, $key, 0));
    }

    protected function scheduledReportCount(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return GeneratedReport::query()
            ->whereNull('generated_by')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }
}
