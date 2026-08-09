<?php

return [
    'title' => 'Gestão de Permissões',
    'permission' => 'Permissão',
    'permissions' => 'Permissões',
    'create_permission' => 'Criar Permissão',
    'edit_permission' => 'Editar Permissão',
    'delete_permission' => 'Eliminar Permissão',
    'permission_details' => 'Detalhes da Permissão',
    'bulk_create' => 'Criar Permissões em Massa',
    'import_permissions' => 'Importar Permissões',
    'export_permissions' => 'Exportar Permissões',

    // Form fields
    'name' => 'Nome da Permissão',
    'name_help' => 'Use minúsculas com hífens (ex: gerir-usuários)',
    'display_name' => 'Nome de Exibição',
    'description' => 'Descrição',
    'category' => 'Categoria',
    'guard' => 'Guard',
    'guard_name' => 'Nome do Guard',
    'roles_using' => 'Funções que Usam Esta Permissão',
    'routes_using' => 'Rotas que Usam Esta Permissão',
    'created_by' => 'Criado Por',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',

    // Categories
    'uncategorized' => 'Sem Categoria',
    'all_categories' => 'Todas as Categorias',
    'select_category' => 'Selecionar Categoria',
    'new_category' => 'Nova Categoria',

    // Filters
    'filter_by_category' => 'Filtrar por Categoria',
    'filter_by_usage' => 'Filtrar por Uso',
    'has_roles' => 'Com Funções',
    'no_roles' => 'Sem Funções',
    'search_permissions' => 'Pesquisar permissões...',

    // Statistics
    'total_permissions' => 'Total de Permissões',
    'system_permissions' => 'Permissões de Sistema',
    'custom_permissions' => 'Permissões Personalizadas',
    'permissions_with_roles' => 'Permissões com Funções',
    'unused_permissions' => 'Permissões Não Utilizadas',
    'permissions_with_routes' => 'Permissões com Rotas',

    // Actions
    'actions' => 'Ações',
    'view' => 'Ver',
    'edit' => 'Editar',
    'delete' => 'Eliminar',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',
    'create' => 'Criar',
    'back_to_list' => 'Voltar às Permissões',
    'confirm_delete' => 'Confirmar Eliminação',

    // Bulk operations
    'bulk_operations' => 'Operações em Massa',
    'add_permission_line' => 'Adicionar Permissão',
    'remove_line' => 'Remover',
    'default_category' => 'Categoria Padrão',
    'default_guard' => 'Guard Padrão',
    'apply_defaults' => 'Aplicar Padrões',

    // Import/Export
    'select_file' => 'Selecionar Arquivo CSV',
    'download_template' => 'Descarregar Modelo',
    'import' => 'Importar',
    'export' => 'Exportar',

    // Status
    'system_permission' => 'Permissão de Sistema',
    'protected' => 'Protegida',
    'deletable' => 'Eliminável',
    'in_use' => 'Em Uso',
    'not_used' => 'Não Utilizada',

    // Impact analysis
    'deletion_impact' => 'Impacto da Eliminação',
    'affected_roles' => 'Funções Afetadas',
    'affected_users' => 'Usuários Afetados',
    'affected_routes' => 'Rotas Afetadas',
    'roles_list' => 'Funções com esta permissão',

    // Messages
    'messages' => [
        'permission_created_successfully' => 'Permissão criada com sucesso.',
        'permission_updated_successfully' => 'Permissão atualizada com sucesso.',
        'permission_deleted_successfully' => 'Permissão eliminada com sucesso.',
        'bulk_create_success' => ':count permissões criadas com sucesso.',
        'bulk_create_partial' => ':created permissões criadas, :failed falharam.',
        'import_success' => 'Importação concluída: :created criadas, :skipped ignoradas.',
        'no_permissions_found' => 'Nenhuma permissão encontrada.',
        'no_permissions_added' => 'Nenhuma permissão adicionada ainda.',
        'confirm_delete_message' => 'Tem a certeza que deseja eliminar esta permissão? Esta ação não pode ser desfeita.',
    ],

    // Errors
    'errors' => [
        'permission_already_exists' => 'Já existe uma permissão com este nome.',
        'cannot_modify_system_permission' => 'As permissões de sistema não podem ser modificadas.',
        'cannot_delete_system_permission' => 'As permissões de sistema não podem ser eliminadas.',
        'permission_used_by_protected_roles' => 'Esta permissão é usada por funções protegidas: :roles',
        'permission_used_in_routes' => 'Esta permissão é usada em :count rota(s).',
        'permission_creation_failed' => 'Falha ao criar permissão: :error',
        'permission_update_failed' => 'Falha ao atualizar permissão: :error',
        'permission_deletion_failed' => 'Falha ao eliminar permissão: :error',
        'bulk_create_failed' => 'Criação em massa falhou: :error',
        'import_failed' => 'Importação falhou: :error',
        'invalid_permission_name' => 'O nome da permissão deve ser em minúsculas com hífens apenas.',
    ],

    // Validation
    'validation' => [
        'name_required' => 'O nome da permissão é obrigatório.',
        'name_unique' => 'Este nome de permissão já existe.',
        'name_format' => 'O nome da permissão deve ser em minúsculas com hífens (ex: gerir-usuários).',
        'description_too_long' => 'A descrição não pode exceder 1000 caracteres.',
        'category_too_long' => 'A categoria não pode exceder 100 caracteres.',
    ],

    // Help text
    'help' => [
        'naming_convention' => 'Use letras minúsculas com hífens entre palavras (ex: gerir-usuários, ver-relatórios)',
        'categories' => 'As categorias ajudam a organizar permissões. Categorias comuns: Usuários, Funções, Conteúdo, Definições, Relatórios',
        'system_permissions' => 'As permissões de sistema são permissões principais que não podem ser modificadas ou eliminadas.',
        'bulk_create' => 'Crie várias permissões de uma vez. Cada linha criará uma nova permissão.',
        'import_format' => 'Formato CSV: nome, categoria, descrição, nome_guard',
    ],
];
