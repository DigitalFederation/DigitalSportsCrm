<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\ActiveToCanceledTransition;
use Domain\Memberships\States\CanceledMembershipState;
use Domain\Memberships\States\PendingMembershipState;
use Domain\Memberships\States\PendingToCanceledTransition;
use Domain\Users\Actions\SyncUserFederationCommitteeAction;
use Exception;

class CancelMembershipAction
{
    /**
     * @throws Exception
     */
    public function __invoke(Membership $membership): Membership
    {
        switch ($membership->status_class) {
            case PendingMembershipState::class:
                $transiction = new PendingToCanceledTransition;
                $membership = $transiction($membership);
                break;
            case ActiveMembershipState::class:
                $transiction = new ActiveToCanceledTransition;
                $membership = $transiction($membership);
                break;
            default:
                throw new Exception('Transition not allowed');
        }

        if ($membership->status_class == CanceledMembershipState::class) {
            $membership->cancelled_at = now();
            $membership->save();

            $syncFederationRolesAction = new SyncUserFederationCommitteeAction;
            $syncFederationRolesAction->execute($membership->federation()->first()->users()->first());

            activity('Membership')
                ->performedOn($membership)
                ->event('canceled')
                ->log('Membership: '.$membership->id.' canceled.');
        }

        return $membership;
    }
}
