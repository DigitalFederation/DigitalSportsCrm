<?php

namespace Domain\Federations\Actions;

use Domain\Federations\Models\Federation;
use Domain\Memberships\States\ActiveMembershipState;

class HasActiveMembershipAction
{
    public function __invoke(Federation $federation): bool
    {
        $activeStatus = $federation->memberships()->where('status_class', ActiveMembershipState::class)->first();

        if (! empty($activeStatus)) {
            return true;
        }

        return false;
    }
}
