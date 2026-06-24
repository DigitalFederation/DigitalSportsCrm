<?php

return [
    // Validation messages
    'package_required' => 'É necessário selecionar um pacote de adesão',
    'invalid_package' => 'O pacote de adesão selecionado é inválido',
    'individuals_required' => 'É necessário selecionar pelo menos um indivíduo',
    'min_one_individual' => 'Selecione pelo menos um indivíduo',

    // Success messages
    'subscriptions_created' => 'Inscrições Criadas',
    'success_count' => ':count inscrições foram criadas com sucesso',
    'payment_required_count' => ':count requerem documentos de pagamento',
    'free_subscriptions_count' => ':count são gratuitas e estão ativas',

    // Error messages
    'some_subscriptions_failed' => 'Algumas Inscrições Falharam',
    'failed_count' => ':count inscrições não puderam ser criadas. Por favor, verifique os logs para mais detalhes',
    'error' => 'Erro',
    'unexpected_error' => 'Ocorreu um erro inesperado ao processar as inscrições',
    'unauthorized_action' => 'Ação não autorizada',

    // Action buttons
    'retry_failed' => 'Tentar Novamente',
    'retry_failed_title' => 'Tentar Novamente Inscrições Falhadas',
    'retry_failed_description' => 'Deseja tentar criar novamente as inscrições que falharam?',
    'yes_retry' => 'Sim, tentar novamente',
    'no_cancel' => 'Não, cancelar',
    'try_again' => 'Tentar Novamente',
    // Headers and titles
    'select_package' => 'Selecionar um dos planos para atribuir aos membros da sua entidade selecionados de seguida.',
    'select_insurance_package' => 'Selecionar um dos planos de seguro para atribuir aos membros da sua entidade selecionados de seguida.',
    'select_members' => 'Selecionar Membros',
    'entity_member_memberships_title' => 'Planos de Filiação de Membros da Entidade',
    'entity_member_insurances_title' => 'Planos de Seguro de Membros da Entidade',
    'selected' => 'Selecionado',

    // Search and filters
    'search_placeholder' => 'Pesquisar membros por nome ou ID...',
    'filter' => [
        'all_status' => 'Todos os Status',
        'active_subscription' => 'Subscrição Ativa',
        'no_subscription' => 'Sem Subscrição',
    ],

    // Table headers
    'table' => [
        'name' => 'Nome',
        'id' => 'ID',
        'status' => 'Status',
    ],

    // Status labels
    'status' => [
        'active' => 'Ativo',
        'no_subscription' => 'Sem Subscrição',
    ],

    // Messages
    'no_members_found' => 'Nenhum membro encontrado com os critérios selecionados.',

    // Selection tray
    'selected_members' => 'Membros Selecionados',
    'click_to_view' => 'clique para ver',
    'clear_all' => 'Limpar tudo',
    'remove_selection' => 'Remover da seleção',
    'total_selected' => ':count membro(s) selecionado(s)',
    'estimated_total' => 'Total estimado',

    // Actions
    'actions' => [
        'cancel' => 'Cancelar',
        'subscribe_selected' => 'Subscrever Membros Selecionados (:count)',
        'confirm' => 'Confirmar',
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'Confirmar Subscrição',
        'confirm_message' => 'Está prestes a subscrever os membros selecionados para o seguinte pacote:',
        'price' => 'Preço',
        'subscription_count' => 'Esta ação irá criar novas subscrições para :count membros.',
    ],
];
