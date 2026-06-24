<?php

namespace Domain\Individuals\Actions;

use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetectIfIndividualIsAssistantInstructorAction
{
    public function __invoke(Builder|HasMany $individual, ?int $professionalRoleId = null, ?string $role = 'INSTRUCTOR'): bool
    {
        if (isset($professionalRoleId)) {
            return $individual->whereHas('licenses', function (Builder $query) use ($professionalRoleId) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($professionalRoleId) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) use ($professionalRoleId) {
                                $query->select('id')
                                    ->where('id', $professionalRoleId);
                            });
                    });
            })->exists();
        } else {
            return $individual->whereHas('licenses', function (Builder $query) use ($role) {
                $query->select('id', 'model_type', 'model_id', 'license_id')
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($role) {
                        $query->select('id', 'professional_role_id')
                            ->whereHas('professionalRole', function (Builder $query) use ($role) {
                                $query->select('id', 'role')
                                    ->where('role', 'like', $role);
                            });
                    });
            })->exists();
        }
    }
}
