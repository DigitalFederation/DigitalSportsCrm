<?php

namespace App\Enums;

/**
 * Well-known committee codes for the default (diving example) deployment.
 *
 * The authoritative set of committees is deployment-defined in config/committees.php
 * and seeded into the `committee` table; enumerate committees via the Committee model
 * (e.g. Committee::all() / ->international() / ->national()) rather than this enum.
 * These cases remain as convenient, type-safe references to the default committees.
 */
enum CommitteeCodeEnum: string
{
    case Sport = 'SPORT';
    case Diving = 'DIVING';
    case Scientific = 'SCIENTIFIC';
    case DivingServices = 'DIVINGSERVICES';

    /**
     * Committee codes shown together with the given code, per config('committees.related').
     * Returns the code itself plus any related codes (e.g. DIVING -> [DIVING, SCIENTIFIC]).
     */
    public static function certificationFilterValues(string $code): array
    {
        $code = strtoupper($code);
        $related = config('committees.related', []);

        return array_values(array_unique([$code, ...($related[$code] ?? [])]));
    }

    /**
     * Backwards-compatible helper for the diving example's DIVING + related grouping.
     */
    public static function divingAndScientificValues(): array
    {
        return self::certificationFilterValues(self::Diving->value);
    }
}
