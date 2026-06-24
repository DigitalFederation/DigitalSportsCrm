<?php

namespace Domain\Federations\Enums;

class SportOrClassAssociationCategory
{
    public static function name(): string
    {
        return __('federation.sport_or_class_association');
    }

    public static function value(): string
    {
        return 'sport_or_class_association';
    }
}
