<?php

return [
    // Validation messages
    'package_required' => 'Debe seleccionarse un paquete de afiliación',
    'invalid_package' => 'El paquete de afiliación seleccionado no es válido',
    'individuals_required' => 'Debe seleccionarse al menos una persona',
    'min_one_individual' => 'Selecciona al menos una persona',

    // Success messages
    'subscriptions_created' => 'Suscripciones creadas',
    'success_count' => 'Se crearon correctamente :count suscripciones',
    'payment_required_count' => ':count requieren documentos de pago',
    'free_subscriptions_count' => ':count son gratuitas y están activas',

    // Error messages
    'some_subscriptions_failed' => 'Algunas suscripciones fallaron',
    'failed_count' => 'No se pudieron crear :count suscripciones. Consulta los registros para más detalles',
    'error' => 'Error',
    'unexpected_error' => 'Se produjo un error inesperado al procesar las suscripciones',
    'unauthorized_action' => 'Acción no autorizada',

    // Action buttons
    'retry_failed' => 'Reintentar las fallidas',
    'retry_failed_title' => 'Reintentar suscripciones fallidas',
    'retry_failed_description' => '¿Deseas volver a intentar crear las suscripciones fallidas?',
    'yes_retry' => 'Sí, reintentar',
    'no_cancel' => 'No, cancelar',
    'try_again' => 'Intentar de nuevo',
    // Headers and titles
    'select_package' => 'Selecciona uno de los planes para asignarlo a los miembros seleccionados de tu entidad.',
    'select_insurance_package' => 'Selecciona uno de los planes de seguro para asignarlo a los miembros seleccionados de tu entidad.',
    'select_members' => 'Seleccionar miembros',
    'entity_member_memberships_title' => 'Planes de afiliación para miembros de la entidad',
    'entity_member_insurances_title' => 'Planes de seguro para miembros de la entidad',
    'selected' => 'Seleccionados',

    // Search and filters
    'search_placeholder' => 'Buscar miembros por nombre o ID...',
    'filter' => [
        'all_status' => 'Todos los estados',
        'active_subscription' => 'Suscripción activa',
        'no_subscription' => 'Sin suscripción',
    ],

    // Table headers
    'table' => [
        'name' => 'Nombre',
        'id' => 'ID',
        'status' => 'Estado',
    ],

    // Status labels
    'status' => [
        'active' => 'Activo',
        'no_subscription' => 'Sin suscripción',
    ],

    // Messages
    'no_members_found' => 'No se encontraron miembros que coincidan con tus criterios.',

    // Selection tray
    'selected_members' => 'Miembros seleccionados',
    'click_to_view' => 'haz clic para ver',
    'clear_all' => 'Borrar todo',
    'remove_selection' => 'Quitar de la selección',
    'total_selected' => ':count miembro(s) seleccionado(s)',
    'estimated_total' => 'Total estimado',

    // Actions
    'actions' => [
        'cancel' => 'Cancelar',
        'subscribe_selected' => 'Suscribir miembros seleccionados (:count)',
        'confirm' => 'Confirmar',
    ],

    // Modal
    'modal' => [
        'confirm_title' => 'Confirmar suscripción',
        'confirm_message' => 'Estás a punto de suscribir a los miembros seleccionados al siguiente paquete:',
        'price' => 'Precio',
        'subscription_count' => 'Esta acción creará nuevas suscripciones para :count miembros.',
    ],
];
