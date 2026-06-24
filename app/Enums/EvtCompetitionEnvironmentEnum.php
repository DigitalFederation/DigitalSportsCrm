<?php

namespace App\Enums;

enum EvtCompetitionEnvironmentEnum: string
{
    case OPEN_WATER = 'OpenWater';
    case SWIMMING_POOL = 'SwimmingPool';
    case DEPTH = 'Depth';

    public static function toString($type): string
    {
        return match ($type) {
            self::OPEN_WATER->value => __('events.environment_options.OpenWater'),
            self::SWIMMING_POOL->value => __('events.environment_options.SwimmingPool'),
            self::DEPTH->value => __('events.environment_options.Depth'),
            default => ''
        };
    }
}
