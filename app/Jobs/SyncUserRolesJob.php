<?php

namespace App\Jobs;

use App\Models\User;
use Domain\Users\Actions\SyncUserRolesAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncUserRolesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $syncUserRolesAction = new SyncUserRolesAction;
        $syncUserRolesAction->execute($this->user);
        Log::info('Roles synced for user: '.$this->user->id);
    }
}
