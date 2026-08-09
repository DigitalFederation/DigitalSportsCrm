<?php

return [
    'title' => 'Gestión de permisos de rutas',
    'route_permissions' => 'Permisos de rutas',
    'scan_routes' => 'Escanear rutas',
    'assign_permissions' => 'Asignar permisos',
    'bulk_assign' => 'Asignación masiva',
    'routes_to_assign' => 'Rutas a las que asignar permisos',
    'route_details' => 'Detalles de la ruta',
    'permission_mapping' => 'Asignación de permisos',

    // Route fields
    'route_name' => 'Nombre de la ruta',
    'uri' => 'Patrón de URI',
    'methods' => 'Métodos HTTP',
    'controller' => 'Controlador',
    'middleware' => 'Middleware',
    'current_permission' => 'Permiso actual',
    'assigned_permission' => 'Permiso asignado',
    'suggested_permissions' => 'Permisos sugeridos',
    'module' => 'Módulo',
    'prefix' => 'Prefijo',
    'parameters' => 'Parámetros',
    'status' => 'Estado',
    'uncategorized' => 'Sin categoría',

    // Filters
    'filter_by_module' => 'Filtrar por módulo',
    'all_modules' => 'Todos los módulos',
    'all_permissions' => 'Todos los permisos',
    'filter_by_prefix' => 'Filtrar por prefijo',
    'filter_by_permission' => 'Filtrar por estado del permiso',
    'has_permission' => 'Con permiso',
    'no_permission' => 'Sin permiso',
    'all_routes' => 'Todas las rutas',
    'search_routes' => 'Buscar rutas...',

    // Statistics
    'total_routes' => 'Total de rutas',
    'routes_with_permissions' => 'Rutas con permisos',
    'routes_without_permissions' => 'Rutas sin permisos',
    'percentage_protected' => 'Cobertura de protección',
    'dynamic_mappings' => 'Asignaciones dinámicas',
    'active_mappings' => 'Asignaciones activas',

    // Actions
    'scan' => 'Escanear',
    'assign' => 'Asignar',
    'assign_permission' => 'Asignar permiso',
    'edit_permission_assignment' => 'Editar asignación de permiso',
    'current_permissions' => 'Permisos actuales',
    'no_permissions_assigned' => 'No hay permisos asignados',
    'add_permission' => 'Añadir permiso',
    'confirm_remove_permission' => '¿Seguro que quieres eliminar este permiso?',
    'remove' => 'Eliminar',
    'activate' => 'Activar',
    'deactivate' => 'Desactivar',
    'select_all' => 'Seleccionar todo',
    'deselect_all' => 'Deseleccionar todo',
    'apply_suggestions' => 'Aplicar sugerencias',
    'create_permission' => 'Crear permiso',
    'export_mappings' => 'Exportar asignaciones',

    // Route groups
    'grouped_by_module' => 'Rutas agrupadas por módulo',
    'module_statistics' => 'Estadísticas del módulo',
    'route_group' => 'Grupo de rutas',

    // Permission assignment
    'select_permission' => 'Seleccionar permiso',
    'no_permission_assigned' => 'Ningún permiso asignado',
    'permission_exists' => 'El permiso existe',
    'permission_not_exists' => 'El permiso no existe',
    'create_and_assign' => 'Crear y asignar',
    'active' => 'Activo',
    'inactive' => 'Inactivo',

    // Bulk operations
    'bulk_operations' => 'Operaciones masivas',
    'selected_routes' => 'Rutas seleccionadas',
    'bulk_assign_permissions' => 'Asignar permisos de forma masiva',
    'apply_to_selected' => 'Aplicar a los seleccionados',
    'preview_changes' => 'Vista previa de los cambios',

    // Impact preview
    'impact_preview' => 'Vista previa del impacto',
    'new_mappings' => 'Nuevas asignaciones',
    'updated_mappings' => 'Asignaciones actualizadas',
    'removed_mappings' => 'Asignaciones eliminadas',
    'affected_routes' => 'Rutas afectadas',
    'affected_permissions' => 'Permisos afectados',

    // Messages
    'messages' => [
        'no_routes_selected' => 'No se ha seleccionado ninguna ruta. Selecciona al menos una ruta.',
        'permission_updated' => 'Permiso de ruta actualizado correctamente.',
        'permission_assigned' => 'Permiso asignado correctamente.',
        'permission_removed' => 'Permiso de ruta eliminado correctamente.',
        'bulk_assign_success' => 'Asignación masiva completada: :created creadas, :updated actualizadas.',
        'scan_complete' => 'Escaneo de rutas completado. Se han encontrado :count rutas.',
        'export_success' => 'Permisos de rutas exportados correctamente.',
        'no_routes_found' => 'No se han encontrado rutas que coincidan con los criterios.',
        'confirm_remove' => '¿Seguro que quieres eliminar esta asignación de permiso?',
        'try_adjusting_filters' => 'Prueba a ajustar tus filtros o criterios de búsqueda.',
        'routes_selected' => 'rutas seleccionadas',
        'assigning' => 'Asignando...',
        'assigned' => 'Asignado',
    ],

    // Errors
    'errors' => [
        'bulk_assign_failed' => 'La asignación masiva ha fallado: :error',
        'assignment_failed' => 'La asignación ha fallado: :error',
        'permission_update_failed' => 'No se ha podido actualizar el permiso de ruta: :error',
        'scan_failed' => 'El escaneo de rutas ha fallado: :error',
        'export_failed' => 'La exportación ha fallado: :error',
    ],

    // Help text
    'help' => [
        'route_scanning' => 'El escaneo analiza todas las rutas registradas en la aplicación para identificar cuáles tienen permisos y cuáles los necesitan.',
        'permission_suggestions' => 'Las sugerencias se basan en los patrones de nomenclatura de las rutas y en las operaciones CRUD comunes.',
        'dynamic_mappings' => 'Las asignaciones dinámicas te permiten asignar permisos a las rutas sin modificar el código.',
        'bulk_assignment' => 'Selecciona varias rutas y asigna el mismo permiso a todas a la vez.',
        'protection_coverage' => 'Muestra el porcentaje de rutas que tienen protección por permisos.',
    ],
];
