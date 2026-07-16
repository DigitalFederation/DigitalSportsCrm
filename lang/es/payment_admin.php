<?php

return [
    'currency_unsupported' => 'No admite :currency',
    'currency_unsupported_hint' => 'Esta pasarela no puede cobrar en :currency, la moneda de la instalación. Está oculta en el pago.',
    // Page titles
    'payment_methods' => 'Métodos de pago',
    'payment_transactions' => 'Transacciones de pago',
    'webhook_logs' => 'Registros de webhooks',
    'edit_method' => 'Editar método de pago',
    'transaction_details' => 'Detalles de la transacción',
    'webhook_log_details' => 'Detalles del registro de webhook',

    // Navigation
    'manage_payment_methods' => 'Gestionar métodos de pago',
    'view_transactions' => 'Ver transacciones',
    'view_webhook_logs' => 'Ver registros de webhooks',

    // Statistics
    'total_transactions' => 'Total de transacciones',
    'total_webhooks' => 'Total de webhooks',
    'total_amount' => 'Importe total',
    'pending' => 'Pendiente',
    'successful' => 'Correctas',
    'failed' => 'Fallidas',
    'success_rate' => 'Tasa de éxito',
    'avg_processing_time' => 'Tiempo medio de procesamiento',
    'status_breakdown' => 'Desglose por estado',
    'today' => 'Hoy',

    // Table headers
    'id' => 'ID',
    'name' => 'Nombre',
    'driver' => 'Controlador',
    'handler' => 'Gestor',
    'status' => 'Estado',
    'instructions' => 'Instrucciones',
    'document' => 'Documento',
    'payment_method' => 'Método de pago',
    'amount' => 'Importe',
    'date' => 'Fecha',
    'gateway' => 'Pasarela',
    'request_id' => 'ID de solicitud',
    'transaction' => 'Transacción',
    'processing_time' => 'Tiempo de procesamiento',

    // Form labels
    'instructions_help' => 'Instrucciones que se muestran a los usuarios al seleccionar este método de pago.',
    'enabled' => 'Habilitado',
    'disabled' => 'Deshabilitado',
    'technical_info' => 'Información técnica',
    'note' => 'Nota',
    'easypay_config_note' => 'Las credenciales de EasyPay se configuran mediante variables de entorno. Contacta con un desarrollador para actualizar las claves de API.',

    // Gateway status
    'configured' => 'Configurado',
    'not_configured' => 'Sin configurar',
    'mode' => 'Modo',
    'sandbox' => 'Entorno de pruebas',
    'production' => 'Producción',
    'webhook_secret' => 'Secreto del webhook',
    'webhook_url' => 'URL del webhook',
    'available_methods' => 'Métodos disponibles',

    // Actions
    'enable' => 'Habilitar',
    'disable' => 'Deshabilitar',

    // Transaction details
    'transaction_info' => 'Información de la transacción',
    'document_info' => 'Información del documento',
    'payment_data' => 'Datos del pago',
    'comment' => 'Comentario',
    'created_at' => 'Creado el',
    'updated_at' => 'Actualizado el',
    'document_number' => 'Número de documento',
    'document_status' => 'Estado del documento',
    'document_total' => 'Total del documento',
    'owner' => 'Propietario',
    'view_document' => 'Ver documento',
    'no_document_associated' => 'No hay ningún documento asociado a esta transacción.',

    // Webhook log details
    'request_info' => 'Información de la solicitud',
    'related_records' => 'Registros relacionados',
    'ip_address' => 'Dirección IP',
    'received_at' => 'Recibido el',
    'request_headers' => 'Cabeceras de la solicitud',
    'webhook_payload' => 'Contenido del webhook',
    'response_sent' => 'Respuesta enviada',
    'no_transaction' => 'Sin transacción vinculada',
    'no_document' => 'Sin documento vinculado',

    // Filter labels
    'from_date' => 'Fecha desde',
    'to_date' => 'Fecha hasta',

    // Empty states
    'no_transactions_found' => 'No se encontraron transacciones.',
    'no_webhook_logs_found' => 'No se encontraron registros de webhooks.',
];
