<?php

namespace Domain\Federations\Actions;

use App\Models\User;
use Domain\Federations\Models\Federation;

class AssociateUserToFederationAction
{
    public function __invoke(User $user, Federation $federation, string|array $roleName): Federation
    {
        $user->federations()->sync($federation->id);
        $user->syncRoles($roleName);

        return $federation;
    }
}
