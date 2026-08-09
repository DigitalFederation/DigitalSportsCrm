<?php

return [
    // Athlete Entity Sport Registration
    'requires_athlete_entity_sport_registration' => 'Exigir el registro del miembro individual como deportista del club',
    'requires_athlete_entity_sport_registration_hint' => 'Los deportistas deben estar registrados como deportistas para el deporte del evento en el club que los inscribe.',

    // Coach Entity Sport Registration
    'requires_coach_entity_sport_registration' => 'Exigir el registro del miembro individual como entrenador del club',
    'requires_coach_entity_sport_registration_hint' => 'Los entrenadores deben estar registrados como entrenadores para el deporte del evento en el club que los inscribe.',

    // Local Federation Affiliation (existing)
    'local_federation_requirements' => 'Requisitos de afiliación',
    'requires_local_federation_affiliation' => 'Exigir afiliación a la asociación territorial',
    'requires_local_federation_affiliation_hint' => 'Los deportistas y entrenadores deben ser miembros de la misma asociación territorial que el club que los inscribe en la competición. Los oficiales de equipo y los oficiales técnicos no se ven afectados por este requisito.',

    // Validation messages
    'validation' => [
        'required_referee_certifications_invalid' => 'Una o más de las certificaciones de árbitro seleccionadas no son válidas.',
        'required_coach_certifications_invalid' => 'Una o más de las certificaciones de entrenador seleccionadas no son válidas.',
        'required_athlete_licenses_invalid' => 'Una o más de las licencias de deportista seleccionadas no son válidas.',
        'required_athlete_documents_invalid' => 'Uno o más de los documentos de deportista requeridos seleccionados no son válidos.',
        'required_coach_documents_invalid' => 'Uno o más de los documentos de entrenador requeridos seleccionados no son válidos.',
        'required_referee_documents_invalid' => 'Uno o más de los documentos de árbitro requeridos seleccionados no son válidos.',
        'required_official_documents_invalid' => 'Uno o más de los documentos de oficial requeridos seleccionados no son válidos.',
    ],
];
