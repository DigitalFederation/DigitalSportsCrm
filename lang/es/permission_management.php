<?php

return [
    'title' => 'Gestión de permisos',
    'permission' => 'Permiso',
    'permissions' => 'Permisos',
    'create_permission' => 'Crear permiso',
    'edit_permission' => 'Editar permiso',
    'delete_permission' => 'Eliminar permiso',
    'permission_details' => 'Detalles del permiso',
    'bulk_create' => 'Crear permisos de forma masiva',
    'import_permissions' => 'Importar permisos',
    'export_permissions' => 'Exportar permisos',

    // Form fields
    'name' => 'Nombre del permiso',
    'name_help' => 'Usa minúsculas con guiones (por ejemplo, manage-users)',
    'display_name' => 'Nombre para mostrar',
    'description' => 'Descripción',
    'category' => 'Categoría',
    'guard' => 'Guard',
    'guard_name' => 'Nombre del guard',
    'roles_using' => 'Roles que usan este permiso',
    'routes_using' => 'Rutas que usan este permiso',
    'created_by' => 'Creado por',
    'created_at' => 'Creado el',
    'updated_at' => 'Actualizado el',

    // Categories
    'uncategorized' => 'Sin categoría',
    'all_categories' => 'Todas las categorías',
    'select_category' => 'Seleccionar categoría',
    'new_category' => 'Nueva categoría',

    // Filters
    'filter_by_category' => 'Filtrar por categoría',
    'filter_by_usage' => 'Filtrar por uso',
    'has_roles' => 'Con roles',
    'no_roles' => 'Sin roles',
    'search_permissions' => 'Buscar permisos...',

    // Statistics
    'total_permissions' => 'Total de permisos',
    'system_permissions' => 'Permisos del sistema',
    'custom_permissions' => 'Permisos personalizados',
    'permissions_with_roles' => 'Permisos con roles',
    'unused_permissions' => 'Permisos no utilizados',
    'permissions_with_routes' => 'Permisos con rutas',

    // Actions
    'actions' => 'Acciones',
    'view' => 'Ver',
    'edit' => 'Editar',
    'delete' => 'Eliminar',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',
    'create' => 'Crear',
    'back_to_list' => 'Volver a los permisos',
    'confirm_delete' => 'Confirmar eliminación',

    // Bulk operations
    'bulk_operations' => 'Operaciones masivas',
    'add_permission_line' => 'Añadir permiso',
    'remove_line' => 'Eliminar',
    'default_category' => 'Categoría predeterminada',
    'default_guard' => 'Guard predeterminado',
    'apply_defaults' => 'Aplicar valores predeterminados',

    // Import/Export
    'select_file' => 'Seleccionar archivo CSV',
    'download_template' => 'Descargar plantilla',
    'import' => 'Importar',
    'export' => 'Exportar',

    // Status
    'system_permission' => 'Permiso del sistema',
    'protected' => 'Protegido',
    'deletable' => 'Eliminable',
    'in_use' => 'En uso',
    'not_used' => 'No utilizado',

    // Impact analysis
    'deletion_impact' => 'Impacto de la eliminación',
    'affected_roles' => 'Roles afectados',
    'affected_users' => 'Usuarios afectados',
    'affected_routes' => 'Rutas afectadas',
    'roles_list' => 'Roles con este permiso',

    // Messages
    'messages' => [
        'permission_created_successfully' => 'Permiso creado correctamente.',
        'permission_updated_successfully' => 'Permiso actualizado correctamente.',
        'permission_deleted_successfully' => 'Permiso eliminado correctamente.',
        'bulk_create_success' => ':count permisos creados correctamente.',
        'bulk_create_partial' => ':created permisos creados, :failed fallidos.',
        'import_success' => 'Importación completada: :created creados, :skipped omitidos.',
        'no_permissions_found' => 'No se han encontrado permisos.',
        'no_permissions_added' => 'Todavía no se han añadido permisos.',
        'confirm_delete_message' => '¿Seguro que quieres eliminar este permiso? Esta acción no se puede deshacer.',
    ],

    // Errors
    'errors' => [
        'permission_already_exists' => 'Ya existe un permiso con este nombre.',
        'cannot_modify_system_permission' => 'Los permisos del sistema no se pueden modificar.',
        'cannot_delete_system_permission' => 'Los permisos del sistema no se pueden eliminar.',
        'permission_used_by_protected_roles' => 'Este permiso lo utilizan roles protegidos: :roles',
        'permission_used_in_routes' => 'Este permiso se utiliza en :count ruta(s).',
        'permission_creation_failed' => 'No se ha podido crear el permiso: :error',
        'permission_update_failed' => 'No se ha podido actualizar el permiso: :error',
        'permission_deletion_failed' => 'No se ha podido eliminar el permiso: :error',
        'bulk_create_failed' => 'La creación masiva ha fallado: :error',
        'import_failed' => 'La importación ha fallado: :error',
        'invalid_permission_name' => 'El nombre del permiso debe estar en minúsculas y con guiones únicamente.',
    ],

    // Validation
    'validation' => [
        'name_required' => 'El nombre del permiso es obligatorio.',
        'name_unique' => 'Este nombre de permiso ya existe.',
        'name_format' => 'El nombre del permiso debe estar en minúsculas con guiones (por ejemplo, manage-users).',
        'description_too_long' => 'La descripción no puede superar los 1000 caracteres.',
        'category_too_long' => 'La categoría no puede superar los 100 caracteres.',
    ],

    // Help text
    'help' => [
        'naming_convention' => 'Usa letras minúsculas con guiones entre palabras (por ejemplo, manage-users, view-reports)',
        'categories' => 'Las categorías ayudan a organizar los permisos. Categorías comunes: Usuarios, Roles, Contenido, Configuración, Informes',
        'system_permissions' => 'Los permisos del sistema son permisos fundamentales que no se pueden modificar ni eliminar.',
        'bulk_create' => 'Crea varios permisos a la vez. Cada línea creará un nuevo permiso.',
        'import_format' => 'Formato CSV: name, category, description, guard_name',
        'guard_cannot_be_changed' => 'El guard no se puede cambiar después de la creación por motivos de seguridad.',
    ],
];
