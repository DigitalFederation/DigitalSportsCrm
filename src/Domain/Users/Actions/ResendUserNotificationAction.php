<?php

namespace Domain\Users\Actions;

use App\Notifications\CreatedUserNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResendUserNotificationAction
{
    public function execute($user)
    {
        try {
            $token = Str::random(60);

            // Insert the token into the password_resets table
            DB::table('password_resets')->insert([
                'email' => $user->email,
                'token' => Hash::make($token),  // Hash the token
                'created_at' => Carbon::now(),
            ]);

            $user->notify(new CreatedUserNotification($user, $token));

            Log::info('Resent user creation email for user: '.$user->id);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to resend user creation email for user: '.$user->id.'. Error: '.$e->getMessage());

            return false;
        }
    }
}
