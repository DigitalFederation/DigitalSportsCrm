<?php

namespace App\Enums;

enum InsurancePlansTargetAudienceEnum: string
{
    case ENTITY = 'ENTITY';
    case INDIVIDUAL = 'INDIVIDUAL';

    public function toString(): string
    {
        return match ($this) {
            self::ENTITY => __('Entity'),
            self::INDIVIDUAL => __('Individual'),
        };
    }
}
