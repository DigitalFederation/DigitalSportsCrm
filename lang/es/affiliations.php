<?php

return [
    // Page titles and headers
    'title' => 'Afiliaciones',
    'info_title' => 'Gestión de afiliaciones',
    'info_body' => 'Consulta y gestiona todas las afiliaciones del sistema. Supervisa las afiliaciones de los miembros, su estado y las federaciones asociadas.',

    // Filter labels
    'member_type' => 'Tipo de miembro',
    'status' => 'Estado',
    'federation' => 'Federación',
    'member_name' => 'Nombre del miembro',
    'start_date' => 'Fecha de inicio',
    'end_date' => 'Fecha de fin',

    // Table headers
    'table' => [
        'member' => 'Miembro',
        'type' => 'Tipo',
        'federation' => 'Federación',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'fee' => 'Cuota',
        'status' => 'Estado',
    ],

    // Status labels
    'statuses' => [
        'active' => 'Activa',
        'inactive' => 'Inactiva',
        'suspended' => 'Suspendida',
        'expired' => 'Caducada',
        'pending_payment' => 'Pago pendiente',
    ],

    // Actions
    'view_member' => 'Ver miembro',
    'delete' => 'Eliminar',
    'member' => 'Miembro',

    // Data placeholders
    'member_not_found' => 'Miembro no encontrado',
    'no_federation' => 'Sin federación',
    'no_date' => 'Sin fecha',
    'no_fee' => 'Sin cuota',
    'via_entity' => 'a través de la entidad',

    // Messages
    'no_affiliations_found' => 'No se encontraron afiliaciones que coincidan con tus criterios.',
    'affiliations_empty' => 'Todavía no se ha creado ninguna afiliación.',
    'status_updated_successfully' => 'Estado de la afiliación actualizado correctamente.',
    'status_update_failed' => 'No se pudo actualizar el estado de la afiliación. Inténtalo de nuevo.',
    'deleted_successfully' => 'Afiliación eliminada correctamente.',
    'delete_failed' => 'No se pudo eliminar la afiliación. Inténtalo de nuevo.',

    // Delete confirmation
    'confirm_delete_title' => 'Eliminar afiliación',
    'confirm_delete_message' => '¿Estás seguro de que deseas eliminar esta afiliación? Esta acción no se puede deshacer.',
    'delete_confirm' => 'Eliminar afiliación',

    // Status change confirmation
    'confirm_status_change' => '¿Estás seguro de que deseas cambiar el estado de esta afiliación?',

    // Individual profile table
    'active_affiliations' => 'Afiliaciones activas',
    'affiliation_count' => '{0} Sin afiliaciones|{1} :count afiliación|[2,*] :count afiliaciones',
    'no_active_affiliations' => 'No hay afiliaciones activas',
    'plan' => 'Plan',
    'period' => 'Período',
    'privileges' => 'Privilegios',
    'standard_plan' => 'Plan estándar',
    'until' => 'hasta',
    'active' => 'Activa',
    'expired' => 'Caducada',
    'validation_plan' => 'Plan de validación',
    'insurance_requests' => 'Solicitudes de seguro',
    'license_requests' => 'Solicitudes de licencia',
    'standard' => 'Estándar',
];
