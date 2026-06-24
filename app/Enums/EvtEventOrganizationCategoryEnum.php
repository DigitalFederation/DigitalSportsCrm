<?php

namespace App\Enums;

enum EvtEventOrganizationCategoryEnum: string
{
    // Mergulho Recreativo e Científico
    case diving_course = 'diving_course';
    case diving_activity = 'diving_activity';
    case scientific_diving = 'scientific_diving';
    case expedition = 'expedition';
    case citizen_science = 'citizen_science';

    // Desporto
    case coach_course = 'coach_course';
    case referee_judge_course = 'referee_judge_course';
    case coach_training_camp = 'coach_training_camp';
    case athlete_training_camp = 'athlete_training_camp';
    case other_training = 'other_training';
    case commission_meeting = 'commission_meeting';
    case sport_other = 'sport_other';

    // Primary federation
    case federation_general_assembly = 'federation_general_assembly';
    case federation_board_meeting = 'federation_board_meeting';
    case federation_technical_committee_meeting = 'federation_technical_committee_meeting';
    case federation_environment_science_committee_meeting = 'federation_environment_science_committee_meeting';
    case federation_sport_committee_meeting = 'federation_sport_committee_meeting';
    case federation_territorial_committee_meeting = 'federation_territorial_committee_meeting';
    case federation_social_bodies_meeting = 'federation_social_bodies_meeting';
    case federation_other = 'federation_other';

    // International federation
    case international_commission_meeting = 'international_commission_meeting';
    case international_general_assembly = 'international_general_assembly';
    case international_other = 'international_other';

    public static function getGroupedOptions(): array
    {
        return [
            'Mergulho Recreativo e Científico' => [
                self::diving_course->name,
                self::diving_activity->name,
                self::scientific_diving->name,
                self::expedition->name,
                self::citizen_science->name,
            ],
            'Desporto' => [
                self::coach_course->name,
                self::referee_judge_course->name,
                self::coach_training_camp->name,
                self::athlete_training_camp->name,
                self::other_training->name,
                self::commission_meeting->name,
                self::sport_other->name,
            ],
            config('branding.primary.short_name', 'DF') => [
                self::federation_general_assembly->name,
                self::federation_board_meeting->name,
                self::federation_technical_committee_meeting->name,
                self::federation_environment_science_committee_meeting->name,
                self::federation_sport_committee_meeting->name,
                self::federation_territorial_committee_meeting->name,
                self::federation_social_bodies_meeting->name,
                self::federation_other->name,
            ],
            config('branding.international.name', 'International Federation') => [
                self::international_commission_meeting->name,
                self::international_general_assembly->name,
                self::international_other->name,
            ],
        ];
    }

    public static function toString($event_category): string
    {
        $eventCategory = $event_category instanceof self
            ? $event_category
            : self::tryFrom((string) $event_category);

        return match ($eventCategory) {
            // Mergulho Recreativo e Científico
            self::diving_course => __('event_applications.event_categories.diving_course'),
            self::diving_activity => __('event_applications.event_categories.diving_activity'),
            self::scientific_diving => __('event_applications.event_categories.scientific_diving'),
            self::expedition => __('event_applications.event_categories.expedition'),
            self::citizen_science => __('event_applications.event_categories.citizen_science'),

            // Desporto
            self::coach_course => __('event_applications.event_categories.coach_course'),
            self::referee_judge_course => __('event_applications.event_categories.referee_judge_course'),
            self::coach_training_camp => __('event_applications.event_categories.coach_training_camp'),
            self::athlete_training_camp => __('event_applications.event_categories.athlete_training_camp'),
            self::other_training => __('event_applications.event_categories.other_training'),
            self::commission_meeting => __('event_applications.event_categories.commission_meeting'),
            self::sport_other => __('event_applications.event_categories.sport_other'),

            // Primary federation
            self::federation_general_assembly => __('event_applications.event_categories.federation_general_assembly'),
            self::federation_board_meeting => __('event_applications.event_categories.federation_board_meeting'),
            self::federation_technical_committee_meeting => __('event_applications.event_categories.federation_technical_committee_meeting'),
            self::federation_environment_science_committee_meeting => __('event_applications.event_categories.federation_environment_science_committee_meeting'),
            self::federation_sport_committee_meeting => __('event_applications.event_categories.federation_sport_committee_meeting'),
            self::federation_territorial_committee_meeting => __('event_applications.event_categories.federation_territorial_committee_meeting'),
            self::federation_social_bodies_meeting => __('event_applications.event_categories.federation_social_bodies_meeting'),
            self::federation_other => __('event_applications.event_categories.federation_other'),

            // International federation
            self::international_commission_meeting => __('event_applications.event_categories.international_commission_meeting'),
            self::international_general_assembly => __('event_applications.event_categories.international_general_assembly'),
            self::international_other => __('event_applications.event_categories.international_other'),

            default => __('event_applications.event_categories.sport_other'),
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public static function toTranslatedArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = self::toString($case->value);
        }

        return $result;
    }
}
