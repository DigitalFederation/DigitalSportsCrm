<?php

return [
    'title' => 'Gestão de Permissões de Rotas',
    'route_permissions' => 'Permissões de Rotas',
    'scan_routes' => 'Analisar Rotas',
    'assign_permissions' => 'Atribuir Permissões',
    'bulk_assign' => 'Atribuir em Massa',
    'routes_to_assign' => 'Rotas para Atribuir Permissões',
    'route_details' => 'Detalhes da Rota',
    'permission_mapping' => 'Mapeamento de Permissões',

    // Route fields
    'route_name' => 'Nome da Rota',
    'uri' => 'Padrão URI',
    'methods' => 'Métodos HTTP',
    'controller' => 'Controlador',
    'middleware' => 'Middleware',
    'current_permission' => 'Permissão Atual',
    'assigned_permission' => 'Permissão Atribuída',
    'suggested_permissions' => 'Permissões Sugeridas',
    'module' => 'Módulo',
    'prefix' => 'Prefixo',
    'parameters' => 'Parâmetros',
    'status' => 'Estado',
    'uncategorized' => 'Sem Categoria',

    // Filters
    'filter_by_module' => 'Filtrar por Módulo',
    'all_modules' => 'Todos os Módulos',
    'all_permissions' => 'Todas as Permissões',
    'filter_by_prefix' => 'Filtrar por Prefixo',
    'filter_by_permission' => 'Filtrar por Estado de Permissão',
    'has_permission' => 'Com Permissão',
    'no_permission' => 'Sem Permissão',
    'all_routes' => 'Todas as Rotas',
    'search_routes' => 'Pesquisar rotas...',

    // Statistics
    'total_routes' => 'Total de Rotas',
    'routes_with_permissions' => 'Rotas com Permissões',
    'routes_without_permissions' => 'Rotas sem Permissões',
    'percentage_protected' => 'Cobertura de Proteção',
    'dynamic_mappings' => 'Mapeamentos Dinâmicos',
    'active_mappings' => 'Mapeamentos Ativos',

    // Actions
    'scan' => 'Analisar',
    'assign' => 'Atribuir',
    'assign_permission' => 'Atribuir Permissão',
    'edit_permission_assignment' => 'Editar Atribuição de Permissão',
    'current_permissions' => 'Permissões Atuais',
    'no_permissions_assigned' => 'Sem permissões atribuídas',
    'add_permission' => 'Adicionar Permissão',
    'confirm_remove_permission' => 'Tem certeza de que deseja remover esta permissão?',
    'remove' => 'Remover',
    'activate' => 'Ativar',
    'deactivate' => 'Desativar',
    'select_all' => 'Selecionar Tudo',
    'deselect_all' => 'Desselecionar Tudo',
    'apply_suggestions' => 'Aplicar Sugestões',
    'create_permission' => 'Criar Permissão',
    'export_mappings' => 'Exportar Mapeamentos',

    // Route groups
    'grouped_by_module' => 'Rotas Agrupadas por Módulo',
    'module_statistics' => 'Estatísticas do Módulo',
    'route_group' => 'Grupo de Rotas',

    // Permission assignment
    'select_permission' => 'Selecionar Permissão',
    'no_permission_assigned' => 'Sem Permissão Atribuída',
    'permission_exists' => 'Permissão Existe',
    'permission_not_exists' => 'Permissão Não Existe',
    'create_and_assign' => 'Criar e Atribuir',
    'active' => 'Ativo',
    'inactive' => 'Inativo',

    // Bulk operations
    'bulk_operations' => 'Operações em Massa',
    'selected_routes' => 'Rotas Selecionadas',
    'bulk_assign_permissions' => 'Atribuir Permissões em Massa',
    'apply_to_selected' => 'Aplicar aos Selecionados',
    'preview_changes' => 'Pré-visualizar Alterações',

    // Impact preview
    'impact_preview' => 'Pré-visualização de Impacto',
    'new_mappings' => 'Novos Mapeamentos',
    'updated_mappings' => 'Mapeamentos Atualizados',
    'removed_mappings' => 'Mapeamentos Removidos',
    'affected_routes' => 'Rotas Afetadas',
    'affected_permissions' => 'Permissões Afetadas',

    // Messages
    'messages' => [
        'no_routes_selected' => 'Nenhuma rota selecionada. Por favor selecione pelo menos uma rota.',
        'permission_updated' => 'Permissão de rota atualizada com sucesso.',
        'permission_assigned' => 'Permissão atribuída com sucesso.',
        'permission_removed' => 'Permissão de rota removida com sucesso.',
        'bulk_assign_success' => 'Atribuição em massa concluída: :created criadas, :updated atualizadas.',
        'scan_complete' => 'Análise de rotas concluída. Encontradas :count rotas.',
        'export_success' => 'Permissões de rotas exportadas com sucesso.',
        'no_routes_found' => 'Nenhuma rota encontrada com os critérios especificados.',
        'confirm_remove' => 'Tem a certeza que deseja remover este mapeamento de permissão?',
        'try_adjusting_filters' => 'Tente ajustar seus filtros ou critérios de pesquisa.',
        'routes_selected' => 'rotas selecionadas',
        'assigning' => 'A atribuir...',
        'assigned' => 'Atribuída',
    ],

    // Errors
    'errors' => [
        'bulk_assign_failed' => 'Atribuição em massa falhou: :error',
        'assignment_failed' => 'Atribuição falhou: :error',
        'permission_update_failed' => 'Falha ao atualizar permissão de rota: :error',
        'scan_failed' => 'Análise de rotas falhou: :error',
        'export_failed' => 'Exportação falhou: :error',
    ],

    // Help text
    'help' => [
        'route_scanning' => 'A análise examina todas as rotas registadas na aplicação para identificar quais têm permissões e quais precisam delas.',
        'permission_suggestions' => 'As sugestões são baseadas em padrões de nomenclatura de rotas e operações CRUD comuns.',
        'dynamic_mappings' => 'Os mapeamentos dinâmicos permitem atribuir permissões a rotas sem modificar código.',
        'bulk_assignment' => 'Selecione múltiplas rotas e atribua a mesma permissão a todas de uma vez.',
        'protection_coverage' => 'Mostra a percentagem de rotas que têm proteção de permissão.',
    ],
];
