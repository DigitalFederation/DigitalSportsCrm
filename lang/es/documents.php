<?php

$primaryName = config('branding.primary.name', 'Example Federation');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'payment_documents' => 'Documentos de pago',
    'payment_documents_disclaimer' => 'Estos documentos son solo a título informativo, no tienen validez legal. Para cada documento debe emitirse una factura-recibo mediante un programa de contabilidad certificado.',
    'invoices' => 'Facturas',
    'create_manual_order' => 'Crear pedido manual',
    'latest_documents' => 'Últimos documentos',
    'filtered_results' => 'Resultados filtrados',
    'entities' => 'Entidades',
    'member' => 'Miembro',

    // Table headers
    'number' => '# Número',
    'type' => 'Tipo',
    'document_name' => 'Nombre del documento',
    'status' => 'Estado',
    'issue_date' => 'Fecha de emisión',
    'expiration_date' => 'Fecha de vencimiento',
    'total' => 'Total',
    'id' => 'ID',
    'download' => 'Descargar',
    'category' => 'Categoría',

    // Document detail page
    'document_detail' => 'Detalle del documento',
    'payment' => 'Pago',
    'select_method' => 'Selecciona un método',
    'proceed_to_payment' => 'Proceder al pago',

    // Document info labels
    'number_label' => 'Número',
    'type_label' => 'Tipo',
    'date_label' => 'Fecha',
    'recipient' => 'Destinatario',
    'vat_number' => 'Número de IVA',
    'city' => 'Ciudad',
    'address' => 'Dirección',
    'postal_code' => 'Código postal',
    'country' => 'País',

    // Table columns
    'product' => 'Producto',
    'qty' => 'Cant.',
    'unit_price' => 'Precio unitario',
    'amount' => 'Importe',
    'subtotal' => 'Subtotal',
    'amount_paid' => 'Importe pagado',
    'remaining_balance' => 'Saldo pendiente',

    // Payment status
    'document_is_paid' => 'Este documento ya está pagado',
    'find_details_below' => 'Consulta los detalles a continuación',
    'view_moloni_invoice' => 'Ver factura/recibo',
    'document_type' => 'Tipo de documento',
    'created_at' => 'Creado el',
    'transactions' => 'Transacciones',
    'transaction_status' => 'Estado',
    'transaction_date' => 'Fecha',
    'transaction_info' => 'Información',
    'associated_documents' => 'Documentos asociados',

    // Filters
    'year' => 'Año',
    'document_number' => 'Número de documento',
    'filter_cmas_code_help' => 'Buscar por el código internacional del titular',
    'filter_member_placeholder' => 'Nombre del miembro',
    'organization' => 'Organización',
    'national_organization' => 'Organización nacional',
    'date_from' => 'Fecha desde',
    'date_to' => 'Fecha hasta',
    'payment_date' => 'Fecha de pago',

    // Index page filters
    'filters' => [
        'category' => 'Categoría',
        'status' => 'Estado',
        'type' => 'Tipo',
    ],

    // Index page table
    'table' => [
        'number' => '# Número',
        'date' => 'Fecha',
        'type' => 'Tipo',
        'status' => 'Estado',
        'total' => 'Total',
    ],

    // Document manual create
    'attention' => 'Atención',
    'document_no' => 'N.º de documento',
    'due_date' => 'Fecha de vencimiento',
    'federation' => 'Federación',
    'entity' => 'Entidad',
    'individual' => 'Particular',
    'manual_entry' => 'Entrada manual',
    'select_federation' => 'Seleccionar federación',
    'select_federation_option' => '-- Seleccionar federación --',
    'select_entity' => 'Seleccionar entidad',
    'select_entity_option' => '-- Seleccionar entidad --',
    'search_individual' => 'Buscar particular',
    'search_individual_placeholder' => 'Introduce el n.º de afiliación, nombre o correo electrónico',
    'active_member' => 'Miembro activo',
    'birth_date' => 'Fecha de nacimiento',
    'manual_customer_entry' => 'Entrada manual de cliente',
    'customer_name' => 'Nombre del cliente',
    'document_state' => 'Estado del documento',
    'description' => 'Descripción',
    'delete' => 'Eliminar',
    'add_invoice_items' => 'Añadir líneas de factura',
    'document_line' => 'Línea de documento',
    'products' => 'Productos',
    'select_product' => '-- Seleccionar producto --',
    'or' => 'O',
    'product_service' => 'Producto/Servicio',
    'vat_percentage' => '% IVA',
    'add_item' => 'Añadir línea',
    'notes' => 'Notas',
    'save_document' => 'Guardar documento',

    // Moloni invoice
    'create_moloni_invoice' => 'Crear factura en Moloni',
    'create_moloni_invoice_description' => 'Marca esta opción para crear automáticamente una factura en Moloni para este pago.',

    // Owner type categories (for document filters)
    'categories' => [
        'License' => 'Licencia',
        'Membership' => 'Suscripción',
        'Document' => 'Documento',
        'Certification' => 'Certificación',
        'Registration' => 'Inscripción',
        'Manual Order' => 'Pedido manual',
        'Insurance' => 'Seguro',
    ],

    // Document states
    'states' => [
        'paid' => 'Pagado',
        'draft' => 'Borrador',
        'pending' => 'Pendiente',
        'canceled' => 'Cancelado',
        'partially_paid' => 'Pagado parcialmente',
        'void' => 'Anulado',
    ],

    // Action messages
    'edit_draft_only' => 'La edición solo se permite para documentos en estado de borrador.',
    'notification_sent' => 'Notificación enviada.',
    'document_canceled_successfully' => 'Documento cancelado correctamente.',
    'not_cancellable_state' => 'El documento no está en un estado que se pueda cancelar.',
    'has_associated_payments' => 'El documento no se puede eliminar porque tiene pagos asociados.',
    'no_invoices_found' => 'No se han encontrado facturas que coincidan con los criterios especificados.',
    'export_failed' => 'No se ha podido generar la exportación. Inténtalo de nuevo o contacta con soporte.',

    // Confirmations
    'confirm_delete_warning' => '¿Seguro que quieres eliminar este documento? Esta acción es irreversible y eliminará todos los datos asociados.',
    'confirm_cancel_warning' => '¿Seguro que quieres cancelar este documento?',
    'document_deleted_successfully' => 'Documento eliminado correctamente.',

    // Buttons
    'resend_notification' => 'Reenviar notificación',
    'delete_document' => 'Eliminar documento',

    // Filter labels
    'document_period' => 'Periodo del documento',

    // Invoice/Order PDF labels
    'pdf' => [
        'name' => 'Nombre',
        'city' => 'Ciudad',
        'address' => 'Dirección',
        'date' => 'Fecha',
        'vat_number' => 'Número de IVA',
        'postal_code' => 'Código postal',
        'member_number' => 'N.º de miembro',
        'country' => 'País',
        'notes' => 'Notas',
        'description' => 'DESCRIPCIÓN',
        'qty' => 'CANT.',
        'unit_price' => 'PRECIO UNITARIO',
        'total' => 'TOTAL',
        'subtotal' => 'Subtotal',
        'tax' => 'Impuesto',
        'order_disclaimer' => 'Este documento no constituye una factura ni un recibo. El documento fiscal válido se emitirá tras la confirmación del pago, mediante un programa de facturación certificado de acuerdo con la legislación vigente.',
    ],

    // Invoice PDF compliance text
    'invoice_compliance_en' => "Entities and individuals hereby undertake to comply with and strictly enforce {$primaryShortName} rules, as well as to urge their members to adopt an underwater environmental friendly attitude.",
    'invoice_compliance_pt' => "As entidades e individuos comprometem-se por este documento a aplicar e fazer aplicar rigorosamente as regras de {$primaryShortName} e a incentivar os seus membros a adotar uma atitude respeitosa pelo ambiente subaquatico.",
];
