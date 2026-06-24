<?php

namespace Domain\Individuals\Actions;

use App\Models\User;

class DetectIfUserHasOneOfTypeOfGroupAction
{
    public function __invoke(User $user): bool
    {
        return match ($user->group->code) {
            'INDIVIDUAL' => $user->individuals()->exists(),
            'ENTITY' => $user->entities()->exists(),
            'FEDERATION' => $user->federations()->exists(),
            'ADMIN' => true,
            default => false,
        };
    }
}
