<?php

namespace Domain\Federations\Enums;

class TerritorialAssociationCategory
{
    public static function name(): string
    {
        return __('federation.territorial_association');
    }

    public static function value(): string
    {
        return 'territorial_association';
    }
}
