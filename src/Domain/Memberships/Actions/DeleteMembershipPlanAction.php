<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\MembershipPlan;
use Exception;

class DeleteMembershipPlanAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $id)
    {
        $membershipPlan = MembershipPlan::where('id', $id)->with('memberships')->first();

        if (count($membershipPlan->memberships()->get()) === 0) {
            $deleted = $membershipPlan->delete();

            if ($deleted) {
                activity('MembershipPlan')
                    ->performedOn($membershipPlan)
                    ->event('deleted')
                    ->log('MembershipPlan deleted');
            }

            return $deleted;
        }

        throw new Exception(__('This plan can\'t be deleted because it\'s associate to memberships.'), '802');
    }
}
