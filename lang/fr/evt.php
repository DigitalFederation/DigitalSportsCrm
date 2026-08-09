<?php

return [
    // Athlete Entity Sport Registration
    'requires_athlete_entity_sport_registration' => 'Exiger l\'enregistrement du membre individuel en tant qu\'athlète du club',
    'requires_athlete_entity_sport_registration_hint' => 'Les athlètes doivent être enregistrés en tant qu\'athlètes pour le sport de l\'événement dans le club qui les inscrit.',

    // Coach Entity Sport Registration
    'requires_coach_entity_sport_registration' => 'Exiger l\'enregistrement du membre individuel en tant qu\'entraîneur du club',
    'requires_coach_entity_sport_registration_hint' => 'Les entraîneurs doivent être enregistrés en tant qu\'entraîneurs pour le sport de l\'événement dans le club qui les inscrit.',

    // Local Federation Affiliation (existing)
    'local_federation_requirements' => 'Conditions d\'affiliation',
    'requires_local_federation_affiliation' => 'Exiger l\'affiliation à une association territoriale',
    'requires_local_federation_affiliation_hint' => 'Les athlètes et entraîneurs doivent être membres de la même association territoriale que le club qui les inscrit à la compétition. Les officiels d\'équipe et les officiels techniques ne sont pas concernés par cette exigence.',

    // Validation messages
    'validation' => [
        'required_referee_certifications_invalid' => 'Une ou plusieurs certifications d\'arbitre sélectionnées sont invalides.',
        'required_coach_certifications_invalid' => 'Une ou plusieurs certifications d\'entraîneur sélectionnées sont invalides.',
        'required_athlete_licenses_invalid' => 'Une ou plusieurs licences d\'athlète sélectionnées sont invalides.',
        'required_athlete_documents_invalid' => 'Un ou plusieurs documents d\'athlète requis sélectionnés sont invalides.',
        'required_coach_documents_invalid' => 'Un ou plusieurs documents d\'entraîneur requis sélectionnés sont invalides.',
        'required_referee_documents_invalid' => 'Un ou plusieurs documents d\'arbitre requis sélectionnés sont invalides.',
        'required_official_documents_invalid' => 'Un ou plusieurs documents d\'officiel requis sélectionnés sont invalides.',
    ],
];
