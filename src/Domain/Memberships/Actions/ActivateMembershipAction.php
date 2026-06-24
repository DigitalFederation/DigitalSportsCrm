<?php

namespace Domain\Memberships\Actions;

use App\Notifications\MembershipActivationNotification;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\PendingMembershipState;
use Domain\Users\Actions\SyncUserFederationCommitteeAction;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * @mixin \Domain\Memberships\Actions\ActivateMembershipAction
 */
class ActivateMembershipAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $id): void
    {
        $membership = Membership::findOrFail($id);

        if ($membership->status_class == PendingMembershipState::class) {

            $membership->status_class = ActiveMembershipState::class;
            $membership->activated_at = now();
            $membership->save();

            $membershipUsers = $membership->federation()->first()->users;
            foreach ($membershipUsers as $user) {
                $syncFederationRolesAction = new SyncUserFederationCommitteeAction;
                $syncFederationRolesAction->execute($user);

                Notification::send($user, new MembershipActivationNotification($membership));
                Log::info('Notification sent for membership activation.', ['user_id' => $user->id, 'membership_id' => $id]);
            }

        } else {
            Log::error(sprintf(__('Membership: %d is not in Pending state to be activated.'), $membership->id));
        }
    }
}
