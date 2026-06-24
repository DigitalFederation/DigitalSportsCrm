<?php

namespace App\Listeners;

use App\Events\ActivateAfterPayment;
use App\Notifications\UserAlert;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\States\ActiveIndividualEnrollmentState;

class ActivateAfterPaymentIndividualEnrollmentListener
{
    public function handle(ActivateAfterPayment $event): void
    {
        logger()->debug('ActivateAfterPaymentIndividualEnrollmentListener');
        foreach ($event->models as $modelArray) {

            foreach ($modelArray as $model) {
                logger()->debug('ActivateAfterPaymentIndividualEnrollmentListener: model', [$model]);

                if ($model instanceof IndividualEnrollment) {
                    $model->load('individual');

                    $model->status_class = ActiveIndividualEnrollmentState::class;
                    $model->save();

                    if ($model->individual_id && $model->individual->user) {
                        // Notify the individual
                        $model->individual->user->notify(new UserAlert(__('notifications.event_registration_confirmed')));
                    }
                }
            }
        }
    }
}
