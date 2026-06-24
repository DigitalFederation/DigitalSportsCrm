<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon;

class LastLoggedIn
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Login $event)
    {
        // Update the last_login_at field for the user
        $event->user->last_login_at = Carbon::now();
        $event->user->save();
    }
}
