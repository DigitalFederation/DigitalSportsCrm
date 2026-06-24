<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;

class AssociateIndividualToProfessionalRoleAction
{
    public function __invoke(Individual $individual, int|array $professional_role_id): Individual
    {
        $individual->professionalRoles()->attach($professional_role_id);

        return $individual;
    }
}
