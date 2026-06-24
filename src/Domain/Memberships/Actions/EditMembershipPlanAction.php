<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\MembershipPlanData;
use Domain\Memberships\Models\MembershipPlan;

class EditMembershipPlanAction
{
    public function __invoke(MembershipPlanData $data, int $id): bool
    {
        $plan = MembershipPlan::findOrFail($id);
        $plan->licenses()->sync($data->licenses);

        return $plan->update((array) $data);
    }
}
