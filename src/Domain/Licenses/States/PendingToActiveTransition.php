<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Models\LicenseAttributed;

class PendingToActiveTransition
{
    private CalculateLicenseValidityDatesAction $calculateValidityDatesAction;

    public function __construct(CalculateLicenseValidityDatesAction $calculateValidityDatesAction)
    {
        $this->calculateValidityDatesAction = $calculateValidityDatesAction;
    }

    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if ($licenseAttributed->status_class !== PendingLicenseAttributedState::class) {
            throw new \Exception('License must be in Pending state to activate');
        }

        $licenseAttributed->status_class = ActiveLicenseAttributedState::class;
        $licenseAttributed->activated_at = now();

        // Load the license relationship to check interval configuration
        $licenseAttributed->load('license');

        // Calculate validity dates based on license configuration
        if ($licenseAttributed->license && ! $licenseAttributed->current_term_starts_at) {
            $dates = $this->calculateValidityDatesAction->execute($licenseAttributed->license, $licenseAttributed->activated_at);
            $licenseAttributed->current_term_starts_at = $dates['start_date']->format('Y-m-d H:i:s');
            $licenseAttributed->current_term_ends_at = $dates['end_date']?->format('Y-m-d H:i:s');
        }

        $licenseAttributed->save();

        activity('License')
            ->performedOn($licenseAttributed)
            ->event('activated')
            ->log('License activated after payment confirmation');

        return $licenseAttributed;
    }
}
