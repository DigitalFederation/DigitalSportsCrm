<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\Membership;
use Exception;

class ActiveToExpiredTransition
{
    /**
     * @throws Exception
     */
    public function __invoke(Membership $membership): Membership
    {
        if ($membership->status_class != ActiveMembershipState::class) {
            throw new Exception('Transition not allowed');
        }

        $membership->status_class = ExpiredMembershipState::class;
        $membership->save();

        return $membership;
    }
}
