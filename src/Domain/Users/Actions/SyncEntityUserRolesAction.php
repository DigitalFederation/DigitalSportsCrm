<?php

namespace Domain\Users\Actions;

use App\Models\Role;
use App\Models\User;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class SyncEntityUserRolesAction
 *
 * This class is responsible for syncing entity-specific roles for users who are
 * administrators of entities. The roles are determined through the entity's active
 * licenses and their corresponding role mappings in the license_roles pivot table.
 *
 * Role assignments are determined by:
 * - Active licenses held by entities that the user administers
 * - Base entity-admin role for any user who administers an active entity
 * - Special roles like entity-international (manually assigned)
 *
 * Usage:
 * $action = new SyncEntityUserRolesAction();
 * $action->execute($user);
 *
 * @mixin \Domain\Users\Actions\SyncEntityUserRolesAction
 */
class SyncEntityUserRolesAction
{
    /**
     * List of admin roles that should be preserved during entity role syncing
     * These roles are not managed through the entity license system
     */
    private array $preservedAdminRoles = [
        'admin',
        'federation-admin',
        'association-sport-admin',
        'association-scientific-admin',
        'association-admin',
        'association-territorial-admin',
    ];

    /**
     * Execute the entity role syncing action.
     *
     * This method collects entity-specific roles from:
     * - Active licenses held by entities the user administers
     * - Base entity-admin role if user administers any active entity
     *
     * @param  User  $user  The user for whom to sync the entity roles.
     */
    public function execute(User $user): void
    {
        DB::beginTransaction();
        try {
            // Preserve existing admin roles that aren't entity-related
            $currentRoles = $user->getRoleNames();
            $adminRolesToPreserve = $currentRoles->intersect($this->preservedAdminRoles);

            // 1. Retrieve entities where the user is an administrator
            $entities = $user->entities()
                ->with([
                    'licenses' => function ($query) {
                        $query->where('status_class', ActiveLicenseAttributedState::class);
                    },
                ])
                ->get();

            $entityRolesToAssign = collect();

            if ($entities->isEmpty()) {
                // No entities administered, sync with only preserved admin roles
                $user->syncRoles($adminRolesToPreserve->toArray());

                Log::info('User has no administered entities, entity roles removed', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'preserved_roles' => $adminRolesToPreserve->toArray(),
                ]);

                DB::commit();

                return;
            }

            // 2. Add base entity-admin role if user administers any active entity
            $entityRolesToAssign->push('entity-admin');

            // 3. Collect roles from active licenses held by administered entities
            foreach ($entities as $entity) {
                foreach ($entity->licenses as $licenseAttributed) {
                    if ($licenseAttributed->status_class === ActiveLicenseAttributedState::class) {
                        // Get roles mapped to this license
                        $licenseRoles = DB::table('license_roles')
                            ->join('roles', 'license_roles.role_id', '=', 'roles.id')
                            ->where('license_roles.license_id', $licenseAttributed->license_id)
                            ->pluck('roles.name');

                        $entityRolesToAssign = $entityRolesToAssign->merge($licenseRoles);
                    }
                }
            }

            // 4. Combine entity roles with preserved admin roles and sync
            $finalRoles = $entityRolesToAssign->merge($adminRolesToPreserve)->unique();
            $user->syncRoles($finalRoles->toArray());

            DB::commit();

            Log::info('Entity user roles synced', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'entities_count' => $entities->count(),
                'entity_roles' => $entityRolesToAssign->unique()->toArray(),
                'preserved_admin_roles' => $adminRolesToPreserve->toArray(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error syncing entity user roles', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
