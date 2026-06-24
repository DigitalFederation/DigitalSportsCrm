<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;

class UpdateIndividualProfessionalRoleAction
{
    public function __invoke(string $code, string $individualId): bool|Individual
    {
        $professional_role = ProfessionalRole::where('code', $code)->first();
        if (! empty($professional_role)) {
            $individual = Individual::where('id', $individualId)->first();
            $individual->professionalRoles()->save($professional_role);

            return $individual;
        }

        return false;
    }
}
