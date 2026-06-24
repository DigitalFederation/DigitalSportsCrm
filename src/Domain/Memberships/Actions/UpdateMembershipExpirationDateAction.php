<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\Membership;
use Illuminate\Support\Carbon;

class UpdateMembershipExpirationDateAction
{
    public function __invoke(Membership $membership, string $newDate): Membership
    {
        $membership->current_term_ends_at = Carbon::parse($newDate);
        $membership->save();

        activity('Membership')
            ->performedOn($membership)
            ->event('update expiration date')
            ->withProperty('new_date', $newDate)
            ->log('Membership expiration date updated');

        return $membership;
    }
}
