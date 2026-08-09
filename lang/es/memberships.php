<?php

return [
    // Subscription Creation
    'subscription_created_successfully' => 'Suscripción creada correctamente. Procede con el pago.',
    'subscription_created_pending_payment' => 'Suscripción creada correctamente. Procede con el pago.',
    'insurance_subscription_created_pending_payment' => '¡Suscripción de seguro creada correctamente! Completa el pago para activar tu cobertura de seguro.',
    'subscription_created_free' => 'Suscripción creada correctamente.',
    'subscription_creation_error' => 'Se ha producido un error al procesar tu suscripción. Inténtalo de nuevo.',
    'subscription_already_pending' => 'Ya tienes una suscripción pendiente para este paquete.',
    'subscription_already_pending_payment' => 'Ya tienes una suscripción pendiente para este paquete. Completa el pago para activarla.',

    // Document Generation
    'affiliation_description' => 'Afiliación: :name - :federation',
    'insurance_description' => 'Seguro: :name',
    'subscription_document_notes' => 'Suscripción al paquete: :package',
    'bulk_subscription_document_note' => 'Suscripción masiva para :count miembros - Paquete: :package',

    // Document Observer
    'activating_subscription_after_payment' => 'Activando la suscripción del miembro tras el pago',
    'subscription_activated' => 'Suscripción del miembro activada',

    // Payment Flow
    'payment_required' => 'Se requiere el pago para completar la suscripción',
    'proceed_to_payment' => 'Procede al pago para activar tu suscripción',

    // Validation Messages
    'package_selection_required' => 'Es obligatorio seleccionar un paquete de afiliación.',
    'package_selection_invalid' => 'El paquete de afiliación seleccionado no es válido.',
    'invalid_member_type' => 'Tipo de miembro no válido para la suscripción.',
    'no_validation_affiliation_for_insurance' => 'Se requiere una afiliación de validación activa para suscribirse a paquetes solo de seguro.',
    'no_active_affiliation_found' => 'No se ha encontrado ninguna afiliación activa. Se requiere una afiliación de validación.',
    'duplicate_affiliation_plans' => 'Ya tienes una suscripción activa a los siguientes planes de afiliación: :plans',
    'all_affiliation_plans_already_active' => 'Ya tienes una suscripción activa a todos los planes de afiliación de este paquete: :plans',
    'duplicate_insurance_plans' => 'Ya tienes una suscripción activa o pendiente a los siguientes planes de seguro: :plans',
    'insufficient_privileges_for_request_type' => 'Privilegios insuficientes para este tipo de solicitud.',
    'validation_plan_required_for_non_validation_packages' => 'El particular debe tener un plan de validación activo para suscribirse a este paquete de afiliación.',

    // Renewal
    'subscription_renewed_successfully' => 'Suscripción de afiliación renovada correctamente.',

    // Individual Profile Messages
    'complete_profile_before_managing_subscriptions' => 'Completa tu perfil de particular antes de gestionar suscripciones.',

    // Affiliation Plan Business Scenarios
    'business_scenarios' => [
        'direct_individual' => [
            'label' => 'Suscripción directa de particular',
            'description' => 'Los particulares se suscriben directamente a este plan por sí mismos',
            'example' => 'Ejemplo: afiliación anual personal, tarifas para estudiantes',
        ],
        'entity_for_individuals' => [
            'label' => 'La entidad se suscribe por particulares',
            'description' => 'Las entidades (clubes, escuelas) se suscriben a este plan PARA sus miembros particulares',
            'example' => 'Ejemplo: el club paga las afiliaciones de atletas, el centro de buceo paga las certificaciones de estudiantes',
        ],
        'direct_entity' => [
            'label' => 'Suscripción directa de entidad',
            'description' => 'Las entidades se suscriben a este plan por sí mismas (afiliación institucional)',
            'example' => 'Ejemplo: afiliación institucional de club, certificación de centro de buceo',
        ],
        'flexible' => [
            'label' => 'Plan flexible',
            'description' => 'Puede ser utilizado tanto por particulares como por entidades con precios diferentes',
            'example' => 'Ejemplo: plan premium con tarifas individuales e institucionales',
        ],
    ],

    // Form Labels
    'choose_business_scenario' => 'Elegir escenario de negocio',
    'business_scenario_help' => 'Selecciona qué tipo de plan de suscripción quieres crear. Esto determina quién puede suscribirse y cómo funcionan los precios.',
    'plan_name' => 'Nombre del plan',
    'plan_name_help' => 'Elige un nombre claro y descriptivo',
    'select_federation' => 'Seleccionar federación...',
    'pricing' => 'Precios',
    'fee_individual_member' => 'Tarifa cobrada por miembro particular',
    'fee_individual_subscription' => 'Tarifa cuando lo suscriben particulares',
    'fee_entity_institution' => 'Tarifa cobrada a la entidad (institución)',
    'fee_entity_subscription' => 'Tarifa cuando lo suscriben entidades',
    'free_plan_option' => 'Este es un plan gratuito (establece las tarifas en 0 €)',
    'immediate_availability' => 'Déjalo vacío para disponibilidad inmediata',
    'no_expiration' => 'Déjalo vacío para que no caduque',
    'description_help' => 'Proporciona información detallada sobre lo que incluye este plan, requisitos, beneficios, etc.',
    'pdf_documents' => 'Documentos PDF',
    'upload_documents_help' => 'Sube términos, condiciones u otros documentos relevantes. Máximo 10 MB cada uno.',
    'current_attachments' => 'Archivos adjuntos actuales',
    'uncheck_remove_files' => 'Desmarca para eliminar archivos',
    'plan_summary' => 'Resumen del plan',
    'usage' => 'Uso',
    'create_plan_help' => 'Crea un nuevo plan de afiliación eligiendo el escenario de negocio que mejor describa cómo debe funcionar este plan. El formulario te guiará por los ajustes adecuados.',
    'edit_plan_help' => 'Edita los detalles de este plan de afiliación. El escenario de negocio determina la estructura del plan y las opciones de precios.',
    'complete_profile_before_selecting_subscription' => 'Completa tu perfil de particular antes de seleccionar una suscripción.',
    'complete_profile_before_purchasing_subscription' => 'Completa tu perfil de particular antes de comprar una suscripción.',
    'complete_profile_before_viewing_history' => 'Completa tu perfil de particular antes de ver el historial de suscripciones.',
    'please_login_to_continue' => 'Inicia sesión para continuar.',
    'profile_issue_contact_support' => 'Ha habido un problema con tu perfil. Contacta con soporte.',
    'subscription_not_eligible_for_renewal' => 'Esta suscripción no es elegible para renovación.',
    'renewal_error_try_again' => 'Se ha producido un error al renovar tu suscripción. Inténtalo de nuevo.',
    'duplicate_affiliation_plans_error' => 'Ya tienes una suscripción activa a uno o más planes de afiliación de este paquete.',

    // Official Document Requirements
    'missing_official_documents' => 'No puedes suscribirte a este paquete porque requiere documentos oficiales que no has subido o que no están activos.',
    'insurance_requires_document' => 'Obligatorio: :document para :insurance.',

    // Validation Plan
    'validation_plan' => 'Plan de validación',
    'validation_plan_help' => 'Habilita privilegios avanzados para los suscriptores de este plan',
    'validation_plan_enables' => 'Los planes de validación habilitan',
    'insurance_requests' => 'Solicitar pólizas de seguro',
    'license_requests' => 'Solicitar licencias y certificaciones',
    'entity_member_licenses' => 'Para entidades: solicitar licencias para sus miembros',

    // Validation Plan Error Messages
    'insurance_subscription_not_authorized' => 'Suscripción de seguro no autorizada: :reason',
    'license_request_not_authorized' => 'Solicitud de licencia no autorizada: :reason',
    'entity_member_insurance_not_authorized' => 'Asignación de seguro a miembro de entidad no autorizada: :reason',
    'entity_member_license_not_authorized' => 'Solicitud de licencia para miembro de entidad no autorizada: :reason',

    // Validation Plan Privilege Messages
    'validation_plan_no_insurance_privileges' => 'Tu plan de afiliación actual no incluye privilegios para solicitar seguros',
    'validation_plan_no_license_privileges' => 'Tu plan de afiliación actual no incluye privilegios para solicitar licencias',
    'validation_plan_no_entity_member_licenses' => 'Tu plan de afiliación actual no permite solicitar licencias para miembros de la entidad',
    'validation_plan_no_entity_member_subscriptions' => 'Tu plan de afiliación actual no permite suscribir miembros a paquetes',

    // Validation Plan UI Messages
    'validation_plan_required' => 'Se requiere un plan de validación',
    'access_restricted' => 'Acceso restringido',
    'contact_federation_validation_plan' => 'Contacta con tu federación para mejorar tu plan de validación y habilitar las funciones de suscripción de miembros.',
    'validation_plan_required_message' => 'Se requiere un plan de validación para suscribir miembros a paquetes.',
    'no_active_affiliation_found' => 'No se ha encontrado ninguna afiliación activa',
    'entity_member_subscriptions_not_authorized' => 'No puedes suscribir miembros a paquetes. :reason',
    'invalid_member_type' => 'Tipo de miembro no válido',
    'insufficient_privileges_for_request_type' => 'Privilegios insuficientes para este tipo de solicitud',

    // Subscription page
    'affiliations' => 'Afiliaciones',
    'active_affiliations' => 'Afiliaciones activas',
    'included_plans' => 'Planes incluidos',
    'affiliation_plans' => 'Planes de afiliación',

    // Member subscriptions
    'member_subscriptions' => [
        'created_successfully' => 'Suscripción del miembro creada correctamente.',
        'renewed_successfully' => 'Suscripción del miembro renovada correctamente.',
        'delete' => 'Eliminar',
        'deleted_successfully' => 'Suscripción del miembro eliminada correctamente.',
        'delete_failed' => 'No se ha podido eliminar la suscripción del miembro. Inténtalo de nuevo.',
        'confirm_delete_title' => 'Eliminar suscripción del miembro',
        'confirm_delete_warning' => 'Esta acción eliminará permanentemente la suscripción del miembro y todas las afiliaciones y seguros relacionados. Esta acción no se puede deshacer.',
        'will_delete_related' => 'Esto eliminará :affiliations afiliación(es) y :insurances seguro(s)',
        'delete_confirm' => 'Eliminar suscripción',
        'change_status' => 'Cambiar estado',
        'change_status_title' => 'Cambiar el estado de la suscripción',
        'change_status_warning' => 'Esto solo cambiará el estado de la suscripción. Los documentos de pago, las afiliaciones y los seguros NO se verán afectados.',
        'new_status' => 'Nuevo estado',
        'update_status' => 'Actualizar estado',
        'status_updated_successfully' => 'Estado de la suscripción del miembro actualizado correctamente.',
        'status_update_failed' => 'No se ha podido actualizar el estado de la suscripción del miembro.',
        'pending_payment' => 'Pago pendiente',
    ],

    // Notifications
    'subscription_activated_notification' => 'Tu suscripción a :package se ha activado y es válida hasta el :date.',

    // Membership states
    'states' => [
        'active' => 'Activo',
        'pending' => 'Pendiente',
        'expired' => 'Caducado',
        'canceled' => 'Cancelado',
    ],

    // Member subscription states
    'subscription_states' => [
        'active' => 'Activo',
        'pending' => 'Pendiente',
        'pending_payment' => 'Pago pendiente',
        'expired' => 'Caducado',
    ],

    // Table headers
    'title' => 'Afiliaciones',
    'name' => 'Nombre',
    'plans' => 'Planes',
    'status' => 'Estado',
    'expiration_date' => 'Fecha de vencimiento',
    'organizations_membership_association' => 'Asociación de afiliación a organizaciones',
];
