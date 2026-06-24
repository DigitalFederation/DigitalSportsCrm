<?php

namespace Domain\Licenses\Actions;

use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Database\Eloquent\Builder;

/**
 * @deprecated Use federation->licenses() relationship instead
 *
 * This action retrieves licenses based on the old membership plan system.
 * With the new federation_licenses pivot table, you should use:
 * Federation::find($federation_id)->licenses()->get()
 */
class GetLicensesFromFederationMembership
{
    public function __invoke(int $federation_id)
    {
        // New approach: Get licenses directly from federation relationship
        $federation = Federation::find($federation_id);

        if (! $federation) {
            return collect();
        }

        return $federation->licenses()->get();

        // Old approach (kept for reference - based on membership plans):
        // $licenses = License::whereHas('plans', function (Builder $query) use ($federation_id) {
        //     return $query->whereHas('memberships', function (Builder $query) use ($federation_id) {
        //         return $query->where(compact('federation_id'))
        //             ->where('status_class', ActiveMembershipState::class);
        //     });
        // })->get();
    }
}
