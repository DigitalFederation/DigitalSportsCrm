<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveToExpiredTransition;
use Domain\Users\Actions\SyncUserEntityCommitteeAction;
use Domain\Users\Actions\SyncUserRolesAction;

/**
 * @mixin \Domain\Licenses\Actions\ExpireLicenseAttributedAction
 */
class ExpireLicenseAttributedAction
{
    public function __invoke(LicenseAttributed $license): void
    {

        // Validate owner exists
        if (! $license->owner()->exists()) {
            \Log::warning('License has no owner', [
                'license_id' => $license->id,
                'model_type' => $license->model_type,
                'model_id' => $license->model_id,
            ]);

            return;
        }

        $activeToCanceled = new ActiveToExpiredTransition;
        $license = $activeToCanceled($license);
        $license->save();

        \Log::info('License expired successfully', [
            'license_id' => $license->id,
            'model_type' => $license->model_type,
            'model_id' => $license->model_id,
            'status_class' => $license->status_class,
        ]);
        activity('License')
            ->performedOn($license)
            ->event('expired')
            ->withProperties((array) $license)
            ->log('License expired.');
        if ($license->model_type == 'entity') {
            // Per PM requirement: Entity licenses should NOT trigger role changes
            // Only sync committee information, but do not modify user roles
            $syncFederationRolesAction = new SyncUserEntityCommitteeAction;
            $syncFederationRolesAction->execute($license->owner()->first()->users()->first());

            \Log::debug('Synced entity committee for expired license', [
                'license_id' => $license->id,
                'entity_id' => $license->model_id,
            ]);
        } else {
            // Individual licenses continue to trigger role changes as before
            $syncFederationRolesAction = new SyncUserRolesAction;
            $syncFederationRolesAction->execute($license->owner()->first()->user()->first());
            \Log::debug('Synced individual roles for expired license', [
                'license_id' => $license->id,
                'individual_id' => $license->model_id,
            ]);
        }
    }
}
