<?php

namespace Domain\Entities\Actions;

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Users\Actions\SyncEntityUserRolesAction;

class AssociateUserToEntityAction
{
    public function __invoke(User $user, Entity $entity, string $roleName): Entity
    {
        $user->entities()->attach($entity->id);

        // Assign role directly using Spatie
        $user->assignRole($roleName);

        // Sync entity roles based on the entity's active licenses
        $syncEntityUserRolesAction = new SyncEntityUserRolesAction;
        $syncEntityUserRolesAction->execute($user);

        return $entity;
    }
}
