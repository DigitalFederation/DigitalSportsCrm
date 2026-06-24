<?php

namespace Domain\Users\Actions;

use App\Models\Committee;
use App\Models\User;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SyncUserFederationCommitteeAction
{
    /**
     * Pattern matching federation committee admin roles (e.g. federation-sport-admin).
     */
    private const FEDERATION_COMMITTEE_ROLE_PATTERN = '/^federation-[a-z]+-admin$/';

    public function execute(User $user): void
    {
        $committeeRoles = Committee::select('committee.id', 'committee.code')
            ->whereHas('membershipPlans', function (Builder $query) use ($user) {
                $query->whereHas('memberships', function (Builder $query) use ($user) {
                    $query->where('status_class', ActiveMembershipState::class)
                        ->whereHas('federation', function (Builder $query) use ($user) {
                            $query->whereHas('users', function (Builder $query) use ($user) {
                                $query->where('users.id', $user->id);
                            });
                        });
                })->orWhereHas('membershipsChilds', function (Builder $query) use ($user) {
                    $query->where('status_class', ActiveMembershipState::class)
                        ->whereHas('federation', function (Builder $query) use ($user) {
                            $query->whereHas('users', function (Builder $query) use ($user) {
                                $query->where('users.id', $user->id);
                            });
                        });
                });
            })->pluck('committee.code')->toArray();

        // Build the desired set of federation roles
        $desiredFederationRoles = collect(['federation-admin']);

        foreach ($committeeRoles as $committee) {
            $desiredFederationRoles->push('federation-' . strtolower($committee) . '-admin');
        }

        // Get the user's current roles
        $currentRoles = $user->getRoleNames();

        // Identify current federation roles (federation-admin + federation-{committee}-admin)
        $currentFederationRoles = $currentRoles->filter(function (string $role) {
            return $role === 'federation-admin'
                || preg_match(self::FEDERATION_COMMITTEE_ROLE_PATTERN, $role);
        });

        // Remove federation roles that are no longer applicable
        $rolesToRemove = $currentFederationRoles->diff($desiredFederationRoles);
        foreach ($rolesToRemove as $role) {
            $user->removeRole($role);
        }

        // Add new federation roles that the user doesn't have yet
        $rolesToAdd = $desiredFederationRoles->diff($currentRoles);
        foreach ($rolesToAdd as $role) {
            $user->assignRole($role);
        }

        if ($rolesToRemove->isNotEmpty() || $rolesToAdd->isNotEmpty()) {
            Log::info('Federation committee roles synced (non-destructive)', [
                'user_id' => $user->id,
                'added' => $rolesToAdd->values()->toArray(),
                'removed' => $rolesToRemove->values()->toArray(),
                'preserved_non_federation' => $currentRoles->diff($currentFederationRoles)->values()->toArray(),
            ]);
        }
    }
}
