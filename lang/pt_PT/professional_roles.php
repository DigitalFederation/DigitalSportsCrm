<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'title' => 'Perfis Profissionais',
    'create' => 'Criar Perfil Profissional',
    'edit' => 'Editar Perfil Profissional',
    'edit_title' => 'Editar Perfil',
    'information_title' => 'Informações',
    'update' => 'Atualizar Perfil Profissional',

    // Fields
    'name' => 'Nome',
    'code' => 'Codigo',
    'role_type' => 'Tipo de Perfil',
    'committee' => 'Comité',
    'committee_all' => 'Todos',

    // Hints
    'code_hint' => 'Identificador unico em maiusculas (ex: FINSWIMMINGCOACH)',
    'role_type_hint' => 'Categoria a que este perfil pertence (afeta elegibilidade para inscrição em eventos)',

    // Filters
    'filter_by_name' => 'Filtrar por nome...',

    // Info boxes
    'info_title' => 'Informação do Perfil Profissional',
    'info_body' => "Os perfis profissionais definem que atividades e acessos uma pessoa tem acesso (ex: Treinador, Oficial Tecnico, Membro dos orgaos sociais, membros tecnicos de {$primaryShortName}). O tipo de perfil determina como o sistema trata este perfil para inscricao e verificacao de elegibilidade em eventos, ou acesso a funcionalidades do portal.",
    'edit_info_title' => 'Informações',
    'edit_info_body' => 'Os perfis profissionais definem as atividades que uma pessoa pode realizar na federação. O tipo de perfil determina a elegibilidade para eventos: ATLETA para competidores, TREINADOR para treinadores de equipa, OFICIAL TÉCNICO para árbitros e juízes, INSTRUTOR para instrutores de cursos, MERGULHADOR para mergulhadores recreativos/profissionais, STAFF para suporte operacional da federação, e STAFF DA FEDERAÇÃO para funções administrativas da federação.',

    // Success messages
    'created_successfully' => 'Perfil profissional criado com sucesso.',
    'updated_successfully' => 'Perfil profissional atualizado com sucesso.',
    'deleted_successfully' => 'Perfil profissional eliminado com sucesso.',
    'role_assigned_successfully' => 'Papel profissional atribuído com sucesso.',
    'role_removed_successfully' => 'Papel profissional removido com sucesso.',

    // Error messages
    'cannot_delete_has_individuals' => 'Não e possivel eliminar este perfil profissional porque esta atribuido a indivíduos.',
    'cannot_delete_has_certifications' => 'Não e possivel eliminar este perfil profissional porque esta ligado a certificações.',
    'cannot_delete_has_licenses' => 'Não e possivel eliminar este perfil profissional porque esta ligado a licenças.',
    'delete_failed' => 'Falha ao eliminar perfil profissional. Por favor tente novamente.',

    // Role types
    'role_types' => [
        'ATHLETE' => 'Atleta',
        'COACH' => 'Treinador',
        'TECHNICAL_OFFICIAL' => 'Oficial Técnico',
        'INSTRUCTOR' => 'Instrutor',
        'LEADER' => 'Líder',
        'DIVER' => 'Mergulhador',
        'STAFF' => 'Staff',
        'DIVINGPROFESSIONAL' => 'Profissional de Mergulho',
        'FEDERATION_STAFF' => 'Staff da Federação',
    ],

    // Actions
    'manage_roles' => 'Gerir Funções',
];
