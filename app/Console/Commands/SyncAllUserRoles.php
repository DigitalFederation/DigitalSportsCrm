<?php

namespace App\Console\Commands;

use App\Models\User;
use Domain\Users\Actions\SyncUserRolesAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAllUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:all-user-roles 
                            {--chunk-size=100 : Process users in chunks of this size}
                            {--dry-run : Show what would be done without making changes}
                            {--user-id= : Sync only a specific user by ID}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync roles for all users based on their licenses, certifications, and federation memberships';

    /**
     * Statistics tracking
     *
     * @var array
     */
    protected $stats = [
        'users_processed' => 0,
        'users_with_changes' => 0,
        'roles_added' => 0,
        'roles_removed' => 0,
        'errors' => 0,
        'skipped' => 0,
    ];

    /**
     * Dry run mode flag
     *
     * @var bool
     */
    protected $isDryRun = false;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->isDryRun = $this->option('dry-run');

        try {
            // Confirm before proceeding unless forced
            if (! $this->option('force') && ! $this->isDryRun && ! $this->confirmAction()) {
                $this->warn('Operation cancelled.');

                return 1;
            }

            $this->info($this->isDryRun ? '🔍 Starting DRY RUN...' : '🚀 Starting role sync...');
            $this->newLine();

            // Process specific user or all users
            if ($userId = $this->option('user-id')) {
                $this->processSpecificUser((int) $userId);
            } else {
                $this->processAllUsers();
            }

            $this->displaySummary();

            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Confirm the action with the user
     */
    protected function confirmAction(): bool
    {
        $userCount = $this->option('user-id')
            ? 1
            : User::count();

        $this->warn("⚠️  This will sync roles for {$userCount} user(s).");
        $this->warn('This action will modify user roles based on their current licenses and certifications.');

        return $this->confirm('Do you wish to continue?', false);
    }

    /**
     * Process a specific user
     */
    protected function processSpecificUser(int $userId): void
    {
        $user = User::with('roles')->find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return;
        }

        $this->info("Processing user: {$user->name} (ID: {$user->id})");
        $this->processUser($user);
    }

    /**
     * Process all users in chunks
     */
    protected function processAllUsers(): void
    {
        $chunkSize = (int) $this->option('chunk-size');
        $totalUsers = User::count();

        if ($totalUsers === 0) {
            $this->warn('No users found in the database.');

            return;
        }

        $this->info("Processing {$totalUsers} users in chunks of {$chunkSize}...");
        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        User::with('roles')->chunk($chunkSize, function ($users) use ($bar) {
            foreach ($users as $user) {
                $this->processUser($user);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    /**
     * Process a single user
     */
    protected function processUser(User $user): void
    {
        try {
            // Load roles relationship to avoid lazy loading
            $user->load('roles');

            // Get current roles for comparison
            $currentRoles = $user->roles->pluck('name')->sort()->values()->toArray();

            if ($this->isDryRun) {
                // In dry run mode, we need to simulate what would happen
                $this->simulateRoleSync($user, $currentRoles);
            } else {
                // Use database transaction for each user
                DB::transaction(function () use ($user, $currentRoles) {
                    $syncAction = new SyncUserRolesAction;
                    $syncAction->execute($user);

                    // Get new roles after sync
                    $user->refresh();
                    $user->load('roles');
                    $newRoles = $user->roles->pluck('name')->sort()->values()->toArray();

                    // Calculate changes
                    $this->calculateChanges($user, $currentRoles, $newRoles);
                });
            }

            $this->stats['users_processed']++;
        } catch (\Exception $e) {
            $this->stats['errors']++;

            $this->error("Error processing user {$user->id}: {$e->getMessage()}");

            Log::error('Error in sync:all-user-roles command', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Simulate role sync in dry run mode
     */
    protected function simulateRoleSync(User $user, array $currentRoles): void
    {
        // This is a simplified simulation - in a real scenario, you might want to
        // duplicate some of the SyncUserRolesAction logic to predict changes
        $this->comment("  [DRY RUN] Would process user {$user->id} ({$user->name})");
        $this->comment('  Current roles: ' . ($currentRoles ? implode(', ', $currentRoles) : 'none'));

        // For dry run, we can't know exact changes without executing the action
        // So we just indicate that the user would be processed
        $this->stats['users_processed']++;
    }

    /**
     * Calculate and track changes made to user roles
     */
    protected function calculateChanges(User $user, array $oldRoles, array $newRoles): void
    {
        $added = array_diff($newRoles, $oldRoles);
        $removed = array_diff($oldRoles, $newRoles);

        if (count($added) > 0 || count($removed) > 0) {
            $this->stats['users_with_changes']++;
            $this->stats['roles_added'] += count($added);
            $this->stats['roles_removed'] += count($removed);

            // Log detailed changes if verbose
            if ($this->output->isVerbose()) {
                $this->info("  User {$user->id} ({$user->name}):");
                if (count($added) > 0) {
                    $this->comment('    + Added: ' . implode(', ', $added));
                }
                if (count($removed) > 0) {
                    $this->comment('    - Removed: ' . implode(', ', $removed));
                }
            }
        }
    }

    /**
     * Display final summary
     */
    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Summary:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->table(
            ['Metric', 'Count'],
            [
                ['Users Processed', $this->stats['users_processed']],
                ['Users with Changes', $this->stats['users_with_changes']],
                ['Roles Added', $this->stats['roles_added']],
                ['Roles Removed', $this->stats['roles_removed']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($this->stats['errors'] > 0) {
            $this->warn("⚠️  {$this->stats['errors']} errors occurred during processing. Check logs for details.");
        }

        if ($this->isDryRun) {
            $this->newLine();
            $this->warn('🔍 This was a DRY RUN - no changes were made.');
        } else {
            $this->newLine();
            $this->info('✅ Role sync completed successfully!');
        }
    }
}
