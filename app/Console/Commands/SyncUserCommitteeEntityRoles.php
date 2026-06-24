<?php

namespace App\Console\Commands;

use App\Models\User;
use Domain\Users\Actions\SyncUserEntityCommitteeAction;
use Illuminate\Console\Command;

class SyncUserCommitteeEntityRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user-entity-committee-roles {userId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user roles based on the syncRolesWithUser criteria.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Get all users with their roles
        $userId = $this->argument('userId');
        if ($userId) {
            $user = User::where('id', $userId)->first();

            if (! $user) {
                $this->error('User not found');

                return;
            }

            $doSync = new SyncUserEntityCommitteeAction;
            $doSync->execute($user);

            $this->info('User roles synced successfully!');
        }
    }
}
