<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\Membership;
use Exception;

class PendingToActiveTransition
{
    /**
     * @throws Exception
     */
    public function __invoke(Membership $membership): Membership
    {
        if ($membership->status_class != PendingMembershipState::class) {
            throw new Exception('Transition not allowed');
        }
        $membership->status_class = ActiveMembershipState::class;
        $membership->save();

        return $membership;
    }
}
