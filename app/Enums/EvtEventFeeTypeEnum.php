<?php

namespace App\Enums;

enum EvtEventFeeTypeEnum: string
{
    case PER_PERSON = 'PER_PERSON';
    case FLAT_FEE = 'FLAT_FEE';
    case PER_DISCIPLINE = 'PER_DISCIPLINE';
    case FREE = 'FREE';
    case EVENT_FEE = 'EVENT_FEE';

    public static function toString($type): string
    {
        return match ($type) {
            'PER_PERSON' => __('events.per_person_fee'),
            'FLAT_FEE' => __('events.flat_fee'),
            'PER_DISCIPLINE' => __('events.per_discipline_fee'),
            'EVENT_FEE' => __('events.event_fee'),
            default => __('events.free'),
        };
    }
}
