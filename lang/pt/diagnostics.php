<?php

return [
    // Page titles
    'title' => 'Centro de Diagnóstico de Elegibilidade',
    'subtitle' => 'Diagnosticar porque indivíduos podem não aparecer nas listas de inscrição',

    // Tab titles
    'tab_individual_profile' => 'Perfil Individual',
    'tab_event_enrollment' => 'Inscrição em Eventos',
    'tab_license_availability' => 'Disponibilidade de Licenças',

    // Individual Profile Tab
    'individual_profile_title' => 'Diagnóstico do Perfil Individual',
    'individual_profile_description' => 'Pesquise um indivíduo para ver o perfil completo de elegibilidade e entender porque pode ou não ser inscrito para diferentes funções.',
    'search_placeholder' => 'Pesquisar por código, nome ou email...',
    'no_individual_selected' => 'Nenhum Indivíduo Selecionado',
    'search_to_start' => 'Pesquise um indivíduo para ver o perfil de elegibilidade.',
    'quick_status' => 'Estado Rápido',

    // Role labels
    'role_athlete' => 'Atleta',
    'role_coach' => 'Treinador',
    'role_referee' => 'Árbitro',
    'role_official' => 'Oficial',

    // Sections
    'federation_memberships' => 'Filiações em Federações',
    'entity_memberships' => 'Filiações em Entidades',
    'professional_roles' => 'Funções Profissionais',
    'certifications' => 'Certificações (Verificação de Árbitro)',
    'active_licenses' => 'Licenças Ativas',

    // Table headers
    'federation' => 'Federação',
    'entity' => 'Entidade',
    'type' => 'Tipo',
    'status' => 'Estado',
    'since' => 'Desde',
    'sports' => 'Modalidades',
    'role' => 'Função',
    'source' => 'Origem',
    'certification' => 'Certificação',
    'grants_role' => 'Concede Função',
    'action_needed' => 'Ação Necessária',
    'license' => 'Licença',
    'expires' => 'Expira',

    // Federation types
    'local' => 'Local',
    'main' => 'Principal',
    'modalidade' => 'Modalidade',

    // Empty states
    'no_federation_memberships' => 'Nenhuma filiação em federação encontrada.',
    'no_entity_memberships' => 'Nenhuma filiação em entidade encontrada.',
    'no_professional_roles' => 'Nenhuma função profissional atribuída.',
    'no_certifications' => 'Nenhuma certificação atribuída.',
    'no_active_licenses' => 'Nenhuma licença ativa.',
    'unknown_federation' => 'Federação Desconhecida',
    'unknown_entity' => 'Entidade Desconhecida',
    'unknown_license' => 'Licença Desconhecida',
    'unknown_certification' => 'Certificação Desconhecida',

    // Sources
    'source_direct_assignment' => 'Atribuição Direta',
    'source_entity_assignment' => 'Atribuição por Entidade',

    // Certification action
    'action_activate_certification' => 'ATIVAR para habilitar função',

    // Quick status reasons
    'not_checked' => 'Não verificado',
    'reason_no_active_federation' => 'Sem filiação ativa em federação',
    'reason_no_active_entity' => 'Sem filiação ativa em entidade',
    'reason_not_registered_athlete' => 'Não registado como atleta',
    'reason_registered_athlete' => 'Registado como atleta',
    'reason_no_coach_role' => 'Sem função profissional de TREINADOR',
    'reason_has_coach_role' => 'Tem função de TREINADOR atribuída',
    'reason_cert_pending_activation' => 'Certificação existe mas está PENDENTE de ativação',
    'reason_no_referee_cert' => 'Nenhuma certificação de árbitro atribuída',
    'reason_no_referee_role' => 'Sem função profissional de ÁRBITRO (verificar certificação)',
    'reason_has_referee_role' => 'Tem função de ÁRBITRO atribuída',
    'reason_no_active_membership' => 'Sem filiação ativa',
    'reason_active_member' => 'Membro ativo',

    // Event Enrollment Tab
    'event_enrollment_title' => 'Diagnóstico de Inscrição em Eventos',
    'event_enrollment_description' => 'Selecione um evento e indivíduo para diagnosticar porque podem não aparecer na lista de inscrição para uma função específica.',
    'select_event' => 'Selecionar Evento',
    'select_event_placeholder' => '-- Selecione um evento --',
    'select_competition' => 'Selecionar Competição (opcional)',
    'all_competitions' => '-- Todas as competições --',
    'select_role' => 'Função a Diagnosticar',
    'search_individual' => 'Pesquisar Indivíduo',
    'run_diagnostic' => 'Executar Diagnóstico',
    'selected' => 'Selecionado',
    'select_event_first' => 'Selecione um Evento Primeiro',
    'select_event_to_start' => 'Escolha um evento da lista para iniciar o diagnóstico.',

    // Diagnostic results
    'eligible_as_role' => 'ELEGÍVEL como :role',
    'not_eligible_as_role' => 'NÃO ELEGÍVEL como :role',
    'passed' => 'PASSOU',
    'failed' => 'FALHOU',
    'suggestions' => 'Ações Sugeridas',

    // Check labels
    'check_federation_membership' => 'Filiação em Federação',
    'check_entity_membership' => 'Filiação em Entidade',
    'check_athlete_registration' => 'Registo de Atleta',
    'check_coach_role' => 'Função Profissional de Treinador',
    'check_referee_role' => 'Função Profissional de Árbitro',
    'check_referee_cert_exists' => 'Certificação de Árbitro Existe',
    'check_referee_cert_active' => 'Certificação está Ativa',
    'check_required_certs' => 'Certificações Obrigatórias',
    'check_required_licenses' => 'Licenças Obrigatórias',
    'check_active_membership' => 'Filiação Ativa',
    'check_not_enrolled' => 'Não Inscrito Anteriormente',

    // Check messages - Passed
    'check_federation_membership_passed' => 'Membro ativo de :federation',
    'check_federation_membership_athlete_passed' => 'Tem filiação ativa em federação',
    'check_federation_membership_coach_passed' => 'Tem filiação ativa em federação',
    'check_entity_membership_passed' => 'Membro ativo de: :entities',
    'check_entity_membership_passed_coach' => 'Tem filiação ativa em entidade',
    'check_athlete_registration_passed' => 'Registado como atleta para :sport',
    'check_coach_role_passed' => 'Tem função profissional de TREINADOR atribuída',
    'check_referee_role_passed' => 'Tem função profissional de ÁRBITRO atribuída',
    'check_referee_cert_exists_passed' => 'Tem certificação(ões) de árbitro: :certs',
    'check_referee_cert_active_passed' => 'Tem pelo menos uma certificação de árbitro ativa',
    'check_required_certs_passed' => 'Tem todas as certificações obrigatórias',
    'check_required_licenses_passed' => 'Tem todas as licenças obrigatórias',
    'check_active_membership_passed' => 'Tem filiação ativa (pode ser inscrito como oficial)',
    'check_not_enrolled_passed' => 'Ainda não inscrito neste evento',

    // Check messages - Failed
    'check_federation_membership_failed' => 'Nenhuma filiação ativa em federação encontrada',
    'check_entity_membership_failed' => 'Nenhuma filiação ativa em entidade encontrada',
    'check_athlete_registration_failed' => 'Não registado como atleta em nenhuma entidade',
    'check_athlete_wrong_sport' => 'Registado para :registered mas o evento requer :required',
    'check_coach_role_failed' => 'Não tem função profissional de TREINADOR atribuída',
    'check_referee_role_failed' => 'Não tem função profissional de ÁRBITRO atribuída',
    'check_referee_role_cert_pending' => 'Certificação ":cert" existe mas está PENDENTE - função de ÁRBITRO ainda não atribuída',
    'check_referee_cert_exists_failed' => 'Nenhuma certificação de tipo árbitro atribuída',
    'check_referee_cert_no_certs' => 'Nenhuma certificação de árbitro para verificar',
    'check_referee_cert_pending' => 'Certificação(ões) de árbitro existem mas estão PENDENTES: :certs',
    'check_referee_cert_inactive' => 'Nenhuma certificação de árbitro ativa encontrada',
    'check_required_certs_failed' => 'Falta certificação(ões) obrigatória(s): :certs',
    'check_required_licenses_failed' => 'Falta licença(s) obrigatória(s): :licenses',
    'check_active_membership_failed' => 'Sem filiação ativa em nenhuma federação ou entidade',
    'check_already_enrolled' => 'Já inscrito neste evento para esta função',

    // Suggestions
    'suggestion_activate_membership' => 'Ativar filiação em federação/entidade',
    'suggestion_join_entity' => 'Aderir a uma entidade como membro',
    'suggestion_register_as_athlete' => 'Registar como atleta em Entidade > Atletas',
    'suggestion_register_for_sport' => 'Registar como atleta para a modalidade correta',
    'suggestion_assign_coach_role' => 'Atribuir função de TREINADOR em Entidade > Treinadores',
    'suggestion_attribute_referee_cert' => 'Atribuir certificação de árbitro em Federação > Certificações',
    'suggestion_activate_certification' => 'ATIVAR a certificação pendente para conceder função de ÁRBITRO',
    'suggestion_check_cert_status' => 'Verificar estado da certificação - pode estar expirada ou cancelada',
    'suggestion_obtain_required_cert' => 'Obter e ativar a(s) certificação(ões) obrigatória(s)',
    'suggestion_obtain_required_license' => 'Obter e ativar a(s) licença(s) obrigatória(s)',

    // Membership details
    'member_of_federations' => 'Federação(ões): :federations',
    'member_of_entities' => 'Entidade(s): :entities',

    // License Availability Tab
    'license_availability_title' => 'Diagnóstico de Disponibilidade de Licenças',
    'license_availability_description' => 'Diagnosticar porque certas licenças podem não aparecer na lista de compra.',
    'coming_soon' => 'Em breve...',
];
