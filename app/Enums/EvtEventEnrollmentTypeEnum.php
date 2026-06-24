<?php

namespace App\Enums;

/**
 * - Only Federations
 * - Only Individuals
 * - Only Clubs
 * - Only Federations and Clubs
 * - Federations, Clubs and Individuals
 */
enum EvtEventEnrollmentTypeEnum: string
{
    case only_federations = 'Only Federations';
    case only_entities = 'Only Clubs';
    case only_individuals = 'Only Individuals';
    case only_federations_and_entities = 'Only Federations and Clubs';
    case all = 'Federations, Clubs and Individuals';

    case only_federations_and_individuals = 'Only Federations and Individuals';

    public static function toString($type): string
    {
        return match ($type) {
            'only_federations' => self::only_federations->value,
            'only_entities' => self::only_entities->value,
            'only_individuals' => self::only_individuals->value,
            'only_federations_and_entities' => self::only_federations_and_entities->value,
            'all' => self::all->value,
            'only_federations_and_individuals' => self::only_federations_and_individuals->value,
            default => ''
        };
    }
}
