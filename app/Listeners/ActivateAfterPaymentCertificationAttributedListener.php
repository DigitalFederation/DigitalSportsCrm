<?php

namespace App\Listeners;

use App\Events\ActivateAfterPayment;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Illuminate\Support\Facades\Log;

class ActivateAfterPaymentCertificationAttributedListener
{
    /**
     * Handle the event.
     */
    public function handle(ActivateAfterPayment $event): void
    {
        foreach ($event->models as $modelArray) {
            foreach ($modelArray as $model) {
                if ($model instanceof CertificationAttributed) {
                    // Only activate if currently in pending state
                    if ($model->status_class !== PendingCertificationAttributedState::class) {
                        Log::info('ActivateAfterPaymentCertificationAttributedListener: CertificationAttributed not in pending state', [
                            'certification_attributed_id' => $model->id,
                            'current_state' => $model->status_class,
                        ]);

                        continue;
                    }

                    // Activate the certification
                    $model->status_class = ActiveCertificationAttributedState::class;
                    $model->activated_at = now();

                    // Generate certification codes if not already set
                    if (! $model->national_code) {
                        $model->national_code = $this->generateNationalCode($model);
                    }

                    if ($model->certification->isInternationalCertification() && ! $model->international_code) {
                        $model->international_code = $this->generateInternationalCode($model);
                    }

                    $model->save();

                    Log::info('ActivateAfterPaymentCertificationAttributedListener: Certification activated after payment', [
                        'certification_attributed_id' => $model->id,
                        'national_code' => $model->national_code,
                        'international_code' => $model->international_code,
                    ]);

                    activity('Certification')
                        ->performedOn($model)
                        ->event('activated_after_payment')
                        ->log('Certification activated after payment');
                }
            }
        }
    }

    /**
     * Generate national certification code
     */
    private function generateNationalCode(CertificationAttributed $certificationAttributed): string
    {
        $federation = $certificationAttributed->federation;
        $year = now()->year;

        // Get the last certification number for this federation and year
        $lastCertification = CertificationAttributed::where('federation_id', $certificationAttributed->federation_id)
            ->whereYear('created_at', $year)
            ->whereNotNull('national_code')
            ->orderBy('id', 'desc')
            ->first();

        $sequentialNumber = 1;
        if ($lastCertification && preg_match('/\d+$/', $lastCertification->national_code, $matches)) {
            $sequentialNumber = intval($matches[0]) + 1;
        }

        return sprintf('%s-%d-%06d', $federation->code ?? 'XX', $year, $sequentialNumber);
    }

    /**
     * Generate International certification code
     */
    private function generateInternationalCode(CertificationAttributed $certificationAttributed): string
    {
        $year = now()->year;

        // Get the last international code for this year
        $lastCertification = CertificationAttributed::whereYear('created_at', $year)
            ->whereNotNull('international_code')
            ->orderBy('id', 'desc')
            ->first();

        $sequentialNumber = 1;
        if ($lastCertification && preg_match('/\d+$/', $lastCertification->international_code, $matches)) {
            $sequentialNumber = intval($matches[0]) + 1;
        }

        return sprintf('CMAS-%d-%08d', $year, $sequentialNumber);
    }
}
