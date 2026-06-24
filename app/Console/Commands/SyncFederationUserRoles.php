<?php

namespace App\Console\Commands;

use App\Models\User;
use Domain\Users\Actions\SyncUserFederationCommitteeAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFederationUserRoles extends Command
{
    protected $signature = 'federations:sync-roles';

    protected $description = 'Sync roles for all federation users based on their memberships';

    public function handle(SyncUserFederationCommitteeAction $syncAction)
    {
        $this->info('Starting to sync roles for federation users.');

        // Fetch all users associated with federations
        $federationUsers = User::whereHas('federations')->get();

        if ($federationUsers->isEmpty()) {
            $this->info('No federation users found for role syncing.');

            return;
        }

        foreach ($federationUsers as $user) {
            try {
                // Use the provided action to sync roles for the user
                $syncAction->execute($user);
                // Log::info('Roles synced for federation user: ' . $user->id);
                $this->info('Roles synced for federation user: ' . $user->id);
            } catch (\Exception $e) {
                Log::error('Error syncing roles for federation user: ' . $user->id . ' - ' . $e->getMessage());
                $this->error('Error syncing roles for federation user: ' . $user->id);
            }
        }

        $this->info('All federation users have been processed for role syncing.');
    }
}
