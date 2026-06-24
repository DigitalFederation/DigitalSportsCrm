<?php

return [
    // Athlete Entity Sport Registration
    'requires_athlete_entity_sport_registration' => 'Require individual member registration as club athlete',
    'requires_athlete_entity_sport_registration_hint' => 'Athletes must be registered as athletes for the event sport in the enrolling club.',

    // Coach Entity Sport Registration
    'requires_coach_entity_sport_registration' => 'Require individual member registration as club coach',
    'requires_coach_entity_sport_registration_hint' => 'Coaches must be registered as coaches for the event sport in the enrolling club.',

    // Local Federation Affiliation (existing)
    'local_federation_requirements' => 'Affiliation Requirements',
    'requires_local_federation_affiliation' => 'Require Territorial Association Affiliation',
    'requires_local_federation_affiliation_hint' => 'Athletes and coaches must be members of the same territorial association as the club enrolling them in the competition. Team officials and technical officials are not affected by this requirement.',

    // Validation messages
    'validation' => [
        'required_referee_certifications_invalid' => 'One or more selected referee certifications are invalid.',
        'required_coach_certifications_invalid' => 'One or more selected coach certifications are invalid.',
        'required_athlete_licenses_invalid' => 'One or more selected athlete licenses are invalid.',
        'required_athlete_documents_invalid' => 'One or more selected required athlete documents are invalid.',
        'required_coach_documents_invalid' => 'One or more selected required coach documents are invalid.',
        'required_referee_documents_invalid' => 'One or more selected required referee documents are invalid.',
        'required_official_documents_invalid' => 'One or more selected required official documents are invalid.',
    ],
];
