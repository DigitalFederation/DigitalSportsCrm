<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationsCenterService
{
    /**
     * Whitelisted commands that can be executed from the Operations Center.
     */
    protected array $allowedCommands = [
        // License Management
        'licenses:activate-paid' => [
            'name' => 'Activate Paid Licenses',
            'description' => 'Activate pending licenses with fully paid documents',
            'category' => 'license',
            'dangerous' => false,
        ],
        'command:ExpireLicenses' => [
            'name' => 'Expire Licenses',
            'description' => 'Expire active licenses that are past their end date',
            'category' => 'license',
            'dangerous' => false,
        ],

        // Membership Management
        'memberships:cancel-expiration' => [
            'name' => 'Cancel Expired Memberships',
            'description' => 'Transition memberships with expired terms to canceled state',
            'category' => 'membership',
            'dangerous' => false,
        ],
        'membership:inform-expiration' => [
            'name' => 'Inform Membership Expiration',
            'description' => 'Send membership expiration notifications to users',
            'category' => 'membership',
            'dangerous' => false,
        ],

        // Role Synchronization
        'sync:all-user-roles' => [
            'name' => 'Sync All User Roles',
            'description' => 'Sync roles for all users based on licenses, certifications, and memberships',
            'category' => 'sync',
            'dangerous' => false,
            'supports_dry_run' => true,
        ],
        'sync:individual-user-roles' => [
            'name' => 'Sync Individual User Roles',
            'description' => 'Sync roles for individual users',
            'category' => 'sync',
            'dangerous' => false,
        ],
        'federations:sync-roles' => [
            'name' => 'Sync Federation Roles',
            'description' => 'Sync federation committee roles for federation users',
            'category' => 'sync',
            'dangerous' => false,
        ],
        'sync:user-committee-entity-roles' => [
            'name' => 'Sync Entity Committee Roles',
            'description' => 'Sync committee roles for entity users',
            'category' => 'sync',
            'dangerous' => false,
        ],
        'sync:user-committee-federation-roles' => [
            'name' => 'Sync Federation Committee Roles',
            'description' => 'Sync committee federation roles',
            'category' => 'sync',
            'dangerous' => false,
        ],
        'sync:all-entity-user-roles' => [
            'name' => 'Sync All Entity User Roles',
            'description' => 'Sync roles for all entity users',
            'category' => 'sync',
            'dangerous' => false,
        ],

        // QR Code Generation
        'qr-code:entities-generate' => [
            'name' => 'Generate Entity QR Codes',
            'description' => 'Generate QR codes for all entities without codes',
            'category' => 'qr',
            'dangerous' => false,
        ],
        'qr-code:individuals-generate' => [
            'name' => 'Generate Individual QR Codes',
            'description' => 'Generate QR codes for all individuals without codes',
            'category' => 'qr',
            'dangerous' => false,
        ],
        'qr-code:certifications-generate' => [
            'name' => 'Generate Certification QR Codes',
            'description' => 'Generate QR codes for all certifications without codes',
            'category' => 'qr',
            'dangerous' => false,
        ],

        // Data Maintenance
        'documents:generate-missing-invoices' => [
            'name' => 'Generate Missing Invoices',
            'description' => 'Generate invoice numbers for paid documents missing them',
            'category' => 'maintenance',
            'dangerous' => false,
        ],
        'official-documents:suspend-expired' => [
            'name' => 'Suspend Expired Documents',
            'description' => 'Suspend official documents that are past their expiry date',
            'category' => 'maintenance',
            'dangerous' => false,
        ],
        'diving:check-license-expiration' => [
            'name' => 'Check Diving License Expiration',
            'description' => 'Check diving licenses for expiration and send notifications',
            'category' => 'maintenance',
            'dangerous' => false,
        ],
        'event-applications:send-deadline-notifications' => [
            'name' => 'Send Event Deadline Notifications',
            'description' => 'Send notifications for approaching event application deadlines',
            'category' => 'maintenance',
            'dangerous' => false,
        ],

        // Cache Management
        'cache:clear-entity' => [
            'name' => 'Clear Entity Cache',
            'description' => 'Clear entity cache for all users or a specific user',
            'category' => 'cache',
            'dangerous' => false,
        ],
        'menu-cache:clear' => [
            'name' => 'Clear Menu Cache',
            'description' => 'Clear cached menu data',
            'category' => 'cache',
            'dangerous' => false,
        ],
        'optimize:clear' => [
            'name' => 'Clear All Caches',
            'description' => 'Clear all application caches (config, route, view, events)',
            'category' => 'cache',
            'dangerous' => false,
        ],
    ];

    /**
     * Get queue statistics.
     */
    public function getQueueStats(): array
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        $processing = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->count();
        $batches = DB::table('job_batches')
            ->whereNull('finished_at')
            ->count();

        return [
            'pending' => $pending,
            'processing' => $processing,
            'failed' => $failed,
            'active_batches' => $batches,
        ];
    }

    /**
     * Get pending jobs from the queue.
     */
    public function getPendingJobs(int $limit = 50): Collection
    {
        return DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);

                return (object) [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'job_class' => $this->extractJobClass($payload),
                    'attempts' => $job->attempts,
                    'reserved_at' => $job->reserved_at ? Carbon::createFromTimestamp($job->reserved_at) : null,
                    'available_at' => Carbon::createFromTimestamp($job->available_at),
                    'created_at' => Carbon::createFromTimestamp($job->created_at),
                    'is_processing' => $job->reserved_at !== null,
                ];
            });
    }

    /**
     * Get failed jobs.
     */
    public function getFailedJobs(int $limit = 50): Collection
    {
        return DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);

                return (object) [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'job_class' => $this->extractJobClass($payload),
                    'exception' => $this->truncateException($job->exception),
                    'exception_full' => $job->exception,
                    'failed_at' => Carbon::parse($job->failed_at),
                ];
            });
    }

    /**
     * Retry a failed job.
     */
    public function retryJob(string $uuid): bool
    {
        try {
            Artisan::call('queue:retry', ['id' => [$uuid]]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to retry job', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a failed job.
     */
    public function deleteFailedJob(string $uuid): bool
    {
        return DB::table('failed_jobs')
            ->where('uuid', $uuid)
            ->delete() > 0;
    }

    /**
     * Purge all failed jobs.
     */
    public function purgeFailedJobs(): int
    {
        return DB::table('failed_jobs')->delete();
    }

    /**
     * Get active job batches.
     */
    public function getActiveBatches(int $limit = 20): Collection
    {
        return DB::table('job_batches')
            ->whereNull('finished_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($batch) {
                $progress = $batch->total_jobs > 0
                    ? round((($batch->total_jobs - $batch->pending_jobs) / $batch->total_jobs) * 100, 1)
                    : 0;

                return (object) [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'total_jobs' => $batch->total_jobs,
                    'pending_jobs' => $batch->pending_jobs,
                    'failed_jobs' => $batch->failed_jobs,
                    'completed_jobs' => $batch->total_jobs - $batch->pending_jobs - $batch->failed_jobs,
                    'progress' => $progress,
                    'created_at' => Carbon::createFromTimestamp($batch->created_at),
                    'cancelled_at' => $batch->cancelled_at ? Carbon::createFromTimestamp($batch->cancelled_at) : null,
                ];
            });
    }

    /**
     * Get completed job batches.
     */
    public function getCompletedBatches(int $limit = 20): Collection
    {
        return DB::table('job_batches')
            ->whereNotNull('finished_at')
            ->orderBy('finished_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($batch) {
                return (object) [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'total_jobs' => $batch->total_jobs,
                    'failed_jobs' => $batch->failed_jobs,
                    'completed_jobs' => $batch->total_jobs - $batch->failed_jobs,
                    'created_at' => Carbon::createFromTimestamp($batch->created_at),
                    'finished_at' => Carbon::createFromTimestamp($batch->finished_at),
                    'success' => $batch->failed_jobs === 0,
                ];
            });
    }

    /**
     * Cancel a job batch.
     */
    public function cancelBatch(string $batchId): bool
    {
        try {
            $batch = app(\Illuminate\Bus\BatchRepository::class)->find($batchId);
            if ($batch) {
                $batch->cancel();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to cancel batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get available whitelisted commands.
     */
    public function getAvailableCommands(): array
    {
        return $this->allowedCommands;
    }

    /**
     * Get commands grouped by category.
     */
    public function getCommandsByCategory(): array
    {
        $categories = [
            'license' => ['name' => 'License Management', 'commands' => []],
            'membership' => ['name' => 'Membership Management', 'commands' => []],
            'sync' => ['name' => 'Role Synchronization', 'commands' => []],
            'qr' => ['name' => 'QR Code Generation', 'commands' => []],
            'maintenance' => ['name' => 'Data Maintenance', 'commands' => []],
            'cache' => ['name' => 'Cache Management', 'commands' => []],
        ];

        foreach ($this->allowedCommands as $signature => $config) {
            $category = $config['category'];
            if (isset($categories[$category])) {
                $categories[$category]['commands'][$signature] = $config;
            }
        }

        return array_filter($categories, fn ($cat) => ! empty($cat['commands']));
    }

    /**
     * Check if a command is allowed.
     */
    public function isCommandAllowed(string $signature): bool
    {
        return isset($this->allowedCommands[$signature]);
    }

    /**
     * Execute an allowed command.
     */
    public function executeCommand(string $signature, array $parameters = []): array
    {
        if (! $this->isCommandAllowed($signature)) {
            return [
                'success' => false,
                'output' => 'Command not allowed.',
                'exit_code' => 1,
            ];
        }

        try {
            $exitCode = Artisan::call($signature, $parameters);
            $output = Artisan::output();

            return [
                'success' => $exitCode === 0,
                'output' => $output,
                'exit_code' => $exitCode,
            ];
        } catch (\Exception $e) {
            Log::error('Command execution failed', [
                'command' => $signature,
                'parameters' => $parameters,
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
     * Get system health status.
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'queue' => $this->checkQueueHealth(),
            'storage' => $this->checkStorageHealth(),
            'failed_jobs_alert' => $this->getFailedJobsAlert(),
        ];
    }

    /**
     * Check database health.
     */
    protected function checkDatabaseHealth(): array
    {
        try {
            DB::select('SELECT 1');

            return ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection failed'];
        }
    }

    /**
     * Check queue health.
     */
    protected function checkQueueHealth(): array
    {
        try {
            $connection = config('queue.default');

            return ['status' => 'ok', 'message' => "Driver: {$connection}"];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Queue check failed'];
        }
    }

    /**
     * Check storage health.
     */
    protected function checkStorageHealth(): array
    {
        try {
            $testFile = storage_path('app/.health_check');
            file_put_contents($testFile, 'test');
            unlink($testFile);

            return ['status' => 'ok', 'message' => 'Writable'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Not writable'];
        }
    }

    /**
     * Get failed jobs alert level.
     */
    protected function getFailedJobsAlert(): array
    {
        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            return ['status' => 'ok', 'message' => 'No failed jobs', 'count' => 0];
        }

        if ($count <= 5) {
            return ['status' => 'warning', 'message' => "{$count} failed job(s)", 'count' => $count];
        }

        return ['status' => 'error', 'message' => "{$count} failed jobs", 'count' => $count];
    }

    /**
     * Extract job class name from payload.
     */
    protected function extractJobClass(array $payload): string
    {
        if (isset($payload['displayName'])) {
            return class_basename($payload['displayName']);
        }

        if (isset($payload['data']['commandName'])) {
            return class_basename($payload['data']['commandName']);
        }

        return 'Unknown';
    }

    /**
     * Truncate exception message for display.
     */
    protected function truncateException(string $exception): string
    {
        $lines = explode("\n", $exception);

        return $lines[0] ?? 'Unknown error';
    }
}
