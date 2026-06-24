<?php

namespace Domain\Users\Actions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class SyncUserRolesAction
 *
 * This class is responsible for syncing roles for individual users based on their licenses,
 * certifications, and federation memberships. The roles are determined through the role
 * mapping tables (license_roles, certification_roles, and federation_roles) which provide
 * a flexible and configurable way to manage role assignments.
 *
 * Role assignments are determined by:
 * - Active licenses mapped through the license_roles pivot table
 * - Active certifications mapped through the certification_roles pivot table
 * - Federation memberships mapped through the federation_roles pivot table
 *
 * Usage:
 * $action = new SyncUserRolesAction();
 * $action->execute($user);
 *
 * @mixin \Domain\Users\Actions\SyncUserRolesAction
 */
class SyncUserRolesAction
{
    /**
     * List of admin roles that should be preserved during syncing
     * These roles are not managed through the role mapping system
     */
    private array $preservedAdminRoles = [
        'admin',
        'federation-admin',
        'association-sport-admin',
        'association-scientific-admin',
        'association-admin',
        'association-territorial-admin',
        // Entity roles are managed by SyncEntityUserRolesAction
    ];

    /**
     * Execute the role syncing action.
     *
     * This method collects roles from:
     * - Active licenses through license_roles relationship
     * - Active certifications through certification_roles relationship
     * - Federation memberships through federation_roles
     *
     * IMPORTANT: Admin roles are preserved to prevent accidental lockouts.
     *
     * @param  User  $user  The user for whom to sync the roles.
     */
    public function execute(User $user): void
    {
        try {
            // Check if user has any admin roles that need to be preserved
            $currentRoles = $user->getRoleNames();
            $adminRolesToPreserve = $currentRoles->intersect($this->preservedAdminRoles);

            if ($adminRolesToPreserve->isNotEmpty()) {
                Log::warning('Admin role preservation safeguard triggered during role sync', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'preserved_roles' => $adminRolesToPreserve->toArray(),
                    'reason' => 'Preventing accidental admin lockout',
                ]);
            }
            // 1. Retrieve the user's individuals with eager loading
            // IMPORTANT: Use withoutGlobalScopes() to avoid filtering by authenticated user's federation
            // This ensures we get ALL individuals for the target user, not just those visible to the
            // currently authenticated user (e.g., when a Federation admin removes an individual)
            $individuals = $user->individuals()
                ->withoutGlobalScopes()
                ->with([
                    'licenses' => function ($query) {
                        $query->where('status_class', \Domain\Licenses\States\ActiveLicenseAttributedState::class);
                    },
                    'certificationsAttributed' => function ($query) {
                        $query->where('status_class', \Domain\Certifications\States\ActiveCertificationAttributedState::class);
                    },
                    'individualFederations' => function ($query) {
                        $query->where('active', true);
                    },
                ])
                ->get();

            if ($individuals->isEmpty()) {
                // No individuals, but preserve admin roles if any
                if ($adminRolesToPreserve->isNotEmpty()) {
                    $user->syncRoles($adminRolesToPreserve->toArray());
                    Log::info('User has no individuals but admin roles preserved', [
                        'user_id' => $user->id,
                        'preserved_roles' => $adminRolesToPreserve->toArray(),
                    ]);
                } else {
                    // No individuals and no admin roles, clear all roles
                    $user->syncRoles([]);
                }

                return;
            }

            $roleIds = collect();

            // 2. Collect roles from active licenses through license_roles
            $activeLicenseIds = $individuals->flatMap->licenses
                ->filter->isActive()
                ->pluck('license_id')
                ->unique();

            if ($activeLicenseIds->isNotEmpty()) {
                $licenseRoles = DB::table('license_roles')
                    ->whereIn('license_id', $activeLicenseIds)
                    ->select('role_id', 'committee_id')
                    ->get();

                // Add license-based roles
                $roleIds = $roleIds->merge($licenseRoles->pluck('role_id'));
            }

            // 3. Collect roles from active certifications through certification_roles
            $activeCertificationIds = $individuals->flatMap->certificationsAttributed
                ->filter->isActive()
                ->pluck('certification_id')
                ->unique();

            if ($activeCertificationIds->isNotEmpty()) {
                $certificationRoles = DB::table('certification_roles')
                    ->whereIn('certification_id', $activeCertificationIds)
                    ->select('role_id', 'committee_id')
                    ->get();

                // Add certification-based roles
                $roleIds = $roleIds->merge($certificationRoles->pluck('role_id'));
            }

            // 4. Collect roles from federation memberships through federation_roles
            $activeFederationIds = $individuals->flatMap->individualFederations
                ->where('active', true)
                ->pluck('federation_id')
                ->unique();

            if ($activeFederationIds->isNotEmpty()) {
                // Get roles for specific federations and global federation roles (where federation_id is NULL)
                $federationRoles = DB::table('federation_roles')
                    ->where(function ($query) use ($activeFederationIds) {
                        $query->whereIn('federation_id', $activeFederationIds)
                            ->orWhereNull('federation_id');
                    })
                    ->where('requires_active_membership', true)
                    ->pluck('role_id');

                // Add federation-based roles
                $roleIds = $roleIds->merge($federationRoles);
            }

            // Also check for global federation roles that don't require active membership
            $globalFederationRoles = DB::table('federation_roles')
                ->whereNull('federation_id')
                ->where('requires_active_membership', false)
                ->pluck('role_id');

            $roleIds = $roleIds->merge($globalFederationRoles);

            // 5. Get unique role IDs and fetch the actual roles
            $uniqueRoleIds = $roleIds->unique()->filter();

            if ($uniqueRoleIds->isEmpty()) {
                // No roles from licenses/certifications/federations, but preserve admin roles if any
                if ($adminRolesToPreserve->isNotEmpty()) {
                    $user->syncRoles($adminRolesToPreserve->toArray());
                    Log::info('No mapped roles found but admin roles preserved', [
                        'user_id' => $user->id,
                        'preserved_roles' => $adminRolesToPreserve->toArray(),
                    ]);
                } else {
                    // No roles to sync and no admin roles
                    $user->syncRoles([]);
                }

                return;
            }

            // Fetch the role names from the database
            $roleNames = Role::whereIn('id', $uniqueRoleIds)->pluck('name');

            // 6. Combine with preserved admin roles
            $finalRoles = $roleNames->merge($adminRolesToPreserve)->unique();

            // Sync roles using Spatie's built-in method
            $user->syncRoles($finalRoles->toArray());

            Log::info('User roles synced', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'synced_roles_count' => $finalRoles->count(),
                'preserved_admin_roles' => $adminRolesToPreserve->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing user roles', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
