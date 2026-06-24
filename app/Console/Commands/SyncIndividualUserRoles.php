<?php

namespace App\Console\Commands;

use App\Jobs\SyncUserRolesJob;
use Domain\Individuals\Models\Individual;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncIndividualUserRoles extends Command
{
    protected $signature = 'users:sync-roles';

    protected $description = 'Sync roles for all individual users';

    public function handle()
    {
        $this->info('Starting to sync roles for individual users.');

        $individuals = Individual::with('user')->get();

        if ($individuals->isEmpty()) {
            $this->info('No individual users found for role syncing.');

            return;
        }

        foreach ($individuals as $individual) {
            if ($individual->user) {
                SyncUserRolesJob::dispatch($individual->user);
                Log::info('Job dispatched for individual sync roles: '.$individual->id);
                $this->info('Job dispatched for individual sync roles: '.$individual->id);
            }
        }

        $this->info('All individual users have been queued for role syncing.');
    }
}
