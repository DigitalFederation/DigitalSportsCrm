<?php

$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalName = config('branding.international.name', 'International Federation');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    // Page titles
    'licenses' => 'Licencias',
    'my_licenses_description' => 'Aquí puede ver todas sus licencias y adquirir nuevas licencias de miembro',
    'view_my_licenses' => 'Ver Mis Licencias',
    'no_federation_association_description' => 'No está asociado a ninguna federación. Por favor, contacte con el administrador de su federación para establecer esta asociación antes de adquirir licencias.',
    'no_international_license_access_description' => 'No está asociado a una federación que tenga acuerdos de licencia internacional. Solo los miembros de federaciones con acuerdos internacionales pueden adquirir estas licencias.',

    // Tab sections
    'basic_information' => 'Información Básica',
    'roles_permissions' => 'Roles y Permisos',
    'requirements' => 'Requisitos',
    'pricing' => 'Precios',
    'availability' => 'Disponibilidad',
    'advanced_settings' => 'Configuración Avanzada',

    // Document requirements sections
    'diving_professionals' => 'Profesionales de Buceo',

    // Purchase page titles and headers
    'Purchase License' => 'Adquirir Licencia',
    'Manage Licenses' => 'Gestionar Licencias',
    'Manage Licenses for' => 'Gestionar Licencias para',
    'License Purchased Successfully!' => '¡Licencia Adquirida Correctamente!',
    'Purchase Successful!' => '¡Compra Exitosa!',
    'Purchase Successful' => 'Compra Exitosa',
    'order_details' => 'Detalles del Pedido',

    // Page descriptions
    'Select and purchase a license for yourself' => 'Seleccione y adquiera una licencia para usted',
    'Purchase licenses for your entity or members' => 'Adquiera licencias para su entidad o miembros',

    // Information messages
    'Information' => 'Información',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. Please ensure your profile information is complete before proceeding.' => 'Seleccione una licencia y proceda al pago. Su licencia se activará automáticamente una vez confirmado el pago. Por favor, asegúrese de que la información de su perfil esté completa antes de continuar.',
    'Select a license and proceed to payment. Your license will be activated automatically once payment is confirmed. For group purchases, you can select multiple members to receive the same license.' => 'Seleccione una licencia y proceda al pago. Su licencia se activará automáticamente una vez confirmado el pago. Para compras en grupo, puede seleccionar varios miembros para recibir la misma licencia.',

    // Form labels and options
    'Select Federation' => 'Seleccionar Federación',
    'Select a federation...' => 'Seleccione una federación...',
    'Select License' => 'Seleccionar Licencia',
    'Purchase Type' => 'Tipo de Compra',
    'Individual License' => 'Licencia Individual',
    'Group Purchase' => 'Compra en Grupo',
    'Select Member' => 'Seleccionar Miembro',
    'Select Members' => 'Seleccionar Miembros',
    'Select a member...' => 'Seleccione un miembro...',

    // Purchase type descriptions
    'Purchase license for one specific member' => 'Adquirir licencia para un miembro específico',
    'Purchase licenses for multiple members' => 'Adquirir licencias para varios miembros',

    // License information
    'License' => 'Licencia',
    'License Code' => 'Código de Licencia',
    'License Holder' => 'Titular de la Licencia',
    'License Information' => 'Información de la Licencia',
    'per license' => 'por licencia',
    'license' => 'Licencia',
    'start_date' => 'Fecha de Inicio',
    'expiry_date' => 'Fecha de Caducidad',
    'status' => 'Estado',

    // Purchase summary
    'Purchase Summary' => 'Resumen de la Compra',
    'Purchase Details' => 'Detalles de la Compra',
    'Entity' => 'Entidad',
    'Federation' => 'Federación',
    'Number of Members' => 'Número de Miembros',
    'Price per License' => 'Precio por Licencia',
    'Total' => 'Total',
    'Total Amount' => 'Importe Total',
    'Total Paid' => 'Total Pagado',

    // Status and dates
    'Status' => 'Estado',
    'Active' => 'Activo',
    'Payment Confirmed' => 'Pago Confirmado',
    'Issue Date' => 'Fecha de Emisión',
    'Expiration Date' => 'Fecha de Caducidad',
    'Today' => 'Hoy',
    'Permanent' => 'Permanente',

    // International and codes
    'Pending Assignment' => 'Asignación Pendiente',
    'Order Number' => 'Número de Pedido',

    // Success messages
    'Your license has been activated and is ready to use' => 'Su licencia se ha activado y está lista para usar',
    'Your license purchase has been completed successfully' => 'La compra de su licencia se ha completado correctamente',
    'All selected members have been automatically licensed' => 'Todos los miembros seleccionados han recibido su licencia automáticamente',
    'Your entity license has been automatically activated' => 'La licencia de su entidad se ha activado automáticamente',

    // Certificate information
    'Your License Certificate' => 'Su Certificado de Licencia',
    'Your license certificate is now available for download' => 'Su certificado de licencia ya está disponible para descargar',
    'License certificates are now available for download' => 'Los certificados de licencia ya están disponibles para descargar',
    'A confirmation email has been sent to your registered email address' => 'Se ha enviado un correo electrónico de confirmación a su dirección de correo registrada',
    'You will receive email confirmation shortly' => 'Recibirá una confirmación por correo electrónico en breve',

    // Next steps and information
    'What happens next?' => '¿Qué sucede a continuación?',
    'Important Information' => 'Información Importante',
    'Remember to renew before expiration date' => 'Recuerde renovar antes de la fecha de caducidad',

    // Action buttons
    'View My Licenses' => 'Ver Mis Licencias',
    'Download Invoice' => 'Descargar Factura',
    'Download Certificate' => 'Descargar Certificado',
    'Back to Dashboard' => 'Volver al Panel',

    // Error messages
    'no_license_purchase_found' => 'No se encontró ninguna compra de licencia.',
    'entity_license_required_for_members' => 'Su entidad debe tener una licencia de entidad activa antes de poder adquirir licencias de miembro. Por favor, adquiera primero una licencia de entidad.',
    'entity_sport_license_required' => 'Su entidad debe tener una licencia de entidad activa para este deporte antes de poder adquirir licencias de miembro para él. Por favor, adquiera primero una licencia de entidad para este deporte.',
    'No licenses available' => 'No hay licencias disponibles',
    'There are no licenses available for purchase in this federation at the moment.' => 'Actualmente no hay licencias disponibles para adquirir en esta federación.',
    'There are no licenses available for entity purchase at the moment.' => 'Actualmente no hay licencias disponibles para la compra por parte de entidades.',
    'No Federation Association' => 'Sin Asociación a Federación',
    'You are not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'No está asociado a ninguna federación. Por favor, contacte con el administrador de su federación para establecer esta asociación antes de adquirir licencias.',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing licenses.' => 'Su entidad no está asociada a ninguna federación. Por favor, contacte con el administrador de su federación para establecer esta asociación antes de adquirir licencias.',
    'No federation' => 'Sin federación',

    // Dynamic messages with parameters
    'Purchase for' => 'Adquirir por',
    'Purchase for €:amount' => 'Adquirir por €:amount',
    'Request Free License' => 'Solicitar Licencia Gratuita',
    ':count members selected' => ':count miembros seleccionados',
    'This license certifies you for: :role' => 'Esta licencia le certifica para: :role',
    'Valid for sport: :sport' => 'Válido para el deporte: :sport',
    'members' => 'miembros',
    'Members' => 'Miembros',

    // Federation License Manager
    'Select which licenses this federation can offer to its member entities.' => 'Seleccione qué licencias puede ofrecer esta federación a sus entidades miembro.',
    'Search Licenses' => 'Buscar Licencias',
    'Search by name or code...' => 'Buscar por nombre o código...',
    'Filter by Committee' => 'Filtrar por Comité',
    'All Committees' => 'Todos los Comités',
    'selected' => 'seleccionado',
    'International' => 'Internacional',
    'No licenses found matching your filters.' => 'No se encontraron licencias que coincidan con sus filtros.',
    'No licenses available.' => 'No hay licencias disponibles.',
    'license(s) selected' => 'licencia(s) seleccionada(s)',
    'Cancel' => 'Cancelar',
    'Save Changes' => 'Guardar Cambios',
    'Licenses updated successfully!' => '¡Licencias actualizadas correctamente!',

    // Debug information messages
    'cannot_proceed_with_purchase' => 'No se puede proceder con la compra:',
    'entity_no_active_affiliation' => 'La entidad no tiene una afiliación activa',
    'no_license_selected' => 'Ninguna licencia seleccionada',
    'price_not_calculated' => 'Precio no calculado',
    'calculated_price' => 'precio calculado',
    'no_members_selected' => 'Ningún miembro seleccionado',
    'no_members_for_entity' => 'No se encontraron miembros para esta entidad. Por favor, asegúrese de que su entidad tenga individuos asociados.',
    'validation_plan' => 'Plan de validación',

    // Affiliation messages
    'Active Affiliation Required' => 'Se Requiere Afiliación Activa',
    'Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.' => 'Su entidad debe tener una afiliación activa (paquete de membresía) para adquirir licencias. Por favor, asegúrese de que la membresía de su entidad esté activa y pagada antes de continuar.',
    'You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Debe tener una afiliación activa (paquete de membresía) para adquirir licencias. Por favor, asegúrese de que su membresía individual esté activa y pagada antes de continuar.',

    // License validation error messages
    'already_has_license' => 'Ya tiene una licencia :status de este tipo',
    'Your profile already has this Active License' => 'Su perfil ya tiene esta Licencia Activa',
    'Your license is pending payment' => 'Su licencia está pendiente de pago',
    'missing_required_documents_detailed' => 'No se puede solicitar esta licencia. Faltan los siguientes documentos requeridos: :documents. Por favor, suba estos documentos en la sección de Documentos Oficiales antes de solicitar esta licencia.',
    'missing_required_certifications' => 'No se puede solicitar esta licencia. Faltan las siguientes certificaciones requeridas: :certifications. Por favor, obtenga estas certificaciones antes de solicitar esta licencia.',
    'members_missing_required_certifications' => 'Los siguientes miembros no tienen las certificaciones requeridas: :members',
    'license_requirements' => 'Requisitos de la Licencia',
    'required_certifications' => 'Certificaciones requeridas',
    'required_documents' => 'Documentos requeridos',
    'member_missing_certifications' => 'Certificaciones faltantes: :certifications',
    'member_missing_documents' => 'Documentos faltantes: :documents',
    'member_must_have_active_affiliation' => 'El miembro debe tener una afiliación activa',
    'show_ineligible_members' => 'Mostrar miembros no elegibles',
    'hide_ineligible_members' => 'Ocultar miembros no elegibles',
    'member_not_eligible' => 'Este miembro no cumple los requisitos',
    'no_eligible_members' => 'No hay miembros elegibles para esta licencia',
    'some_members_ineligible' => ':eligible de :total miembros son elegibles para esta licencia',
    'entity' => 'entidad',
    'individual' => 'individuo',
    'license_cannot_be_purchased_by' => 'Esta licencia no puede ser adquirida por :type',
    'license_request_not_authorized' => 'Solicitud de licencia no autorizada: :reason',
    'license_parameter_null' => 'El parámetro de licencia es nulo',
    'license_missing_properties' => 'A la licencia le faltan propiedades requeridas (id o license_code)',
    'cannot_determine_federation' => 'No se puede determinar la federación para la compra de la licencia',
    'license_price_not_configured' => 'El precio de la licencia no está configurado para este tipo de comprador',

    // License fields
    'license_type' => 'Tipo de Licencia',
    'license_number' => 'Número de Licencia',
    'valid_until' => 'Válido Hasta',
    'acceptance_date' => 'Fecha de Aceptación',
    'issue_date' => 'Fecha de Emisión',
    'expiration_date' => 'Fecha de Caducidad',

    // Error messages for purchase flow
    'This license is not free' => 'Esta licencia no es gratuita.',
    'This license cannot be purchased with this method' => 'Esta licencia no se puede adquirir con este método.',

    // License status messages
    'Your profile already has this Active License' => 'Su perfil ya tiene esta Licencia Activa',
    'Your license is pending payment' => 'Su licencia está pendiente de pago',
    'Your license is pending admin validation' => 'Su licencia está pendiente de validación por el administrador',
    'Your license is pending technical director approval' => 'Su licencia está pendiente de aprobación del director técnico',
    'Your license is being processed' => 'Su licencia se está procesando',

    // New form translations
    'Search licenses' => 'Buscar Licencias',
    'Search licenses...' => 'Buscar licencias...',
    'licenses found' => 'licencias encontradas',
    'Sport Committee' => 'Comité Deportivo',
    'All Sports' => 'Todos los Deportes',
    'Role' => 'Rol',
    'Price' => 'Precio',
    'Free' => 'Gratis',
    'Select' => 'Seleccionar',
    'Purchase' => 'Adquirir',
    'Request' => 'Solicitar',
    'Contact Support' => 'Contactar con Soporte',
    'Membership Required' => 'Se Requiere Membresía',

    // Admin validation
    'license_pending_validation_requires_approval' => 'La licencia está pendiente de validación y requiere la aprobación del administrador.',
    'validate_and_approve' => 'Validar y Aprobar',
    'reject_validation' => 'Rechazar Validación',

    // Entity pending licenses
    'entity_has_pending_licenses' => 'Su entidad tiene licencias pendientes a la espera de pago',
    'invitations_available_after_payment' => 'Las invitaciones a atletas y entrenadores estarán disponibles una vez que se complete el pago de la licencia',
    'complete_payment_to_enable_invitations' => 'Complete el pago para activar sus licencias y habilitar las funciones de invitación',
    'pending_licenses_for_sports' => 'Licencias pendientes para: :sports',
    'license_approved_successfully' => 'Licencia aprobada correctamente.',
    'error_approving_license' => 'Error al aprobar la licencia: ',
    'license_not_in_approvable_state' => 'La licencia no está en un estado que permita la aprobación',
    'license_validation_rejected' => 'Validación de licencia rechazada',
    'license_canceled' => 'Licencia cancelada',
    'cannot_activate_unpaid_license' => 'No se puede activar la licencia: el pago no se ha completado. Por favor, asegúrese de que el documento de pago asociado esté pagado antes de activarla.',

    // License state translations
    'statuses' => [
        'ActiveLicenseAttributedState' => 'Activo',
        'PendingLicenseAttributedState' => 'Pendiente',
        'PendingTechnicalDirectorApprovalLicenseAttributedState' => 'Pendiente de Aprobación del TD',
        'PendingValidationLicenseAttributedState' => 'Pendiente de Validación del Administrador',
        'CanceledLicenseAttributedState' => 'Cancelado',
        'SuspendedLicenseAttributedState' => 'Suspendido',
        'ExpiredLicenseAttributedState' => 'Caducado',
        'ProvisionalLicenseAttributedState' => 'Provisional',
    ],

    // State translations for the states themselves
    'states' => [
        'pending' => 'Pendiente',
        'active' => 'Activo',
        'expired' => 'Caducado',
        'suspended' => 'Suspendido',
        'canceled' => 'Cancelado',
        'provisional' => 'Provisional',
        'waiting_approval' => 'A la Espera de Aprobación',
        'pending_validation' => 'Pendiente de Validación',
        'pending_technical_director_approval' => 'Pendiente de Aprobación del Director Técnico',
        'no_license' => 'Sin Licencia',
    ],

    // International License Specific
    'Active Affiliation Required' => 'Se Requiere Afiliación Activa',
    'You must have an active affiliation (membership package) to purchase international licenses. Please ensure your individual membership is active and paid before proceeding.' => 'Debe tener una afiliación activa (paquete de membresía) para adquirir licencias internacionales. Por favor, asegúrese de que su membresía individual esté activa y pagada antes de continuar.',
    'Search international licenses' => 'Buscar licencias internacionales',
    'Search international licenses...' => 'Buscar licencias internacionales...',
    'international licenses found' => 'licencias internacionales encontradas',
    'International License' => 'Licencia Internacional',
    'No international licenses available' => 'No hay licencias internacionales disponibles',
    'No international licenses are currently available for your federation.' => 'Actualmente no hay licencias internacionales disponibles para su federación.',
    'No international licenses match your search criteria.' => 'Ninguna licencia internacional coincide con sus criterios de búsqueda.',
    'Purchase International License' => 'Adquirir Licencia Internacional',
    'International License Purchase Success' => 'Compra de Licencia Internacional Exitosa',
    'Purchase Initiated Successfully' => 'Compra Iniciada Correctamente',
    'Your international license purchase has been initiated. Please complete the payment to activate your license.' => 'Se ha iniciado la compra de su licencia internacional. Por favor, complete el pago para activar su licencia.',
    'International License Details' => 'Detalles de la Licencia Internacional',
    'View National Licenses' => 'Ver Licencias Nacionales',
    'Select and purchase an international license for yourself' => 'Seleccione y adquiera una licencia internacional para usted',
    'No International License Access' => 'Sin Acceso a Licencias Internacionales',
    'Back to International Licenses' => 'Volver a las Licencias Internacionales',
    'View My International Licenses' => 'Ver Mis Licencias Internacionales',
    'Purchase International Licenses for Members' => 'Adquirir Licencias Internacionales para Miembros',
    'Select members and purchase international licenses on their behalf' => 'Seleccione miembros y adquiera licencias internacionales en su nombre',
    'Purchase International Entity License' => 'Adquirir Licencia Internacional de Entidad',
    'Purchase an international license for your organization' => 'Adquiera una licencia internacional para su organización',
    'Switch to International Entity License Purchase' => 'Cambiar a Compra de Licencia Internacional de Entidad',
    'Switch to International Member License Purchase' => 'Cambiar a Compra de Licencia Internacional de Miembro',
    'Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.' => 'Su entidad no está asociada a ninguna federación. Por favor, contacte con el administrador de su federación para establecer esta asociación antes de adquirir licencias internacionales.',

    // Table headers
    'licenses_title' => 'Licencias',
    'name' => 'Nombre',
    'license_name' => 'Nombre de la Licencia',
    'year' => 'Año',
    'actions' => 'Acciones',
    'sport_commission' => 'Comisión Deportiva',
    'sport_categories' => 'Categorías Deportivas',
    'not_active' => 'No Activo',
    'assign_individual_license' => 'Asignar Licencia Individual',
    'assign_entity_license' => 'Asignar Licencia de Entidad',

    // Separated license page titles
    'Sport Club Licenses' => 'Licencias de Club Deportivo',
    'Sport Licenses' => 'Licencias Deportivas',
    'International Entity Licenses' => "Licencias de Entidad {$internationalName}",
    'International Professional Licenses' => "Licencias Profesionales {$internationalName}",
    'Scientific Entity Licenses' => 'Licencias de Entidad Científica',
    'Scientific Professional Licenses' => 'Licencias Profesionales Científicas',
    'Primary Diving Services Licenses' => "Licencias de Servicios de Buceo {$primaryShortName}",

    // Middleware error messages
    'entity_has_inactive_license' => 'Su entidad tiene una licencia :committee, pero actualmente no está activa. Por favor, asegúrese de que su licencia :committee esté activa para acceder a esta función.',
    'entity_needs_active_license' => 'Su entidad necesita una licencia :committee activa para acceder a esta función. Por favor, contacte con su federación para obtener la licencia necesaria.',

    // License states
    'state_active' => 'Activo',
    'state_pending' => 'Pendiente',
    'state_canceled' => 'Cancelado',
    'state_provisional' => 'Provisional',
    'state_suspended' => 'Suspendido',
    'state_waiting_approval' => 'A la Espera de Aprobación',
    'state_expired' => 'Caducado',
    'state_pending_validation' => 'Pendiente de Validación',
    'state_pending_technical_director_approval' => 'Pendiente de Aprobación del Director Técnico',

    // Payment status
    'payment_status' => 'Estado del Pago',
    'payment_status_paid' => 'Pagado',
    'payment_status_pending_payment' => 'Pago Pendiente',
    'payment_status_no_document' => 'Sin Documento',

    // Filter labels
    'filters' => [
        'first_name' => 'Nombre',
        'surname' => 'Apellido',
        'member_number' => 'Número de Miembro',
        'sport' => 'Deporte',
        'entity_name' => 'Entidad',
    ],

    // Separated license purchase page titles and subtitles
    'Purchase Sport Club License' => 'Adquirir Licencia de Club Deportivo',
    'Purchase a sport license for your club' => 'Adquiera una licencia deportiva para su club',
    'Purchase Sport Licenses' => 'Adquirir Licencias Deportivas',
    'Select members and purchase sport licenses on their behalf' => 'Seleccione miembros y adquiera licencias deportivas en su nombre',
    'Purchase International Entity License' => "Adquirir Licencia de Entidad {$internationalName}",
    'Purchase an international license for your entity' => "Adquiera una licencia {$internationalShortName} para su entidad",
    'Purchase International Professional Licenses' => "Adquirir Licencias Profesionales {$internationalName}",
    'Select members and purchase international licenses on their behalf' => "Seleccione miembros y adquiera licencias {$internationalShortName} en su nombre",
    'Purchase Scientific Entity License' => 'Adquirir Licencia de Entidad Científica',
    'Purchase a scientific license for your entity' => 'Adquiera una licencia científica para su entidad',
    'Purchase Scientific Professional Licenses' => 'Adquirir Licencias Profesionales Científicas',
    'Select members and purchase scientific licenses on their behalf' => 'Seleccione miembros y adquiera licencias científicas en su nombre',
    'Purchase Primary Diving Services Licenses' => "Adquirir Licencias de Servicios de Buceo {$primaryShortName}",
    'Select members and purchase primary diving licenses on their behalf' => "Seleccione miembros y adquiera licencias de buceo {$primaryShortName} en su nombre",

    // Generic, committee-label-driven fallbacks (used when a committee declares
    // no purchase title/subtitle of its own in config/committees.php).
    'Purchase :committee Entity License' => 'Adquirir Licencia de Entidad :committee',
    'Purchase a :committee license for your entity' => 'Adquiera una licencia :committee para su entidad',
    'Purchase :committee Licenses' => 'Adquirir Licencias :committee',
    'Select members and purchase :committee licenses on their behalf' => 'Seleccione miembros y adquiera licencias :committee en su nombre',
    ':committee Entity Licenses' => 'Licencias de Entidad :committee',
    ':committee Professional Licenses' => 'Licencias Profesionales :committee',

    // Individual separated license purchase page titles
    'individual_sport_license_title' => 'Licencias Profesionales Deportivas',
    'individual_sport_license_subtitle' => 'Adquiera licencias para árbitros y entrenadores',
    'individual_national_diving_license_title' => "Licencia Profesional de Buceo {$primaryShortName}",
    'individual_national_diving_license_subtitle' => "Adquiera la licencia profesional de buceo {$primaryShortName}",
    'individual_cmas_diving_license_title' => "Licencia Profesional de Buceo Recreativo {$internationalShortName}",
    'individual_cmas_diving_license_subtitle' => "Adquiera la licencia profesional de buceo recreativo {$internationalShortName}",
    'individual_scientific_license_title' => "Licencia Profesional de Buceo Científico {$internationalShortName}",
    'individual_scientific_license_subtitle' => "Adquiera la licencia profesional de buceo científico {$internationalShortName}",

    // Individual separated licenses attributed page titles
    'individual_sport_licenses_title' => 'Licencias Deportivas',
    'individual_sport_licenses_subtitle' => 'Sus licencias deportivas para atletas, entrenadores y oficiales técnicos',
    'individual_national_diving_licenses_title' => 'Licencias Profesionales de Buceo',
    'individual_national_diving_licenses_subtitle' => 'Sus licencias profesionales de buceo',
    'individual_national_diving_licenses_info' => 'Aquí puede ver y adquirir nuevas licencias profesionales de buceo',
    'individual_cmas_diving_licenses_title' => "Licencias {$internationalName}",
    'individual_cmas_diving_licenses_subtitle' => '',
    'individual_cmas_diving_licenses_info' => "Aquí puede ver todas sus licencias profesionales anuales de buceo {$internationalName}",
    'individual_scientific_licenses_title' => "Licencias {$internationalName}",
    'individual_scientific_licenses_subtitle' => '',
    'individual_scientific_licenses_info' => "Aquí puede ver todas sus licencias profesionales anuales de buceo {$internationalName}",

    // Other individual license translations
    'individual_licenses_info' => 'Aquí puede ver todas sus licencias para atletas, entrenadores y oficiales técnicos',
    'sport' => 'Deporte',
    'category' => 'Categoría',

    // Federation separated licenses attributed page titles
    'federation_sport_entity_licenses_title' => 'Licencias de Club Deportivo',
    'federation_sport_entity_licenses_subtitle' => 'Licencias deportivas atribuidas a clubes',
    'federation_sport_individual_licenses_title' => 'Licencias Individuales Deportivas',
    'federation_sport_individual_licenses_subtitle' => 'Licencias deportivas atribuidas a atletas y entrenadores',
    'federation_national_diving_entity_licenses_title' => "Licencias de Centro de Buceo {$primaryShortName}",
    'federation_national_diving_entity_licenses_subtitle' => "Licencias de buceo {$primaryShortName} atribuidas a centros de buceo",
    'federation_national_diving_individual_licenses_title' => "Licencias Profesionales de Buceo {$primaryShortName}",
    'federation_national_diving_individual_licenses_subtitle' => "Licencias de buceo {$primaryShortName} atribuidas a profesionales",
    'federation_cmas_diving_entity_licenses_title' => 'Licencias de Centro de Buceo Internacional',
    'federation_cmas_diving_entity_licenses_subtitle' => 'Licencias de buceo internacional atribuidas a centros de buceo',
    'federation_cmas_diving_individual_licenses_title' => 'Licencias Profesionales de Buceo Internacional',
    'federation_cmas_diving_individual_licenses_subtitle' => 'Licencias de buceo internacional atribuidas a profesionales',
    'federation_scientific_entity_licenses_title' => 'Licencias de Centro de Buceo Científico',
    'federation_scientific_entity_licenses_subtitle' => 'Licencias de buceo científico atribuidas a centros de buceo',
    'federation_scientific_individual_licenses_title' => 'Licencias Profesionales de Buceo Científico',
    'federation_scientific_individual_licenses_subtitle' => 'Licencias de buceo científico atribuidas a profesionales',

    // Admin separated licenses attributed page titles
    'admin_sport_entity_licenses_title' => 'Licencias de Club Deportivo',
    'admin_sport_entity_licenses_subtitle' => 'Todas las licencias deportivas atribuidas a clubes',
    'admin_sport_individual_licenses_title' => 'Licencias Individuales Deportivas',
    'admin_sport_individual_licenses_subtitle' => 'Todas las licencias deportivas atribuidas a atletas y entrenadores',
    'admin_national_diving_entity_licenses_title' => "Licencias de Centro de Buceo {$primaryShortName}",
    'admin_national_diving_entity_licenses_subtitle' => "Todas las licencias de buceo {$primaryShortName} atribuidas a centros de buceo",
    'admin_national_diving_individual_licenses_title' => "Licencias Profesionales de Buceo {$primaryShortName}",
    'admin_national_diving_individual_licenses_subtitle' => "Todas las licencias de buceo {$primaryShortName} atribuidas a profesionales",
    'admin_cmas_diving_entity_licenses_title' => 'Licencias de Entidad Internacional',
    'admin_cmas_diving_entity_licenses_subtitle' => 'Todas las licencias internacionales atribuidas a entidades',
    'admin_cmas_diving_individual_licenses_title' => 'Licencias Profesionales de Buceo Recreativo',
    'admin_cmas_diving_individual_licenses_subtitle' => 'Todas las licencias atribuidas a profesionales de buceo recreativo',
    'admin_scientific_entity_licenses_title' => 'Licencias de Entidad Científica',
    'admin_scientific_entity_licenses_subtitle' => 'Todas las licencias de buceo científico atribuidas a entidades',
    'admin_scientific_individual_licenses_title' => 'Licencias Profesionales de Buceo Científico',
    'admin_scientific_individual_licenses_subtitle' => 'Todas las licencias de buceo científico atribuidas a profesionales',

    // Committee names (for translation)
    'Technical Committee' => 'Comité Técnico',
    'Scientific Committee' => 'Comité Científico',

    // International license field
    'is_international_label' => "Licencia {$internationalName}",
    'is_international_help' => "Si marca esta opción, esta licencia solo estará disponible para instructores/guías y entidades de {$internationalName}.",

    // International licenses page
    'international_licenses' => 'Licencias Internacionales',
    'cmas_international_licenses' => 'Licencias Internacionales',
    'international_licenses_description' => 'Sus licencias internacionales reconocidas en todo el mundo',
    'view_national_licenses' => 'Ver Licencias Nacionales',
    'purchase_international_license' => 'Adquirir Licencia Internacional',
    'license' => 'Licencia',
    'federation' => 'Federación',
    'sport_category' => 'Deporte/Categoría',
    'validity' => 'Validez',
    'international_code' => 'Código Internacional',
    'active' => 'Activo',
    'pending' => 'Pendiente',
    'cancelled' => 'Cancelado',
    'unknown' => 'Desconocido',
    'view' => 'Ver',
    'documents' => 'Documentos',
    'no_international_licenses' => 'Sin licencias internacionales',
    'no_international_licenses_message' => 'Aún no ha adquirido ninguna licencia internacional.',

    // License purchase success page
    'License Purchase Initiated!' => '¡Compra de Licencia Iniciada!',
    'Your license purchase is being processed. You will receive a confirmation once payment is complete.' => 'La compra de su licencia se está procesando. Recibirá una confirmación una vez que se complete el pago.',
    'You can view and manage your license in the My Licenses section' => 'Puede ver y gestionar su licencia en la sección Mis Licencias',
    'Payment Required' => 'Pago Requerido',
    'Your license is pending payment to be activated' => 'Su licencia está pendiente de pago para ser activada',
    'Please complete the payment to activate your license and download the certificate' => 'Por favor, complete el pago para activar su licencia y descargar el certificado',
    'An invoice has been generated and is available for download' => 'Se ha generado una factura y está disponible para descargar',
    'Pending Payment' => 'Pago Pendiente',
    'Complete Payment' => 'Completar Pago',
    'Payment integration coming soon' => 'Integración de pagos próximamente',

    // DIVINGSERVICES certification requirement
    'active_diving_certification_required' => 'Se Requiere Certificación de Buceo Activa',
    'active_diving_certification_required_description' => 'Debe tener una certificación profesional de buceo activa para solicitar una licencia profesional de buceo.',

    // License detail page actions
    'pending_payment_message' => 'La licencia está pendiente de confirmación de pago. Se activará automáticamente una vez que se procese el pago.',
    'waiting_approval_message' => 'La licencia está a la espera de aprobación.',
    'provisional_message' => 'La licencia es provisional y puede activarse.',
    'manually_activate' => 'Activar Licencia Manualmente',
    'cancel_license' => 'Cancelar Licencia',
    'suspend_license' => 'Suspender Licencia',
    'reactivate_license' => 'Reactivar Licencia',
    'approve_license' => 'Aprobar Licencia',
    'reject_license' => 'Rechazar Licencia',
    'activate_provisional' => 'Activar Licencia Provisional',
    'confirm_manual_activate' => '¿Está seguro de que desea activar manualmente esta licencia?',
    'confirm_cancel' => '¿Está seguro de que desea cancelar esta licencia?',
    'confirm_suspend' => '¿Está seguro de que desea suspender esta licencia?',
    'confirm_reactivate' => '¿Está seguro de que desea reactivar esta licencia?',
    'confirm_approve' => '¿Está seguro de que desea aprobar esta licencia?',
    'confirm_reject' => '¿Está seguro de que desea rechazar esta licencia?',
    'confirm_activate_provisional' => '¿Está seguro de que desea activar esta licencia provisional?',
    'confirm_validate_approve' => '¿Está seguro de que desea validar y aprobar esta licencia?',
    'confirm_reject_validation' => '¿Está seguro de que desea rechazar esta validación de licencia?',
];
