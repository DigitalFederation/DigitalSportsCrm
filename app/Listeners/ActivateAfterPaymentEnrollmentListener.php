<?php

namespace App\Listeners;

use App\Enums\EvtEventPaymentStatusEnum;
use App\Events\ActivateAfterPayment;
use App\Notifications\UserAlert;
use Domain\EvtEvents\Actions\ActivateEnrollmentsAction;
use Domain\EvtEvents\Models\Enrollment;
use Illuminate\Support\Facades\Log;

class ActivateAfterPaymentEnrollmentListener
{
    protected ActivateEnrollmentsAction $activateEnrollmentsAction;

    public function __construct(ActivateEnrollmentsAction $activateEnrollmentsAction)
    {
        $this->activateEnrollmentsAction = $activateEnrollmentsAction;
    }

    /**
     * Handle the event.
     */
    public function handle(ActivateAfterPayment $event): void
    {
        foreach ($event->models as $modelArray) {
            foreach ($modelArray as $model) {

                if ($model instanceof Enrollment) {
                    try {
                        $model->payment_status = EvtEventPaymentStatusEnum::PAID->value;
                        $model->save();

                        // Activate related enrollments using the action
                        $this->activateEnrollmentsAction->execute($model->id);

                        // Notify the user
                        if ($model->user) {
                            $model->user->notify(new UserAlert(__('notifications.event_enrollment_confirmed')));
                        }
                    } catch (\Exception $e) {
                        Log::error('Error in ActivateEnrollmentAfterPaymentListener: ' . $e->getMessage());
                    }
                }
            }
        }
    }

}
