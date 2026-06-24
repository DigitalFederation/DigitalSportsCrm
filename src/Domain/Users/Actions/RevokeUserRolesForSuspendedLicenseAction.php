<?php

namespace Domain\Users\Actions;

use App\Models\User;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RevokeUserRolesForSuspendedLicenseAction
{
    public function execute(User $user, LicenseAttributed $suspendedLicense): void
    {
        // Determine the type of license holder using morph alias
        if ($suspendedLicense->model_type === 'individual') {
            $this->revokeRolesForIndividual($user, $suspendedLicense);
        } elseif ($suspendedLicense->model_type === 'entity') {
            $this->revokeRolesForEntity($user, $suspendedLicense);
        }

        // Additional logic if needed for other types or specific conditions
    }

    private function revokeRolesForIndividual(User $user, LicenseAttributed $suspendedLicense): void
    {
        // Detach the specified professional roles from the individual
        $individual = Individual::with('professionalRoles')->find($suspendedLicense->model_id);
        $individual->professionalRoles()->detach($suspendedLicense->license->professional_role_id);

        $professionalRoles = ProfessionalRole::where('id', $suspendedLicense->license->professional_role_id)->first();

        $rolePattern = strtolower($professionalRoles->role);
        $currentRoles = $user->getRoleNames();
        // Find all roles of the user that match this pattern
        $rolesToRemove = $user->roles->filter(function (Role $role) use ($rolePattern) {
            return Str::contains(strtolower($role->name), $rolePattern);
        })->pluck('name')->toArray();

        // Remove these roles from the user
        foreach ($rolesToRemove as $roleName) {
            $user->removeRole($roleName);
            activity()
                ->causedBy($user)
                ->log('Removed role '.$roleName.' from user '.$user->name.' ('.$user->id.')'.' due to license suspension');
        }

    }

    private function revokeRolesForEntity(User $user, LicenseAttributed $suspendedLicense): void
    {
        // Per PM requirement: Entity licenses should NOT trigger role changes
        // This method is now a no-op for entity licenses
        // Entity administrators maintain their roles regardless of license status

        \Log::info('Entity license suspended - no role changes per PM requirement', [
            'user_id' => $user->id,
            'license_id' => $suspendedLicense->id,
        ]);
    }
}
