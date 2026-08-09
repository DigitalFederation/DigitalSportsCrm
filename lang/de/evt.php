<?php

return [
    // Athlete Entity Sport Registration
    'requires_athlete_entity_sport_registration' => 'Individuelle Mitgliederregistrierung als Vereinsathlet erforderlich',
    'requires_athlete_entity_sport_registration_hint' => 'Athleten müssen im anmeldenden Verein als Athleten für die Veranstaltungssportart registriert sein.',

    // Coach Entity Sport Registration
    'requires_coach_entity_sport_registration' => 'Individuelle Mitgliederregistrierung als Vereinstrainer erforderlich',
    'requires_coach_entity_sport_registration_hint' => 'Trainer müssen im anmeldenden Verein als Trainer für die Veranstaltungssportart registriert sein.',

    // Local Federation Affiliation (existing)
    'local_federation_requirements' => 'Zugehörigkeitsanforderungen',
    'requires_local_federation_affiliation' => 'Zugehörigkeit zum Landesverband erforderlich',
    'requires_local_federation_affiliation_hint' => 'Athleten und Trainer müssen Mitglieder desselben Landesverbandes sein wie der Verein, der sie für den Wettkampf anmeldet. Teamoffizielle und technische Offizielle sind von dieser Anforderung nicht betroffen.',

    // Validation messages
    'validation' => [
        'required_referee_certifications_invalid' => 'Eine oder mehrere der ausgewählten Schiedsrichterqualifikationen sind ungültig.',
        'required_coach_certifications_invalid' => 'Eine oder mehrere der ausgewählten Trainerqualifikationen sind ungültig.',
        'required_athlete_licenses_invalid' => 'Eine oder mehrere der ausgewählten Athletenlizenzen sind ungültig.',
        'required_athlete_documents_invalid' => 'Eines oder mehrere der ausgewählten erforderlichen Athletendokumente sind ungültig.',
        'required_coach_documents_invalid' => 'Eines oder mehrere der ausgewählten erforderlichen Trainerdokumente sind ungültig.',
        'required_referee_documents_invalid' => 'Eines oder mehrere der ausgewählten erforderlichen Schiedsrichterdokumente sind ungültig.',
        'required_official_documents_invalid' => 'Eines oder mehrere der ausgewählten erforderlichen Offiziellendokumente sind ungültig.',
    ],
];
