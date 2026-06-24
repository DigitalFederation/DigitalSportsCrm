<?php

namespace App\Console\Commands;

use App\Models\User;
use Domain\Users\Actions\SyncEntityUserRolesAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAllEntityUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:all-entity-user-roles 
                            {--dry-run : Show what would be done without making changes}
                            {--user= : Sync roles for a specific user ID}
                            {--chunk=100 : Number of users to process in each chunk}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync entity roles for all users who are entity administrators based on their entity licenses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificUserId = $this->option('user');
        $chunkSize = (int) $this->option('chunk');
        $detailed = $this->option('detailed');

        $this->info('Starting entity user roles synchronization...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE: No changes will be made to the database');
        }

        $syncAction = new SyncEntityUserRolesAction;

        try {
            if ($specificUserId) {
                // Sync specific user
                $user = User::findOrFail($specificUserId);
                $this->syncUserEntityRoles($user, $syncAction, $dryRun, $detailed);
                $this->info("Completed sync for user: {$user->email}");
            } else {
                // Sync all users who are entity administrators
                $this->syncAllEntityUsers($syncAction, $dryRun, $chunkSize, $detailed);
            }

            $this->info('Entity user roles synchronization completed successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error("Error during synchronization: {$e->getMessage()}");
            Log::error('SyncAllEntityUserRoles command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Sync entity roles for all users who are entity administrators
     */
    private function syncAllEntityUsers(SyncEntityUserRolesAction $syncAction, bool $dryRun, int $chunkSize, bool $detailed): void
    {
        // Get all users who are entity administrators
        $query = User::whereHas('entities')->with('entities');

        $totalUsers = $query->count();
        $this->info("Found {$totalUsers} users who are entity administrators");

        $processedCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();

        $query->chunk($chunkSize, function ($users) use ($syncAction, $dryRun, $detailed, &$processedCount, &$errorCount, $progressBar) {
            foreach ($users as $user) {
                try {
                    $this->syncUserEntityRoles($user, $syncAction, $dryRun, $detailed);
                    $processedCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("Failed to sync user {$user->email}: {$e->getMessage()}");
                    Log::error('Failed to sync entity roles for user', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine();

        $this->info("Processed: {$processedCount} users");
        if ($errorCount > 0) {
            $this->warn("Errors: {$errorCount} users failed to sync");
        }
    }

    /**
     * Sync entity roles for a specific user
     */
    private function syncUserEntityRoles(User $user, SyncEntityUserRolesAction $syncAction, bool $dryRun, bool $detailed): void
    {
        if ($detailed) {
            $this->line("Processing user: {$user->email}");
        }

        if ($dryRun) {
            // In dry run mode, just log what would be done
            $entities = $user->entities()->with('licenses')->get();
            $entityCount = $entities->count();
            $activeLicenseCount = $entities->sum(function ($entity) {
                return $entity->licenses->filter(function ($license) {
                    return $license->isActive();
                })->count();
            });

            if ($detailed) {
                $this->line("  - Administers {$entityCount} entities");
                $this->line("  - Entities have {$activeLicenseCount} active licenses");
                $this->line('  - Current roles: ' . implode(', ', $user->getRoleNames()->toArray()));
            }
        } else {
            // Actually sync the roles
            $syncAction->execute($user);

            if ($detailed) {
                $this->line('  - Synced roles: ' . implode(', ', $user->fresh()->getRoleNames()->toArray()));
            }
        }
    }
}
