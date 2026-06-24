<?php

namespace App\Listeners;

use Domain\Individuals\Actions\DetectIfIndividualIsInstructorAction;
use Illuminate\Support\Facades\Auth;

class CheckIfIndividualIsInstructor
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
     *
     * @param  object  $event
     */
    public function handle($event): void
    {
        if (! empty(Auth::user()->group) &&
            Auth::user()->group->code == 'INDIVIDUAL') {
            $detect = new DetectIfIndividualIsInstructorAction;
            if ($detect(Auth::user()->individuals())) {
                session(['is_instructor' => true]);
            }
        }
    }
}
