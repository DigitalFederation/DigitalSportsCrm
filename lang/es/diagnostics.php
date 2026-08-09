<?php

return [
    // Page titles
    'title' => 'Centro de diagnóstico de elegibilidad',
    'subtitle' => 'Diagnostica por qué los particulares pueden no aparecer en las listas de inscripción',

    // Tab titles
    'tab_individual_profile' => 'Perfil del particular',
    'tab_event_enrollment' => 'Inscripción a eventos',
    'tab_license_availability' => 'Disponibilidad de licencias',

    // Individual Profile Tab
    'individual_profile_title' => 'Diagnóstico del perfil del particular',
    'individual_profile_description' => 'Busca un particular para ver su perfil completo de elegibilidad y entender por qué puede o no puede inscribirse para diferentes roles.',
    'search_placeholder' => 'Buscar por código internacional, nombre o correo electrónico...',
    'no_individual_selected' => 'Ningún particular seleccionado',
    'search_to_start' => 'Busca un particular para ver su perfil de elegibilidad.',
    'quick_status' => 'Estado rápido',

    // Role labels
    'role_athlete' => 'Atleta',
    'role_coach' => 'Entrenador',
    'role_referee' => 'Árbitro',
    'role_official' => 'Oficial',

    // Sections
    'federation_memberships' => 'Afiliaciones a federaciones',
    'entity_memberships' => 'Afiliaciones a entidades',
    'professional_roles' => 'Roles profesionales',
    'certifications' => 'Certificaciones (comprobación de árbitro)',
    'active_licenses' => 'Licencias activas',

    // Table headers
    'federation' => 'Federación',
    'entity' => 'Entidad',
    'type' => 'Tipo',
    'status' => 'Estado',
    'since' => 'Desde',
    'sports' => 'Deportes',
    'role' => 'Rol',
    'source' => 'Origen',
    'certification' => 'Certificación',
    'grants_role' => 'Otorga rol',
    'action_needed' => 'Acción necesaria',
    'license' => 'Licencia',
    'expires' => 'Caduca',

    // Federation types
    'local' => 'Local',
    'main' => 'Principal',
    'modalidade' => 'Deporte',

    // Empty states
    'no_federation_memberships' => 'No se han encontrado afiliaciones a federaciones.',
    'no_entity_memberships' => 'No se han encontrado afiliaciones a entidades.',
    'no_professional_roles' => 'No hay roles profesionales asignados.',
    'no_certifications' => 'No hay certificaciones atribuidas.',
    'no_active_licenses' => 'No hay licencias activas.',
    'unknown_federation' => 'Federación desconocida',
    'unknown_entity' => 'Entidad desconocida',
    'unknown_license' => 'Licencia desconocida',
    'unknown_certification' => 'Certificación desconocida',

    // Sources
    'source_direct_assignment' => 'Asignación directa',
    'source_entity_assignment' => 'Asignación por entidad',

    // Certification action
    'action_activate_certification' => 'ACTIVAR para habilitar el rol',

    // Quick status reasons
    'not_checked' => 'No comprobado',
    'reason_no_active_federation' => 'Sin afiliación activa a una federación',
    'reason_no_active_entity' => 'Sin afiliación activa a una entidad',
    'reason_not_registered_athlete' => 'No registrado como atleta',
    'reason_registered_athlete' => 'Registrado como atleta',
    'reason_no_coach_role' => 'Sin rol profesional de ENTRENADOR',
    'reason_has_coach_role' => 'Tiene el rol de ENTRENADOR asignado',
    'reason_cert_pending_activation' => 'La certificación existe pero está PENDIENTE de activación',
    'reason_no_referee_cert' => 'Sin certificación de árbitro atribuida',
    'reason_no_referee_role' => 'Sin rol profesional de ÁRBITRO (comprueba la certificación)',
    'reason_has_referee_role' => 'Tiene el rol de ÁRBITRO asignado',
    'reason_no_active_membership' => 'Sin afiliación activa',
    'reason_active_member' => 'Miembro activo',

    // Event Enrollment Tab
    'event_enrollment_title' => 'Diagnóstico de inscripción a eventos',
    'event_enrollment_description' => 'Selecciona un evento y un particular para diagnosticar por qué puede no aparecer en la lista de inscripción para un rol específico.',
    'select_event' => 'Seleccionar evento',
    'select_event_placeholder' => '-- Seleccionar un evento --',
    'select_competition' => 'Seleccionar competición (opcional)',
    'all_competitions' => '-- Todas las competiciones --',
    'select_role' => 'Rol a diagnosticar',
    'search_individual' => 'Buscar particular',
    'run_diagnostic' => 'Ejecutar diagnóstico',
    'selected' => 'Seleccionado',
    'select_event_first' => 'Primero selecciona un evento',
    'select_event_to_start' => 'Elige un evento del desplegable para comenzar el diagnóstico.',

    // Diagnostic results
    'eligible_as_role' => 'ELEGIBLE como :role',
    'not_eligible_as_role' => 'NO ELEGIBLE como :role',
    'passed' => 'SUPERADO',
    'failed' => 'FALLIDO',
    'suggestions' => 'Acciones sugeridas',

    // Check labels
    'check_federation_membership' => 'Afiliación a federación',
    'check_entity_membership' => 'Afiliación a entidad',
    'check_athlete_registration' => 'Registro de atleta',
    'check_coach_role' => 'Rol profesional de entrenador',
    'check_referee_role' => 'Rol profesional de árbitro',
    'check_referee_cert_exists' => 'Existe certificación de árbitro',
    'check_referee_cert_active' => 'La certificación está activa',
    'check_required_certs' => 'Certificaciones requeridas',
    'check_required_licenses' => 'Licencias requeridas',
    'check_active_membership' => 'Afiliación activa',
    'check_not_enrolled' => 'No inscrito todavía',

    // Check messages - Passed
    'check_federation_membership_passed' => 'Miembro activo de :federation',
    'check_federation_membership_athlete_passed' => 'Tiene afiliación activa a una federación',
    'check_federation_membership_coach_passed' => 'Tiene afiliación activa a una federación',
    'check_entity_membership_passed' => 'Miembro activo de: :entities',
    'check_entity_membership_passed_coach' => 'Tiene afiliación activa a una entidad',
    'check_athlete_registration_passed' => 'Registrado como atleta para :sport',
    'check_coach_role_passed' => 'Tiene el rol profesional de ENTRENADOR asignado',
    'check_referee_role_passed' => 'Tiene el rol profesional de ÁRBITRO asignado',
    'check_referee_cert_exists_passed' => 'Tiene certificación(es) de árbitro: :certs',
    'check_referee_cert_active_passed' => 'Tiene al menos una certificación de árbitro activa',
    'check_required_certs_passed' => 'Tiene todas las certificaciones requeridas',
    'check_required_licenses_passed' => 'Tiene todas las licencias requeridas',
    'check_active_membership_passed' => 'Tiene afiliación activa (puede inscribirse como oficial)',
    'check_not_enrolled_passed' => 'Todavía no está inscrito en este evento',

    // Check messages - Failed
    'check_federation_membership_failed' => 'No se ha encontrado afiliación activa a una federación',
    'check_entity_membership_failed' => 'No se ha encontrado afiliación activa a una entidad',
    'check_athlete_registration_failed' => 'No registrado como atleta en ninguna entidad',
    'check_athlete_wrong_sport' => 'Registrado para :registered pero el evento requiere :required',
    'check_coach_role_failed' => 'No tiene el rol profesional de ENTRENADOR asignado',
    'check_referee_role_failed' => 'No tiene el rol profesional de ÁRBITRO asignado',
    'check_referee_role_cert_pending' => 'La certificación ":cert" existe pero está PENDIENTE - el rol de ÁRBITRO aún no está asignado',
    'check_referee_cert_exists_failed' => 'No hay ninguna certificación de tipo árbitro atribuida',
    'check_referee_cert_no_certs' => 'No hay certificaciones de árbitro que comprobar',
    'check_referee_cert_pending' => 'Existe(n) certificación(es) de árbitro pero está(n) PENDIENTE(S): :certs',
    'check_referee_cert_inactive' => 'No se han encontrado certificaciones de árbitro activas',
    'check_required_certs_failed' => 'Falta(n) certificación(es) requerida(s): :certs',
    'check_required_licenses_failed' => 'Falta(n) licencia(s) requerida(s): :licenses',
    'check_active_membership_failed' => 'Sin afiliación activa en ninguna federación o entidad',
    'check_already_enrolled' => 'Ya inscrito en este evento para este rol',

    // Suggestions
    'suggestion_activate_membership' => 'Activar la afiliación a la federación/entidad',
    'suggestion_join_entity' => 'Unirse a una entidad como miembro',
    'suggestion_register_as_athlete' => 'Registrarse como atleta en Entidad > Atletas',
    'suggestion_register_for_sport' => 'Registrarse como atleta para el deporte correcto',
    'suggestion_assign_coach_role' => 'Asignar el rol de ENTRENADOR en Entidad > Entrenadores',
    'suggestion_attribute_referee_cert' => 'Atribuir una certificación de árbitro en Federación > Certificaciones',
    'suggestion_activate_certification' => 'ACTIVAR la certificación pendiente para otorgar el rol de ÁRBITRO',
    'suggestion_check_cert_status' => 'Comprobar el estado de la certificación: puede estar caducada o cancelada',
    'suggestion_obtain_required_cert' => 'Obtener y activar la(s) certificación(es) requerida(s)',
    'suggestion_obtain_required_license' => 'Obtener y activar la(s) licencia(s) requerida(s)',

    // Membership details
    'member_of_federations' => 'Federación(es): :federations',
    'member_of_entities' => 'Entidad(es): :entities',

    // License Availability Tab
    'license_availability_title' => 'Diagnóstico de disponibilidad de licencias',
    'license_availability_description' => 'Diagnostica por qué ciertas licencias pueden no aparecer en la lista de compra.',
    'coming_soon' => 'Próximamente...',
];
