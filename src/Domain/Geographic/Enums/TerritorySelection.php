<?php

namespace Domain\Geographic\Enums;

enum TerritorySelection: string
{
    case NOT_LISTED = 'territory_not_listed';

    public static function isNotListed(mixed $value): bool
    {
        return $value === self::NOT_LISTED->value;
    }
}
