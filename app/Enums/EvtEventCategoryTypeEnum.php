<?php

namespace App\Enums;

enum EvtEventCategoryTypeEnum: string
{
    case competition = 'competition';
    case organization = 'organization';

    public static function toString($category): string
    {
        return match ($category) {
            'competition' => __('events.category.competition'),
            'organization' => __('events.category.organization'),
            default => ''
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public static function toTranslatedArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => self::toString($case->value)])
            ->all();
    }
}
