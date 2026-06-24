<?php

namespace App\Listeners;

use App\Events\ActivateAfterPayment;
use Domain\Memberships\Actions\ActivateMembershipAction;
use Domain\Memberships\Models\Membership;
use Exception;
use Illuminate\Support\Facades\Log;

class ActivateAfterPaymentMembershipListener
{
    /**
     * Handle the event.
     */
    public function handle(ActivateAfterPayment $event): void
    {
        $activateMembership = new ActivateMembershipAction;

        foreach ($event->models as $modelArray) {
            // Assuming each $modelArray contains only one Membership instance
            $model = $modelArray[0];
            if ($model instanceof Membership) {
                try {
                    $activateMembership($model->id);
                    info('Membership activated after payment: '.$model->id);
                } catch (Exception $e) {
                    // Log the error but don't halt the execution
                    Log::error('Error activating membership: ' . $e->getMessage());
                }
            }
        }

    }
}
