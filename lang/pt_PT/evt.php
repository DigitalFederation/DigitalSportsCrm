<?php

return [
    // Registo de Atleta na Modalidade da Entidade
    'requires_athlete_entity_sport_registration' => 'Exigir o registo do membro individual como atleta do clube',
    'requires_athlete_entity_sport_registration_hint' => 'Os atletas devem estar registados como atletas na modalidade do evento no clube que os inscreve.',

    // Registo de Treinador na Modalidade da Entidade
    'requires_coach_entity_sport_registration' => 'Exigir o registo do membro individual como treinador do clube',
    'requires_coach_entity_sport_registration_hint' => 'Os treinadores devem estar registados como treinadores na modalidade do evento no clube que os inscreve.',

    // Filiação à Federação Local (existente)
    'local_federation_requirements' => 'Requisitos de Filiação',
    'requires_local_federation_affiliation' => 'Exigir Filiação à Associação Territorial',
    'requires_local_federation_affiliation_hint' => 'Os atletas e treinadores devem ser membros da mesma Associação Territorial que o clube que os inscreve na competição. Os oficiais de equipa e oficiais técnicos não são afetados por este requisito.',

    // Mensagens de validação
    'validation' => [
        'required_referee_certifications_invalid' => 'Uma ou mais certificações de árbitro selecionadas são inválidas.',
        'required_coach_certifications_invalid' => 'Uma ou mais certificações de treinador selecionadas são inválidas.',
        'required_athlete_licenses_invalid' => 'Uma ou mais licenças de atleta selecionadas são inválidas.',
        'required_athlete_documents_invalid' => 'Um ou mais documentos obrigatórios de atleta selecionados são inválidos.',
        'required_coach_documents_invalid' => 'Um ou mais documentos obrigatórios de treinador selecionados são inválidos.',
        'required_referee_documents_invalid' => 'Um ou mais documentos obrigatórios de árbitro selecionados são inválidos.',
        'required_official_documents_invalid' => 'Um ou mais documentos obrigatórios de oficial selecionados são inválidos.',
    ],
];
