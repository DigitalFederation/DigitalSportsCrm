<?php

namespace Domain\Users\Actions;

class GetUserTypeAction
{
    public static function execute($user)
    {
        return match ($user->group->code) {
            'INDIVIDUAL' => $user->individuals()->first(),
            'ENTITY' => $user->entities()->first(),
            'FEDERATION' => $user->federations()->first(),
            default => $user,
        };
    }
}
