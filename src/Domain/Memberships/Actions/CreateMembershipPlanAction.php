<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\DataTransferObject\MembershipPlanData;
use Domain\Memberships\Models\MembershipPlan;

class CreateMembershipPlanAction
{
    public function __invoke(MembershipPlanData $data)
    {

        $plan = MembershipPlan::create((array) $data);
        $plan->licenses()->attach($data->licenses);

        return $plan;
    }
}
