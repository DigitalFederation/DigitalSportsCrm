<?php

namespace Domain\Memberships\Actions;

use Domain\Federations\Actions\AssociateUserToFederationAction;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\LocalMembershipPlan;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/**
 * Handles the assignment of membership plans to a local federation.
 *
 * This action is responsible for associating membership plans with a local federation.
 * It ensures that the provided membership plans are valid and belong to the main federation
 * associated with the local federation. It also handles the removal of all associations
 * when an empty array of membership plan IDs is provided.
 *
 * Usage:
 * (new AssignLocalMembershipPlanAction())->execute($localFederationId, $membershipPlanIds);
 *
 * @mixin \Domain\Memberships\Actions\AssignLocalMembershipPlanAction
 */
class AssignLocalMembershipPlanAction
{
    /**
     * Assigns membership plans to a local federation.
     *
     * This method validates and associates the given membership plans with the specified local federation.
     * If an empty array is provided for membership plan IDs, it removes all existing associations.
     * It also ensures that the local federation is valid and throws an exception if not.
     * Additionally, it manages role assignments based on the associated membership plans.
     *
     * @param  int  $localFederationId  The ID of the local federation to which membership plans are to be assigned.
     * @param  array  $membershipPlanIds  An array of membership plan IDs to be associated with the local federation.
     *                                    Passing an empty array will remove all existing associations.
     *
     * @throws \Exception If the specified federation is not a local federation.
     * @throws ValidationException If no valid membership plans are found for the given IDs.
     */
    public function execute(int $localFederationId, array $membershipPlanIds): void
    {
        $associateUserToFederation = new AssociateUserToFederationAction;

        // Validation to ensure it's a local federation
        $localFederation = Federation::findOrFail($localFederationId);
        if (! $localFederation->is_local) {
            throw new \Exception('This is not a local federation.');
        }

        // If the array is empty, it means we are removing all memberships
        if (empty($membershipPlanIds)) {
            LocalMembershipPlan::where('local_federation_id', $localFederationId)->delete();
            // Update roles for users in the local federation
            $users = $localFederation->users;
            foreach ($users as $user) {

                // ve
                $rolesToRemove = $user->roles->filter(function (Role $role) {
                    return preg_match('/^local-federation-.*-admin$/', $role->name);
                });

                $user->removeRole($rolesToRemove);

                // Ensure the user has the 'association-territorial-admin' role
                if (! $user->hasRole('association-territorial-admin')) {
                    $user->assignRole('association-territorial-admin');
                }
            }

            // Lets save on the Activity Log that all memberships and roles where removed
            activity()
                ->causedBy(auth()->user())
                ->performedOn($localFederation)
                ->log('All memberships and roles where removed from the local federation');

            return;
        }

        // Validation to ensure membership plans belong to the main federation
        $mainFederationId = $localFederation->parent_id;
        $validMembershipPlanIds = MembershipPlan::whereIn('id', $membershipPlanIds)
            ->whereHas('memberships', function ($query) use ($mainFederationId) {
                $query->where('federation_id', $mainFederationId);
            })
            ->pluck('id')
            ->toArray();

        if (empty($validMembershipPlanIds)) {
            Log::error('No valid membership plans found for the given IDs.');
            throw ValidationException::withMessages(['membership_plan_id' => 'No valid membership plans found for the given IDs.']);
        }

        DB::transaction(function () use ($localFederationId, $validMembershipPlanIds, $localFederation, $associateUserToFederation) {
            // Delete existing associations
            LocalMembershipPlan::where('local_federation_id', $localFederationId)->delete();

            // Create new associations
            $associations = [];
            $roles = [];
            foreach ($validMembershipPlanIds as $membershipPlanId) {
                $associations[] = [
                    'local_federation_id' => $localFederationId,
                    'membership_plan_id' => $membershipPlanId,
                ];

                $plan_committee = strtolower(MembershipPlan::with('committee')->find($membershipPlanId)->committee->code);
                if (! in_array("local-federation-{$plan_committee}-admin", $roles)) {
                    $roles[] = "local-federation-{$plan_committee}-admin";
                }
            }

            DB::table('local_membership_plan_associations')->insert($associations);

            // Assign roles to the first user of the federation
            if ($localFederation->users->isNotEmpty()) {
                $associateUserToFederation($localFederation->users->first(), $localFederation, $roles);
            }
        });
    }
}
