<?php

namespace App\Jobs;

use App\Notifications\MembershipExpirationNotification;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InformExpirationMembershipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $time)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $memberships = Membership::whereDate('current_term_ends_at', '<', now())->where('status_class', ActiveMembershipState::class)->get();

        $memberships->each(function (Membership $membership) {
            $user = $membership->federation()->first()->users()->first();
            $user->notify(new MembershipExpirationNotification($membership, $this->time));
            \Laravel\Prompts\info($user->name.' was been informed that their membership '.$membership->name.' with id '.$membership->id.' will expire.');

            activity('Membership')
                ->performedOn($membership)
                ->event('inform cancelation')
                ->withProperties($membership->toArray())
                ->log($user->name.' was been informed that their membership '.$membership->name.' with id '.$membership->id.' will expire.');
        });
    }
}
