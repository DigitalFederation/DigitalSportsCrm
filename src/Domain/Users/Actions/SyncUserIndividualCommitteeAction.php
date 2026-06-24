<?php

namespace Domain\Users\Actions;

use App\Models\Committee;
use App\Models\User;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Illuminate\Database\Eloquent\Builder;

class SyncUserIndividualCommitteeAction
{
    public function execute(User $user)
    {
        $committeeRoles = Committee::select('committee.id', 'committee.code')
            ->whereHas('certifications', function (Builder $query) use ($user) {
                $query->whereHas('certificationsAttributed', function (Builder $query) use ($user) {
                    $query->where('status_class', ActiveCertificationAttributedState::class)
                        ->whereHas('individual', function (Builder $query) use ($user) {
                            $query->where('user_id', $user->id);
                        });
                });
            })->pluck('committee.code')->toArray();

        // Add the default role for the Invidividuals
        $committeeRoles[] = 'individual';

        // 5. Sync the user's roles with the retrieved role names
        $user->syncRoles($committeeRoles);
    }
}
