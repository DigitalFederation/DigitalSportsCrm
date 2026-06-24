<?php

namespace Domain\Users\Actions;

use Domain\Individuals\Actions\AssociateIndividualToProfessionalRoleAction;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;

class SyncUserRolesBasedOnLicenseAction
{
    public function __invoke(LicenseAttributed $license): void
    {
        if ($license->model_type === 'entity') {
            $user = $license->owner?->user()->first();
            if ($user) {
                $syncUserEntityCommitteeAction = new SyncUserEntityCommitteeAction;
                $syncUserEntityCommitteeAction->execute($user);
            }
        } else {
            $individual = Individual::find($license->model_id);
            if ($individual && $license->license->professional_role_id) {
                $professionalRoleAction = new AssociateIndividualToProfessionalRoleAction;
                $professionalRoleAction($individual, $license->license->professional_role_id);
            }

            $syncUserRolesAction = new SyncUserRolesAction;
            $syncUserRolesAction->execute($license->owner?->user()->first());
        }
    }
}
