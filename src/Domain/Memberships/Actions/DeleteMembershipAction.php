<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\Membership;
use Exception;

class DeleteMembershipAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $id)
    {
        $membership = Membership::find($id);

        if ($membership->stateName() == 'pending') {
            $deleted = $membership->delete();

            if ($deleted) {
                activity('Membership')
                    ->performedOn($membership)
                    ->event('deleted')
                    ->log('Membership deleted');
            }

            return $deleted;
        }

        throw new Exception(__('This membership can\'t be deleted because it has already been activated.'), 802);
    }
}
