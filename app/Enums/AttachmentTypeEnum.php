<?php

namespace App\Enums;

enum AttachmentTypeEnum: string
{
    case Standard = 'Standard';
    case Program = 'Program';
    case Form = 'Form';
    case TeachingMaterial = 'Teaching Material';
    case Manual = 'Manual';
    case Presentation = 'Presentation';
    case Table = 'Table';
    case Rules = 'Rules';
    case Procedures = 'Procedures';

    case Minutes = 'Minutes';
    case General = 'General';

    // method to String
    public function toString(): string
    {
        return match ($this) {
            self::Standard => __('Standard'),
            self::Program => __('Program'),
            self::Form => __('Form'),
            self::TeachingMaterial => __('Teaching Material'),
            self::Manual => __('Manual'),
            self::Presentation => __('Presentation'),
            self::Table => __('Table'),
            self::Rules => __('Rules'),
            self::Procedures => __('Procedures'),
            self::Minutes => __('Minutes'),
            self::General => __('General'),
        };
    }
}
