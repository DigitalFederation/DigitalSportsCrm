<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearEntityCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-entity {user? : The user ID to clear cache for (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear entity cache for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user');

        if ($userId) {
            // Clear cache for specific user
            $user = User::find($userId);
            if ($user) {
                $cacheKey = "user:{$user->id}:primary_entity";
                Cache::forget($cacheKey);
                $this->info("Cleared entity cache for user {$user->email}");
            } else {
                $this->error("User with ID {$userId} not found");
            }
        } else {
            // Clear entity cache for all entity users
            $entityUsers = User::where('group_id', 2)->get(); // 2 = ENTITY

            foreach ($entityUsers as $user) {
                $cacheKey = "user:{$user->id}:primary_entity";
                Cache::forget($cacheKey);
            }

            $this->info("Cleared entity cache for {$entityUsers->count()} entity users");
        }

        return Command::SUCCESS;
    }
}
