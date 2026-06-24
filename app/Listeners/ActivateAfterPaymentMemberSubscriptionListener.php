<?php

namespace App\Listeners;

use App\Events\ActivateAfterPayment;
use Domain\Memberships\Actions\ActivateMemberSubscriptionAction;
use Domain\Memberships\Models\MemberSubscription;
use Exception;
use Illuminate\Support\Facades\Log;

class ActivateAfterPaymentMemberSubscriptionListener
{
    /**
     * Handle the event.
     */
    public function handle(ActivateAfterPayment $event): void
    {
        $activateMemberSubscription = new ActivateMemberSubscriptionAction;

        foreach ($event->models as $modelArray) {
            foreach ($modelArray as $model) {
                if ($model instanceof MemberSubscription) {
                    try {
                        $activateMemberSubscription($model->id);
                        info('MemberSubscription activated after payment: ' . $model->id);
                    } catch (Exception $e) {
                        // Log the error but don't halt the execution
                        Log::error('Error activating member subscription: ' . $e->getMessage(), [
                            'subscription_id' => $model->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }
}
