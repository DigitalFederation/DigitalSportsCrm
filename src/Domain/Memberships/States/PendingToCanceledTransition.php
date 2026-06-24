<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\Membership;
use Exception;

class PendingToCanceledTransition
{
    /**
     * @throws Exception
     */
    public function __invoke(Membership $membership): Membership
    {
        if ($membership->status_class != PendingMembershipState::class) {
            throw new Exception('Transition not allowed');
        }

        $membership->status_class = CanceledMembershipState::class;
        $membership->save();

        return $membership;
    }
}
