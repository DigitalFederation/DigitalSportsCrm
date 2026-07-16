<?php

return [
    // Page title
    'title' => 'Integración con Moloni',

    // Connection status
    'connection_status' => 'Estado de la conexión',
    'connected' => 'Conectado',
    'not_connected' => 'No conectado',
    'token_expires' => 'El token expira',
    'minutes_remaining' => 'minutos restantes',

    // Buttons
    'authorize' => 'Autorizar con Moloni',
    'disconnect' => 'Desconectar',
    'test_connection' => 'Probar conexión',
    'sync_now' => 'Sincronizar ahora',
    'save' => 'Guardar configuración',

    // Sync
    'sync_data' => 'Sincronizar datos desde Moloni',
    'last_sync' => 'Última sincronización',
    'no_sync_yet' => 'Todavía no se han sincronizado datos. Haz clic en "Sincronizar ahora" para obtener los datos de Moloni.',
    'sync_required' => 'Sincronización requerida',
    'sync_data_first' => 'Primero sincroniza los datos desde Moloni para completar las opciones de configuración.',

    // Configuration
    'configuration' => 'Configuración de facturas',
    'document_set' => 'Serie de documentos',
    'default_tax' => 'Impuesto predeterminado',
    'exempt_tax' => 'Impuesto exento (0% IVA)',
    'for_exempt_products' => 'para productos exentos',
    'exempt_tax_help' => 'Selecciona el impuesto del 0% que se utilizará para los productos exentos de IVA. Obligatorio cuando los planes tienen una tasa de IVA del 0%.',
    'no_exempt_tax_available' => 'No hay ninguna tasa de impuesto del 0% configurada en Moloni. Crea un impuesto del 0% en tu cuenta de Moloni y sincroniza los datos para habilitar esta opción.',
    'exemption_reason' => 'Motivo de exención',
    'required_for_exempt' => 'obligatorio para productos exentos',
    'exemption_reason_help' => 'Código legal del motivo de exención (por ejemplo, M07 para el Artículo 9 del CIVA). Requerido por Moloni para productos sin IVA.',
    'product_category' => 'Categoría de producto',
    'payment_method' => 'Método de pago',
    'unit' => 'Unidad de medida',
    'select_option' => 'Selecciona una opción...',
    'optional' => 'opcional',
    'category_help' => 'Solo es necesario si se crean nuevos productos en Moloni. No es necesario si los productos ya existen (identificados por referencia).',
    'auto_detect' => 'Detección automática según el pago',
    'payment_method_help' => 'Déjalo vacío para detectar automáticamente según el método de pago del documento (transferencia bancaria, Multibanco, etc.).',
    'unit_help' => 'Solo es necesario si se crean nuevos productos en Moloni. No es necesario si los productos ya existen (identificados por referencia).',

    // Status
    'status' => 'Estado de la integración',
    'ready' => 'Listo',
    'incomplete' => 'Configuración incompleta',
    'invoices_will_be_generated' => 'Las facturas se generarán automáticamente para los documentos pagados.',
    'complete_configuration' => 'Completa la configuración para habilitar la generación automática de facturas.',

    // Logs
    'recent_logs' => 'Registros de sincronización recientes',
    'no_logs' => 'No hay registros de sincronización disponibles.',
    'type' => 'Tipo',
    'date' => 'Fecha',
    'duration' => 'Duración',
    'details' => 'Detalles',

    // Messages
    'connected_successfully' => '¡Conexión con Moloni establecida correctamente!',
    'disconnected_successfully' => 'Desconectado de Moloni.',
    'connection_successful' => '¡Prueba de conexión correcta!',
    'connection_test_failed' => 'La prueba de conexión ha fallado. Comprueba tus credenciales.',
    'connection_failed' => 'Error de conexión: :error',
    'sync_completed' => 'Datos sincronizados correctamente. :count elementos obtenidos.',
    'sync_failed' => 'La sincronización ha fallado: :error',
    'settings_saved' => 'Configuración guardada correctamente.',
    'authorization_denied' => 'Autorización denegada: :error',
    'no_authorization_code' => 'No se ha recibido ningún código de autorización de Moloni.',
    'disconnect_confirm' => '¿Seguro que quieres desconectarte de Moloni? Esto eliminará los tokens almacenados.',

    // Warnings
    'integration_disabled' => 'Integración deshabilitada',
    'enable_in_env' => 'La integración con Moloni está deshabilitada actualmente. Establece MOLONI_ENABLED=true en tu archivo .env para habilitarla.',
    'missing_credentials' => 'Faltan credenciales',
    'add_credentials_to_env' => 'Añade MOLONI_CLIENT_ID y MOLONI_CLIENT_SECRET a tu archivo .env.',

    // New fields
    'company' => 'Empresa',
    'maturity_date' => 'Condiciones de pago',
    'days' => 'días',

    // Invoices
    'recent_invoices' => 'Facturas recientes',
    'no_invoices' => 'Todavía no se ha generado ninguna factura.',
    'failed_invoices' => 'Facturas fallidas',
    'document' => 'Documento',
    'moloni_number' => 'Número de Moloni',
    'moloni_status' => 'Estado',
    'total' => 'Total',
    'error' => 'Error',
    'actions' => 'Acciones',
    'retry' => 'Reintentar',

    // Manual operations
    'invoice_created' => 'Factura :number creada correctamente.',
    'invoice_not_created' => 'No se ha podido crear la factura (Moloni no está configurado o el documento no es elegible).',
    'invoice_creation_failed' => 'No se ha podido crear la factura: :error',
    'customer_synced' => 'Cliente sincronizado correctamente. ID de Moloni: :id',
    'customer_sync_failed' => 'La sincronización del cliente ha fallado: :error',

    // PDF and status
    'download_pdf' => 'Descargar PDF',
    'refresh_status' => 'Actualizar',
    'pdf_not_available' => 'El PDF no está disponible para esta factura.',
    'invoice_not_found' => 'No se ha encontrado ninguna factura de Moloni para este documento.',
    'pdf_download_failed' => 'La descarga del PDF ha fallado: :error',
    'status_refreshed' => 'Estado de la factura :number actualizado correctamente.',
    'status_refresh_failed' => 'La actualización del estado ha fallado: :error',
    'view_in_moloni' => 'Ver en Moloni',

    // Customer management
    'synced_customers' => 'Clientes sincronizados',
    'no_customers' => 'Todavía no se ha sincronizado ningún cliente.',
    'customer_name' => 'Nombre',
    'customer_vat' => 'Número de IVA',
    'customer_type' => 'Tipo',
    'moloni_id' => 'ID de Moloni',
    'individual' => 'Particular',
    'entity' => 'Entidad',
    'sync_customer_button' => 'Sincronizar cliente',

    // Bulk operations
    'retry_selected' => 'Reintentar seleccionados',
    'select_all' => 'Seleccionar todo',
    'bulk_retry_success' => ':count facturas reintentadas correctamente.',
    'bulk_retry_partial' => ':success facturas correctas, :failed facturas fallidas.',
    'bulk_retry_failed' => ':count facturas no se han podido reintentar.',
    'no_invoices_selected' => 'Selecciona al menos una factura para reintentar.',

    // Product reference
    'product_reference' => 'Referencia de Moloni',
    'product_reference_help' => 'Código de referencia único para asociar este plan a un producto de Moloni. Si se establece, el mismo producto se reutilizará en las facturas.',

    // Document series per type
    'document_series_by_type' => 'Series de documentos por tipo',
    'document_series_by_type_description' => 'Configura diferentes series de documentos para cada tipo de documento. Déjalo vacío para usar la serie predeterminada de arriba.',
    'owner_type_license' => 'Licencias',
    'owner_type_membership' => 'Cuotas de entidad',
    'owner_type_member_subscription' => 'Afiliaciones individuales',
    'owner_type_certification' => 'Certificaciones',
    'owner_type_enrollment' => 'Inscripciones de entidades (eventos)',
    'owner_type_individual_enrollment' => 'Inscripciones de personal/oficiales',
    'owner_type_athlete_enrollment' => 'Inscripciones de atletas (competiciones)',
    'owner_type_insurance' => 'Seguro',
    'use_default' => 'Usar predeterminado',

    // Document type
    'document_type' => 'Tipo de documento',
    'invoice_fatura' => 'Factura (Fatura)',
    'invoice_receipt_fatura_recibo' => 'Factura-Recibo (Fatura-Recibo)',
    'document_type_help' => 'La Factura-Recibo combina factura + pago. Requiere que la serie de documentos tenga habilitada la Fatura-Recibo en Moloni.',

    // Document status (draft vs finalized)
    'document_status' => 'Estado del documento',
    'status_finalized' => 'Finalizado (Cerrado)',
    'status_draft' => 'Borrador (Rascunho)',
    'document_status_help' => 'Las facturas en borrador requieren una finalización manual en Moloni antes de convertirse en documentos fiscales válidos. Úsalo para revisar antes de cerrar.',

    // Missing invoices
    'missing_invoices' => 'Documentos sin factura',
    'documents' => 'documentos',
    'create_invoices' => 'Crear facturas',
    'create_invoice' => 'Crear factura',
    'owner' => 'Titular',
    'paid_date' => 'Fecha de pago',
    'no_owner' => 'Sin titular',
    'showing_first_50' => 'Mostrando los primeros 50 de :count documentos. El resto se mostrará después de procesar estos.',
    'no_missing_invoices' => 'Todos los documentos pagados tienen sus facturas de Moloni creadas.',

    // Failure notification
    'notification_invoice_failed_subject' => 'Error al crear la factura en Moloni',
    'notification_invoice_failed_greeting' => 'Alerta de generación de factura',
    'notification_invoice_failed_intro' => 'El sistema no ha podido crear una factura en Moloni para el documento :document tras varios intentos.',
    'notification_invoice_failed_error' => 'Error: :error',
    'notification_invoice_failed_attempts' => 'El sistema lo ha intentado :attempts veces antes de desistir.',
    'notification_invoice_failed_action' => 'Ver ajustes de Moloni',
    'notification_invoice_failed_document_link' => 'Puedes ver el documento en: :url',
    'notification_invoice_failed_database' => 'Error al crear la factura de Moloni para el documento :document',

    // Invoice generation rules
    'invoice_generation_rules' => 'Reglas de generación de facturas',
    'invoice_generation_rules_description' => 'Selecciona qué tipos de detalle de documento deben activar la generación de facturas en Moloni. Los tipos no marcados omitirán la creación de facturas.',
    'invoice_generation_rules_saved' => 'Reglas de generación de facturas guardadas correctamente.',
    'save_invoice_rules' => 'Guardar reglas de facturación',
    'require_all_details_enabled' => 'Requerir que todos los tipos de detalle estén habilitados',
    'require_all_details_enabled_help' => 'Si se marca, las facturas solo se crearán cuando TODOS los tipos de detalle del documento estén habilitados. Si no se marca, las facturas se crean cuando esté presente CUALQUIER tipo habilitado.',

    // Committee-based document series
    'committee_document_series' => 'Series de documentos por comité',
    'committee_document_series_description' => 'Selecciona la serie de documentos para licencias y certificaciones según su comité. Esto tiene prioridad sobre la asignación por tipo de abajo.',
    'committee_diving' => 'Comité de Buceo',
    'committee_scientific' => 'Comité Científico',
    'committee_sport' => 'Comité Deportivo',
    'committee_divingservices' => 'Comité de Servicios de Buceo',

    // Warnings and validation
    'warning' => 'Advertencia',
    'document_set_not_in_cache' => 'La serie de documentos configurada (ID: :id) no está en los datos sincronizados.',
    'sync_to_refresh' => 'Haz clic en "Sincronizar datos" para actualizar las series de documentos disponibles de Moloni.',
    'not_in_cache' => 'No está en los datos sincronizados',
    'no_at_codes' => 'Sin códigos AT: no válido para facturas',

    // Activity log
    'activity_log_description' => 'Actividad reciente de facturas y sincronización',
    'invoice_created_title' => 'Factura creada',
    'invoice_failed_title' => 'Factura fallida',
    'sync_completed_title' => 'Sincronización de datos completada',
    'sync_failed_title' => 'Sincronización de datos fallida',
    'success' => 'Correcto',
    'failed' => 'Fallido',
    'view_document' => 'Ver documento',
    'companies_synced' => 'empresas',
    'series_synced' => 'series',
    'taxes_synced' => 'impuestos',
    'categories_synced' => 'categorías',
];
