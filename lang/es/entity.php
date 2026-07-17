<?php

$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    // Page titles
    'members_list' => 'Lista de miembros',
    'member_list' => 'Lista de miembros',
    'entities' => 'Entidades colectivas',
    'entity_detail' => 'Detalle de la entidad',
    'entities_to_approve' => 'Entidades a aprobar',
    'create_entity' => 'Crear entidad',
    'create_entity_account' => 'Crear una cuenta de entidad',
    'edit_entity_record' => 'Editar registro de entidad',

    // Actions
    'create_individual' => 'Crear individuo',
    'individuals_to_approve' => 'Individuos a aprobar',
    'invite_member' => 'Invitar miembro',
    'submit_request' => 'Enviar solicitud',
    'approve_entity' => 'Aprobar entidad',
    'accept_request' => '¿Aceptar esta solicitud?',
    'view_all' => 'Ver todo',
    'see_all_instructors' => 'Ver todos los instructores',
    'open_url' => 'Abrir url',

    // Table headers
    'gender' => 'Sexo',
    'id_number' => 'Número de documento',
    'national_affiliation' => "Afiliación {$primaryShortName}",
    'table_name' => 'Nombre',
    'table_country' => 'País',
    'table_national_fed_nr' => 'Nº Fed. Nacional',
    'table_cmas_zone' => 'Zona internacional',
    'table_sub_region' => 'Subregión',
    'table_actions' => 'Acciones',
    'table_nationality' => 'Nacionalidad',
    'table_email' => 'Correo electrónico',
    'table_requested' => 'Solicitado',
    'table_federation' => 'Organización',
    'table_type' => 'Tipo',
    'table_status' => 'Estado del miembro',
    'table_national_number' => 'Número nacional',
    'table_number' => 'Número',
    'table_date' => 'Fecha',
    'table_total' => 'Total',
    'table_zone_or_association' => 'Zona o asociación territorial',

    // Form labels
    'name' => 'Nombre de la entidad',
    'given_name' => 'Nombre',
    'family_name' => 'Apellidos',
    'nationality' => 'Nacionalidad',
    'federation' => 'Federación',
    'birthdate' => 'Fecha de nacimiento',
    'member_number' => 'Número de miembro',
    'affiliation_status' => 'Estado de la afiliación',
    'affiliation_active' => 'Activa',
    'affiliation_inactive' => 'Inactiva',
    'valid_member_code' => 'Código de miembro válido',

    // Form sections
    'information' => 'Información',
    'entity_logo' => 'Logotipo de la entidad',
    'club_school_center_name' => 'Nombre del club/escuela/centro',
    'legal_name' => 'Nombre de registro fiscal',
    'responsible_person_name' => 'Nombre de la persona responsable',
    'nif' => 'ID fiscal (NIF)',
    'national_fed_nr' => 'Nº Fed. Nacional',
    'affiliate_nr' => 'Nº de afiliado',
    'hq_location' => 'Ubicación de la sede',
    'district' => 'Distrito',
    'zones' => 'Zonas',
    'no_zones_assigned' => 'No hay zonas asignadas',
    'address' => 'Dirección',
    'location' => 'Localidad',
    'zip_code' => 'Código postal',
    'country' => 'País',
    'select_option' => '-- Seleccione una opción --',
    'public_contacts' => 'Contactos públicos',
    'contact_email' => 'Correo electrónico de contacto',
    'website' => 'Sitio web',
    'phone_number' => 'Número de teléfono',
    'social_media_links' => 'Enlaces de redes sociales',
    'facebook_url' => 'URL de Facebook',
    'x_url' => 'URL de X',
    'instagram_url' => 'URL de Instagram',
    'linkedin_url' => 'URL de LinkedIn',

    // Terms and policies
    'terms_policies' => 'Términos y políticas',
    'terms_confirm' => 'Confirmo que la entidad acepta los',
    'terms_of_service' => 'Términos del Servicio',
    'and' => 'y la',
    'privacy_policy' => 'Política de Privacidad',
    'data_sharing_confirm' => 'Confirmo que la entidad consiente la comunicación de datos a terceros autorizados tal como se describe en la',
    'data_sharing_policy' => 'Política de Comunicación de Datos',
    'save_record' => 'Guardar registro',

    // User login section
    'user_login_information' => 'Información de inicio de sesión del usuario',
    'user_login_info_description' => 'Tras elegir la dirección de correo electrónico del usuario, se enviará un correo para que la persona registre sus propias credenciales.',
    'user_login_email' => 'Correo electrónico de inicio de sesión del usuario',
    'confirm_user_login_email' => 'Confirmar correo electrónico de inicio de sesión del usuario',
    'confirm_email_address' => 'Confirme la dirección de correo electrónico',
    'email_credential_hint' => 'Credencial de correo electrónico para que el usuario inicie sesión',
    'entity_creation_info' => 'Cuando se crea un registro de entidad, también se asigna automáticamente un usuario a este registro. Se enviará un correo electrónico a la dirección elegida para que el usuario registre sus propias credenciales. Después, la persona podrá iniciar sesión en la plataforma.',

    // Modal content
    'member_invitation_form' => 'Formulario de invitación de miembros',
    'member_request' => 'Invitación de miembro',
    'member_request_description' => 'Puede usar este formulario para invitar a miembros mediante su ID personal o su número de miembro. Debe solicitar uno de estos al miembro antes de enviar esta invitación.',
    'or_separator' => 'O',

    // Zone assignment
    'zone_auto_assigned' => 'La zona se asignará automáticamente en función de su asociación.',
    'zone_will_be' => 'Zona',
    'zone_edit_restricted' => "Solo {$primaryShortName} o un administrador pueden editar este campo.",

    // Entity approval
    'approval_national_federation_message' => 'Está a punto de aprobar esta entidad. Se asignará automáticamente un número de miembro.',
    'approval_association_message' => 'Está a punto de aprobar esta entidad para su asociación.',
    'member_number_auto_generated' => 'El número de miembro se generará automáticamente tras la aprobación.',
    'member_number_primary_federation_only' => "Nota: Solo {$primaryShortName} puede asignar el número de la federación nacional. La entidad será aprobada para su asociación sin un número de miembro.",

    // Show page
    'tax_identification_number' => 'Número de Identificación Fiscal',
    'hq_address_city_postal' => 'Dirección de la sede, ciudad, código postal',
    'individuals' => 'Individuos',
    'diving_certifications' => 'Certificaciones de buceo',
    'scientific_certifications' => 'Certificaciones científicas',
    'diving_licenses' => 'Licencias de proveedor de servicios de buceo',
    'scientific_licenses' => 'Licencias científicas',
    'sport_licenses' => 'Licencias deportivas',
    'instructors' => 'Instructores',
    'active' => 'activos',
    'no_instructors_yet' => 'Todavía no hay instructores',
    'federations' => 'Federación(es)',
    'associations' => 'Asociaciones',
    'federation_and_associations' => 'Federación y asociaciones',
    'no_individuals_yet' => 'Todavía no hay individuos',
    'local_federation' => 'Asociación',
    'main_federation' => 'Federación principal',
    'no_federation_memberships' => 'No se encontraron afiliaciones a federaciones',
    'no_association_memberships' => 'No se encontraron afiliaciones a asociaciones',
    'table_association' => 'Asociación',
    'association_type_territorial' => 'Territorial',
    'association_type_nacional' => 'Nacional',
    'association_type_modalidade' => 'Modalidad',

    // Documents
    'documents_invoices' => 'Documentos y facturas',
    'view' => 'Ver',
    'no_documents_found' => 'No se encontraron documentos',
    'no_documents_description' => 'Todavía no se han generado documentos ni facturas para esta entidad.',
    'showing_last_documents' => 'Mostrando los últimos :count documentos',

    // Messages
    'invalid_cmas_code' => 'El código internacional no es válido. Por favor, confirme la información proporcionada.',
    'invalid_member_number' => 'El número de miembro no es válido. Por favor, confirme la información proporcionada.',
    'member_must_have_federation' => 'Este miembro debe tener una relación con una federación (activa o pendiente) y no debe formar parte ya de su entidad.',
    'invitation_sent_success' => 'La invitación al miembro se envió correctamente. Por favor, dé tiempo al miembro para revisar su solicitud.',
    'error_creating_record' => 'Error al crear este registro: :error',

    // Entity Profile Tabs
    'no_certifications_message' => 'Esta entidad aún no tiene certificaciones atribuidas.',
    'no_licenses_message' => 'Esta entidad aún no tiene licencias atribuidas.',

    // Federation membership
    'designation' => 'Designación',
    'member_approved' => 'Miembro aprobado',
    'member_pending_approval' => 'Aprobación pendiente',
    'federation_membership_info' => 'Esta tabla muestra su estado de afiliación en la Federación y las Asociaciones.',

    // Entity Dashboard
    'dashboard' => [
        'entity_profile' => 'Perfil de la entidad',
        'members_to_approve' => 'Miembros a aprobar',
        'no_pending_members' => 'No hay solicitudes de miembros pendientes',
        'entity_affiliations' => 'Afiliaciones de la entidad',
        'no_affiliations' => 'No se encontraron afiliaciones',
        'no_sport_licenses' => 'No hay licencias deportivas',
        'no_diving_licenses' => 'No hay licencias de buceo',
        'no_entity_found' => 'Entidad no encontrada',
        'no_entity_associated' => 'No hay ninguna entidad asociada a su cuenta.',
    ],

    // Error messages
    'committee_not_found' => 'El comité requerido para el tipo de entidad :type no está configurado. Por favor, contacte con el soporte.',

    // Map
    'get_directions' => 'Cómo llegar',

    // International Portal
    'cmas_portal_access' => 'Acceso al Portal Internacional',
    'has_cmas_portal_account' => 'Tiene cuenta en el Portal Internacional',
    'cmas_portal_description' => 'Marque esta casilla si la entidad tiene una cuenta en el Portal Internacional',

    // Public Page Management
    'public_page' => [
        'title' => 'Gestión de la página pública',
        'subtitle' => 'Gestione el perfil público y el contenido de su organización',
        'view_public_page' => 'Ver página pública',
        'tabs' => [
            'general' => 'Configuración general',
            'featured_locations' => 'Lugares destacados',
            'courses' => 'Cursos de buceo',
        ],
        'background_image' => 'Imagen de fondo del perfil',
        'current_background' => 'Fondo actual',
        'current_image' => 'Imagen actual',
        'confirm_remove_background' => '¿Está seguro de que desea eliminar la imagen de fondo?',
        'background_removed' => 'Imagen de fondo eliminada correctamente.',
        'upload_file' => 'Subir un archivo',
        'or_drag_drop' => 'o arrastrar y soltar',
        'image_requirements' => 'PNG, JPG, WEBP hasta 2 MB',
        'preview' => 'Vista previa',
        'public_description' => 'Descripción pública',
        'description_help' => 'Esta descripción se mostrará en la página de su perfil público.',
        'save_settings' => 'Guardar configuración',
        'settings_saved' => 'Configuración guardada correctamente.',
        'featured_locations' => [
            'title' => 'Lugares de buceo destacados',
            'description' => 'Seleccione los lugares de buceo que desea destacar en su perfil público.',
            'select_locations' => 'Seleccionar lugares',
            'no_locations_selected' => 'No se seleccionaron lugares de buceo.',
            'selected_preview' => 'Vista previa de los lugares seleccionados',
            'save_locations' => 'Guardar lugares destacados',
            'locations_saved' => 'Lugares destacados actualizados correctamente.',
            'create_new' => 'Crear nuevo lugar',
        ],
    ],
];
