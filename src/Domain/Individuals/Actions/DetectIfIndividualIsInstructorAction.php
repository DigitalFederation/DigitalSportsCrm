<?php

namespace Domain\Individuals\Actions;

use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DetectIfIndividualIsInstructorAction
 *
 * This class is responsible for detecting if an individual has an instructor role.
 */
class DetectIfIndividualIsInstructorAction
{
    /**
     * Detect if an individual is an instructor based on their licenses and roles.
     *
     * @param  Builder|HasMany  $individual  The Eloquent query builder object or HasMany relation for the individual.
     * @param  int|null  $professionalRoleId  The ID of the professional role to check against. Optional.
     * @param  string|null  $role  The name of the role to check against. Optional, defaults to 'INSTRUCTOR'.
     * @return bool Returns true if the individual is an instructor, otherwise false.
     */
    public function __invoke(Builder|HasMany $individual, ?int $professionalRoleId = null, ?string $role = 'INSTRUCTOR'): bool
    {

        if (isset($professionalRoleId)) {
            return $individual->whereHas('licenses', function (Builder $query) use ($professionalRoleId) {
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->select('id', 'model_type', 'model_id', 'license_id')
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($professionalRoleId) {
                        $query->withoutGlobalScope(ExcludeInternationalScope::class)
                            ->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) use ($professionalRoleId) {
                                $query->select('id')
                                    ->where('id', $professionalRoleId);
                            });
                    });
            })->exists();
        } else {
            return $individual->whereHas('licenses', function (Builder $query) use ($role) {
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->select('id', 'model_type', 'model_id', 'license_id')
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($role) {
                        $query->withoutGlobalScope(ExcludeInternationalScope::class)
                            ->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) use ($role) {
                                $query->select('id', 'role')
                                    ->where('role', 'like', $role);
                            });
                    });
            })->exists();
        }
    }
}
