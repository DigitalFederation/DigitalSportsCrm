<?php

namespace App\Listeners;

use Domain\Individuals\Actions\DetectIfUserHasOneOfTypeOfGroupAction;
use Illuminate\Support\Facades\Auth;

class CheckIfUserHasOneOfTypeOfGroup
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (! empty(Auth::user()->group)) {
            $detect = new DetectIfUserHasOneOfTypeOfGroupAction;
            if (! $detect(Auth::user())) {
                Auth::logout();
            }
        }
    }
}
