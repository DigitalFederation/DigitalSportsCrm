<?php

namespace App\Enums;

/**
 * Event enrollment role types:
 * - Individual
 * - Athlete
 * - Coach
 * - Technical Official (merged from Referee/Judge)
 * - Official (Team Officials)
 * - Staff
 */
enum EvtEventEnrollmentRoleEnum: string
{
    case INDIVIDUAL = 'INDIVIDUAL';
    case ATHLETE = 'ATHLETE';
    case COACH = 'COACH';
    case TECHNICAL_OFFICIAL = 'TECHNICAL_OFFICIAL';
    case OFFICIAL = 'OFFICIAL';
    case STAFF = 'STAFF';

    /**
     * @deprecated Use TECHNICAL_OFFICIAL instead. Kept for backward compatibility.
     */
    case REFEREE = 'REFEREE';

    public static function toString($type): string
    {
        return match ($type) {
            'INDIVIDUAL' => __('events.role_individual'),
            'ATHLETE' => __('events.role_athlete'),
            'COACH' => __('events.role_coach'),
            'TECHNICAL_OFFICIAL', 'REFEREE' => __('events.role_technical_official'),
            'OFFICIAL' => __('events.role_official'),
            'STAFF' => __('events.role_staff'),
            default => ''
        };
    }

    public static function toSlug($type): string
    {
        return match ($type) {
            'INDIVIDUAL' => 'individual',
            'ATHLETE' => 'athlete',
            'COACH' => 'coach',
            'TECHNICAL_OFFICIAL', 'REFEREE' => 'technical-official',
            'OFFICIAL' => 'official',
            'STAFF' => 'staff',
            default => ''
        };
    }

    public static function fromSlug(string $slug): ?self
    {
        return match ($slug) {
            'individual' => self::INDIVIDUAL,
            'athlete' => self::ATHLETE,
            'coach' => self::COACH,
            'technical-official', 'referee' => self::TECHNICAL_OFFICIAL,
            'official' => self::OFFICIAL,
            'staff' => self::STAFF,
            default => null
        };
    }

    public static function toArray(): array
    {
        // Exclude deprecated REFEREE case
        return array_map(
            fn ($role) => ['name' => $role->name, 'value' => $role->value],
            array_filter(self::cases(), fn ($case) => $case !== self::REFEREE)
        );
    }
}
