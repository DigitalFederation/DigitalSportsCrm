<?php

namespace Domain\Memberships\Actions;

use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Exception;

class CreateLocalMembershipAction
{
    /**
     * @throws Exception
     */
    public function __invoke(array $data): Membership
    {
        $membership = Membership::findOrFail($data['membership_id']);
        if (Federation::where(['parent_id' => $membership->federation_id, 'id' => $data['local_id']])->count() === 0) {
            throw new Exception('Local federation not found');
        }

        $membership = $membership->replicate();
        $membership->parent_id = $data['membership_id'];
        $membership->federation_id = $data['local_id'];
        $membership->save();

        return $membership;
    }
}
