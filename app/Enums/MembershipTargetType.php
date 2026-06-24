<?php

namespace App\Enums;

enum MembershipTargetType: string
{
    case INDIVIDUAL = 'individual';
    case ENTITY = 'entity';

    // Keep deprecated values for migration compatibility only
    /** @deprecated Will be removed after migration */
    case BOTH = 'both';
    /** @deprecated Will be removed after migration */
    case INDIVIDUAL_FROM_ENTITY = 'individual_from_entity';

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => __('Individual'),
            self::ENTITY => __('Entity'),
            self::BOTH => __('Both'),
            self::INDIVIDUAL_FROM_ENTITY => __('Individual from entity'),
        };
    }
}
