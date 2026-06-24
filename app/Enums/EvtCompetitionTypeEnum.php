<?php

namespace App\Enums;

enum EvtCompetitionTypeEnum: string
{
    case WORLD_CHAMPIONSHIPS = 'WorldChampionships';
    case WORLD_CUP = 'WorldCup';
    case WORLD_SERIES = 'WorldSeries';
    case WORLD_RECORD_ATTEMPT = 'WorldRecordAttempt';
    case CONTINENTAL_CHAMPIONSHIPS = 'ContinentalChampionships';
    case BI_CONTINENTAL_CHAMPIONSHIP = 'Bi-ContinentalChampionship';
    case BI_CONTINENTAL_CUP = 'Bi-ContinentalCup';
    case CONTINENTAL_ZONE_CHAMPIONSHIPS = 'ContinentalZoneChampionships';
    case AFRICAN_GAMES = 'AfricanGames';
    case AFRICAN_CHAMPIONSHIP = 'AfricanChampionship';
    case AFRICAN_CUP = 'AfricanCup';
    case PAN_AMERICAN_GAMES = 'PanAmericanGames';
    case AMERICAN_GAMES = 'AmericanGames';
    case AMERICAN_CHAMPIONSHIP = 'AmericanChampionship';
    case AMERICAN_CUP = 'AmericanCup';
    case ASIAN_GAMES = 'AsianGames';
    case ASIAN_CHAMPIONSHIP = 'AsianChampionship';
    case ASIAN_CUP = 'AsianCup';
    case SOUTH_EAST_ASIAN_GAMES = 'SouthEastAsianGames';
    case EUROPEAN_GAMES = 'EuropeanGames';
    case EUROPEAN_CHAMPIONSHIPS = 'EuropeanChampionships';
    case EUROPEAN_CUP = 'EuropeanCup';
    case MEDITERRANEAN_GAMES = 'MediterraneanGames';
    case PACIFIC_GAMES = 'PacificGames';
    case OCEANIA_GAMES = 'OceaniaGames';
    case OCEANIA_CHAMPIONSHIPS = 'OceaniaChampionships';
    case OCEANIA_CUP = 'OceaniaCup';
    case WORLD_UNIVERSITY_GAMES = 'WorldUniversityGames';
    case WORLD_UNIVERSITY_CHAMPIONSHIPS = 'WorldUniversityChampionships';
    case UNIVERSITY_WORLD_CUP = 'UniversityWorldCup';
    case BEACH_GAMES = 'BeachGames';
    case INTERNATIONAL_MEETING = 'InternationalMeeting';
    case NATIONAL_COMPETITIONS = 'NationalCompetitions';
    case OTHER_INTERNATIONAL_EVENTS = 'OtherInternationalEvents';

    public static function toString($type): string
    {
        return match ($type) {
            self::WORLD_CHAMPIONSHIPS->value => 'World Championships',
            self::WORLD_CUP->value => 'World Cup',
            self::WORLD_SERIES->value => 'World Series',
            self::WORLD_RECORD_ATTEMPT->value => 'World Record Attempt',
            self::CONTINENTAL_CHAMPIONSHIPS->value => 'Continental Championships',
            self::BI_CONTINENTAL_CHAMPIONSHIP->value => 'Bi-Continental Championship',
            self::BI_CONTINENTAL_CUP->value => 'Bi-Continental Cup',
            self::CONTINENTAL_ZONE_CHAMPIONSHIPS->value => 'Continental Zone Championships',
            self::AFRICAN_GAMES->value => 'African Games',
            self::AFRICAN_CHAMPIONSHIP->value => 'African Championship',
            self::AFRICAN_CUP->value => 'African Cup',
            self::PAN_AMERICAN_GAMES->value => 'Pan American Games',
            self::AMERICAN_GAMES->value => 'American Games',
            self::AMERICAN_CHAMPIONSHIP->value => 'American Championship',
            self::AMERICAN_CUP->value => 'American Cup',
            self::ASIAN_GAMES->value => 'Asian Games',
            self::ASIAN_CHAMPIONSHIP->value => 'Asian Championship',
            self::ASIAN_CUP->value => 'Asian Cup',
            self::SOUTH_EAST_ASIAN_GAMES->value => 'South East Asian Games',
            self::EUROPEAN_GAMES->value => 'European Games',
            self::EUROPEAN_CHAMPIONSHIPS->value => 'European Championships',
            self::EUROPEAN_CUP->value => 'European Cup',
            self::MEDITERRANEAN_GAMES->value => 'Mediterranean Games',
            self::PACIFIC_GAMES->value => 'Pacific Games',
            self::OCEANIA_GAMES->value => 'Oceania Games',
            self::OCEANIA_CHAMPIONSHIPS->value => 'Oceania Championships',
            self::OCEANIA_CUP->value => 'Oceania Cup',
            self::WORLD_UNIVERSITY_GAMES->value => 'World University Games',
            self::WORLD_UNIVERSITY_CHAMPIONSHIPS->value => 'World University Championships',
            self::UNIVERSITY_WORLD_CUP->value => 'University World Cup',
            self::BEACH_GAMES->value => 'Beach Games',
            self::INTERNATIONAL_MEETING->value => 'International Meeting',
            self::NATIONAL_COMPETITIONS->value => 'National Competitions',
            self::OTHER_INTERNATIONAL_EVENTS->value => 'Other International Events',
            default => 'Unknown Event Type',
        };
    }
}
