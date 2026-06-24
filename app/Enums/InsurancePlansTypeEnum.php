<?php

namespace App\Enums;

enum InsurancePlansTypeEnum: string
{
    case PersonalAccident = 'personal_accident';
    case Liability = 'liability';
    case Equipment = 'equipment';
    case Travel = 'travel';
    case Other = 'other';

    public function toString(): string
    {
        return match ($this) {
            self::PersonalAccident => __('insurances.type_personal_accident'),
            self::Liability => __('insurances.type_liability'),
            self::Equipment => __('insurances.type_equipment'),
            self::Travel => __('insurances.type_travel'),
            self::Other => __('insurances.type_other'),
        };
    }
}
