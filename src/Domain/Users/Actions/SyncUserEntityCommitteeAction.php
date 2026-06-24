<?php

namespace Domain\Users\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SyncUserEntityCommitteeAction
{
    public function execute(User $user): void
    {
        // Per PM requirement: Entity licenses should NOT trigger automatic role changes
        // Entity user roles must be managed manually through admin interface only
        // This method is intentionally left as a no-op to preserve manual role assignments

        Log::info('SyncUserEntityCommitteeAction called but skipped - Entity roles are managed manually', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        // Do nothing - Entity roles should only be changed manually by administrators

    }
}
