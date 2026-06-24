<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PingSchedulerHeartbeat extends Command
{
    protected $signature = 'scheduler:heartbeat
                            {--url= : Override the configured heartbeat URL}';

    protected $description = 'Ping an external scheduler heartbeat monitor';

    public function handle(): int
    {
        $url = $this->option('url') ?: config('services.scheduler_heartbeat.url');

        if (! $url) {
            $this->info('Scheduler heartbeat URL is not configured.');

            return self::SUCCESS;
        }

        try {
            $response = Http::timeout((int) config('services.scheduler_heartbeat.timeout', 5))
                ->retry(2, 1000)
                ->get($url);

            if ($response->failed()) {
                Log::warning('Scheduler heartbeat ping failed', [
                    'status' => $response->status(),
                ]);

                $this->error('Scheduler heartbeat ping failed.');

                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            Log::error('Scheduler heartbeat ping failed', [
                'error' => $e->getMessage(),
            ]);

            $this->error('Scheduler heartbeat ping failed.');

            return self::FAILURE;
        }

        $this->info('Scheduler heartbeat pinged successfully.');

        return self::SUCCESS;
    }
}
