<?php

return [
    'title' => 'Problemas de integración',
    'subtitle' => 'Vista consolidada de los errores de integración de Moloni y Easypay',

    // Statistics
    'total_errors' => 'Total de errores',
    'errors_today' => 'Errores hoy',
    'last_30_days' => 'Últimos 30 días',
    'last' => 'Últimos',

    // Error types
    'moloni_error_types' => 'Tipos de error de Moloni',
    'easypay_error_types' => 'Tipos de error de Easypay',

    // Filters
    'integration' => 'Integración',
    'from_date' => 'Fecha desde',
    'to_date' => 'Fecha hasta',

    // Table
    'recent_errors' => 'Errores recientes',
    'showing_count' => 'Mostrando :count errores',
    'type' => 'Tipo',
    'error_message' => 'Mensaje de error',
    'reference' => 'Referencia',
    'date' => 'Fecha',
    'retry' => 'Reintentar',

    // Empty state
    'no_errors' => 'No hay errores de integración',
    'no_errors_description' => 'Todas las integraciones funcionan correctamente en el período seleccionado.',

    // Navigation
    'moloni_settings' => 'Configuración de Moloni',
    'webhook_logs' => 'Registros de webhooks',

    // Troubleshooting
    'troubleshooting_title' => 'Consejos habituales de resolución de problemas',
    'troubleshooting_moloni_auth' => 'Errores de autenticación de Moloni: comprueba si la conexión con Moloni sigue activa en la configuración de Moloni.',
    'troubleshooting_moloni_config' => 'Errores de factura de Moloni: verifica que el conjunto de documentos, el impuesto y otros ajustes estén configurados correctamente.',
    'troubleshooting_easypay_webhook' => 'Errores de webhook de Easypay: comprueba si la transacción existe y si el estado del pago es correcto.',
    'troubleshooting_easypay_transaction' => 'Errores de transacción de Easypay: verifica el estado del documento y la configuración del pago.',
];
