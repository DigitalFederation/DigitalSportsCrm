<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;

class SyncEntityLicensePermissionsAction
{
    public function execute(Entity $entity): void
    {
        $permissions = $this->getPermissionsBasedOnLicenses($entity);

        foreach ($entity->users as $user) {
            $user->syncPermissions($permissions);
        }
    }

    private function getPermissionsBasedOnLicenses(Entity $entity): array
    {
        $permissions = [];

        $licenses = LicenseAttributed::with('license.committee')->where('model_id', $entity->id)
            ->where('model_type', 'entity')
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->get();

        foreach ($licenses as $license) {
            $committeeCode = strtolower($license->license->committee->code);
            $permissions[] = "access-{$committeeCode}-committee";
        }

        return array_unique($permissions);
    }
}
