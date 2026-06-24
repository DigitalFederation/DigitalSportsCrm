<?php

namespace App\Enums;

enum EvtDisciplineEnrollmentTypeEnum: string
{
    case individual = 'individual';
    case team = 'team';
    case relay = 'relay';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function forForm(): array
    {
        return [
            'individual' => 'Individual',
            'team' => 'Team',
            'relay' => 'Relay',
        ];
    }

}
