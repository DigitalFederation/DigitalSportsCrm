<?php

namespace App\Listeners;

use App\Events\ActivateAfterPayment;
use Domain\Licenses\Actions\ActivateLicenseAttributedAction;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Facades\Log;

class ActivateAfterPaymentLicenseAttributedListener
{
    public function handle(ActivateAfterPayment $event): void
    {
        Log::info('ActivateAfterPaymentLicenseAttributedListener: Processing payment activation', [
            'models_count' => count($event->models),
            'model_types' => array_keys($event->models),
        ]);

        foreach ($event->models as $modelArray) {
            foreach ($modelArray as $model) {
                if ($model instanceof LicenseAttributed) {
                    Log::info('ActivateAfterPaymentLicenseAttributedListener: Activating license', [
                        'license_id' => $model->id,
                        'license_name' => $model->license_name,
                        'current_status' => $model->status_class,
                        'holder_name' => $model->holder_name,
                    ]);

                    // Create an instance of ActivateLicenseAttributedAction with required dependency
                    $calculateValidityDatesAction = new CalculateLicenseValidityDatesAction;
                    $activateAction = new ActivateLicenseAttributedAction($calculateValidityDatesAction);
                    // Invoke the action to update the license and execute related user actions
                    // Bypass payment check since this is triggered by a successful payment
                    $activateAction($model, null, true);

                    Log::info('ActivateAfterPaymentLicenseAttributedListener: License activated', [
                        'license_id' => $model->id,
                        'new_status' => $model->fresh()->status_class,
                    ]);
                }
            }
        }
    }
}
