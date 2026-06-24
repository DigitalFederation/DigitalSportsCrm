<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\MembershipPackageData;
use Domain\Memberships\Models\MembershipPackage;

class CreateMembershipPackageAction
{
    public function __invoke(MembershipPackageData $data): MembershipPackage
    {
        $package = MembershipPackage::create([
            'name' => $data->name,
            'description' => $data->description,
            'target_type' => $data->target_type,
            'distribution_methods' => $data->distribution_methods,
            'is_active' => $data->is_active,
        ]);

        $package->federations()->sync($data->federation_ids);
        $package->affiliationPlans()->sync($data->affiliation_plan_ids);
        $package->insurancePlans()->sync($data->insurance_plan_ids);

        return $package;
    }
}
