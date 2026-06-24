<?php

namespace App\Jobs;

use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\ActiveToExpiredTransition;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\error;

class CancelExpirationMembershipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(ActiveToExpiredTransition $toExpiredTransition): void
    {
        $memberships = Membership::whereDate('current_term_ends_at', '<', now())->where('status_class', ActiveMembershipState::class)->get();

        try {
            $memberships->each(function (Membership $membership) use ($toExpiredTransition) {
                $toExpiredTransition($membership);
                \Laravel\Prompts\info('Membership ' . $membership->id . ' was canceled by the expiration date.');

                activity('Membership')
                    ->performedOn($membership)
                    ->event('canceled')
                    ->withProperties($membership->toArray())
                    ->log('Membership canceled by expiration date. #' . $membership->id . ' - ' . $membership->name);
            });
        } catch (Exception $e) {
            error($e->getMessage());
            log::error($e->getMessage());
        }
    }
}
