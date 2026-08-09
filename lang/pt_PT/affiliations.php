<?php

return [
    // Page titles and headers
    'title' => 'Filiações',
    'info_title' => 'Gestão de Filiações',
    'info_body' => 'Visualize e gira todas as filiações do sistema. Monitorize as filiações dos membros, o seu estado e federações associadas.',

    // Filter labels
    'member_type' => 'Tipo de Membro',
    'status' => 'Estado',
    'federation' => 'Organização',
    'member_name' => 'Nome do Membro',
    'start_date' => 'Data de Início',
    'end_date' => 'Data de Fim',

    // Table headers
    'table' => [
        'member' => 'Membro',
        'type' => 'Tipo',
        'federation' => 'Organização',
        'start_date' => 'Data de Início',
        'end_date' => 'Data de Fim',
        'fee' => 'Taxa',
        'status' => 'Estado',
    ],

    // Status labels
    'statuses' => [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
        'suspended' => 'Suspenso',
        'expired' => 'Expirado',
        'pending_payment' => 'Pagamento Pendente',
    ],

    // Actions
    'view_member' => 'Ver Membro',
    'delete' => 'Apagar',
    'member' => 'Membro',

    // Data placeholders
    'member_not_found' => 'Membro não encontrado',
    'no_federation' => 'Sem federação',
    'no_date' => 'Sem data',
    'no_fee' => 'Sem taxa',
    'via_entity' => 'via entidade',

    // Messages
    'no_affiliations_found' => 'Não foram encontradas filiações que correspondam aos seus critérios.',
    'affiliations_empty' => 'Ainda não foram criadas filiações.',
    'status_updated_successfully' => 'Estado da filiação atualizado com sucesso.',
    'status_update_failed' => 'Falha ao atualizar o estado da filiação. Por favor, tente novamente.',
    'deleted_successfully' => 'Filiação apagada com sucesso.',
    'delete_failed' => 'Falha ao apagar a filiação. Por favor, tente novamente.',

    // Delete confirmation
    'confirm_delete_title' => 'Apagar Filiação',
    'confirm_delete_message' => 'Tem a certeza de que deseja apagar esta filiação? Esta ação não pode ser revertida.',
    'delete_confirm' => 'Apagar Filiação',

    // Status change confirmation
    'confirm_status_change' => 'Tem a certeza de que deseja alterar o estado desta filiação?',

    // Component specific
    'active_affiliations' => 'Filiações Ativas',
    'affiliation_count' => '{0} Sem filiações|{1} :count filiação|[2,*] :count filiações',
    'no_active_affiliations' => 'Sem filiações ativas',
    'plan' => 'Plano',
    'period' => 'Período',
    'fee' => 'Taxa',
    'privileges' => 'Privilégios',
    'until' => 'até',
    'standard_plan' => 'Plano padrão',
    'validation_plan' => 'Plano de validação',
    'insurance_requests' => 'Pedidos de seguro',
    'license_requests' => 'Pedidos de licença',
    'standard' => 'Padrão',
    'active' => 'Ativo',
    'expired' => 'Expirado',
];
