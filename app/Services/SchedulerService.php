<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SchedulerService
{
    protected const LAST_RUN_CACHE_PREFIX = 'scheduler_last_run_';

    protected const CACHE_TTL_DAYS = 7;

    /**
     * Get all scheduled tasks with their details.
     */
    public function getScheduledTasks(): Collection
    {
        $schedule = app(Schedule::class);
        $events = $schedule->events();

        return collect($events)->map(function ($event) {
            $command = $this->extractCommand($event);
            $expression = $event->expression;

            return [
                'command' => $command,
                'signature' => $this->extractSignature($command),
                'description' => $this->getCommandDescription($command),
                'expression' => $expression,
                'human_readable' => $this->humanReadableExpression($expression),
                'next_run' => $this->getNextRunTime($expression),
                'last_run' => $this->getLastRunTime($command),
                'timezone' => $event->timezone ?? config('app.timezone'),
                'without_overlapping' => $event->withoutOverlapping ?? false,
                'run_in_background' => $event->runInBackground ?? false,
            ];
        })->values();
    }

    /**
     * Execute a scheduled task manually.
     * Only allows execution of commands that are registered in the scheduler.
     */
    public function runTask(string $signature): array
    {
        // Security: Validate signature exists in scheduled tasks to prevent arbitrary command execution
        $validSignatures = $this->getScheduledTasks()->pluck('signature')->toArray();

        if (! in_array($signature, $validSignatures, true)) {
            Log::warning('Attempted execution of unauthorized scheduled task', [
                'signature' => $signature,
                'user_id' => auth()->id(),
                'valid_signatures' => $validSignatures,
            ]);

            return [
                'success' => false,
                'output' => 'Command not found in scheduled tasks.',
                'exit_code' => 1,
            ];
        }

        try {
            $exitCode = Artisan::call($signature);
            $output = Artisan::output();

            // Record the manual run
            $this->recordTaskRun($signature, true);

            return [
                'success' => $exitCode === 0,
                'output' => $output ?: 'Command completed successfully.',
                'exit_code' => $exitCode,
            ];
        } catch (\Exception $e) {
            Log::error('Scheduled task manual execution failed', [
                'signature' => $signature,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'output' => $e->getMessage(),
                'exit_code' => 1,
            ];
        }
    }

    /**
     * Record that a task was run.
     */
    public function recordTaskRun(string $command, bool $manual = false): void
    {
        $key = self::LAST_RUN_CACHE_PREFIX . md5($command);
        Cache::put($key, [
            'time' => now(),
            'manual' => $manual,
        ], now()->addDays(self::CACHE_TTL_DAYS));
    }

    /**
     * Get the last run time for a task.
     */
    public function getLastRunTime(string $command): ?array
    {
        $key = self::LAST_RUN_CACHE_PREFIX . md5($command);
        $data = Cache::get($key);

        if ($data) {
            return [
                'time' => $data['time'],
                'manual' => $data['manual'] ?? false,
                'ago' => $data['time']->diffForHumans(),
            ];
        }

        return null;
    }

    /**
     * Get the next run time for a cron expression.
     */
    public function getNextRunTime(string $expression): Carbon
    {
        try {
            $cron = new CronExpression($expression);

            return Carbon::instance($cron->getNextRunDate());
        } catch (\Exception $e) {
            return now()->addYear();
        }
    }

    /**
     * Convert cron expression to human-readable format.
     */
    public function humanReadableExpression(string $expression): string
    {
        $common = [
            '* * * * *' => 'Every minute',
            '*/5 * * * *' => 'Every 5 minutes',
            '*/15 * * * *' => 'Every 15 minutes',
            '*/30 * * * *' => 'Every 30 minutes',
            '0 * * * *' => 'Every hour',
            '0 */2 * * *' => 'Every 2 hours',
            '0 */6 * * *' => 'Every 6 hours',
            '0 */12 * * *' => 'Every 12 hours',
            '0 0 * * *' => 'Daily at midnight',
            '0 0 * * 0' => 'Weekly on Sunday',
            '0 0 1 * *' => 'Monthly on the 1st',
            '0 0 1 1 *' => 'Yearly on January 1st',
        ];

        if (isset($common[$expression])) {
            return $common[$expression];
        }

        // Parse the expression for more complex patterns
        $parts = explode(' ', $expression);
        if (count($parts) !== 5) {
            return $expression;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        // Daily at specific time
        if ($day === '*' && $month === '*' && $weekday === '*') {
            if ($minute !== '*' && $hour !== '*') {
                return sprintf('Daily at %s:%s', str_pad($hour, 2, '0', STR_PAD_LEFT), str_pad($minute, 2, '0', STR_PAD_LEFT));
            }
        }

        return $expression;
    }

    /**
     * Extract command from event.
     */
    protected function extractCommand($event): string
    {
        if (isset($event->command)) {
            // Remove the php artisan prefix if present
            $command = $event->command;
            if (str_contains($command, 'artisan')) {
                $parts = explode('artisan', $command);
                $command = trim(end($parts));
                // Remove quotes
                $command = trim($command, "'\"");
            }

            return $command;
        }

        if (isset($event->description)) {
            return $event->description;
        }

        return 'Unknown command';
    }

    /**
     * Extract just the signature from a command.
     */
    protected function extractSignature(string $command): string
    {
        // Remove any arguments
        $parts = explode(' ', $command);

        return $parts[0];
    }

    /**
     * Get description for known commands.
     */
    protected function getCommandDescription(string $command): string
    {
        $descriptions = [
            'memberships:cancel-expiration' => 'Transitions memberships with expired terms to canceled state',
            'official-documents:suspend-expired' => 'Suspends official documents past their expiry date',
            'command:ExpireLicenses' => 'Expires active licenses that are past their end date',
            'diving:check-license-expiration' => 'Checks diving licenses for expiration and sends notifications',
            'event-applications:send-deadline-notifications' => 'Sends notifications for approaching event deadlines',
        ];

        $signature = $this->extractSignature($command);

        return $descriptions[$signature] ?? 'No description available';
    }
}
