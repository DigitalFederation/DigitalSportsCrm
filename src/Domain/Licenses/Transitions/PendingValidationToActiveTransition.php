<?php

namespace Domain\Licenses\Transitions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;

class PendingValidationToActiveTransition
{
    protected LicenseAttributed $licenseAttributed;

    public function __construct(LicenseAttributed $licenseAttributed)
    {
        $this->licenseAttributed = $licenseAttributed;
    }

    public function handle(): LicenseAttributed
    {
        $this->licenseAttributed->status_class = ActiveLicenseAttributedState::class;
        $this->licenseAttributed->activated_at = now();
        $this->licenseAttributed->save();

        // Sync user roles and permissions if it's an individual
        if ($this->licenseAttributed->owner_type === (new \Domain\Individuals\Models\Individual)->getMorphClass()) {
            $this->licenseAttributed->owner->syncRolesFromActiveLicenses();
            $this->licenseAttributed->owner->syncPermissionsFromActiveLicenses();
        }

        activity('license_attributed')
            ->performedOn($this->licenseAttributed)
            ->causedBy(auth()->user())
            ->withProperties([
                'transition' => 'pending_validation_to_active',
                'from' => PendingValidationLicenseAttributedState::class,
                'to' => ActiveLicenseAttributedState::class,
            ])
            ->log('License approved and activated');

        return $this->licenseAttributed;
    }
}
