<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');

return [
    'entity_created' => [
        'subject' => 'Te damos la bienvenida a :app',
        'greeting' => '¡Saludos, :name!',
        'line1' => 'Se ha creado una cuenta para tu entidad.',
        'line2' => 'Para gestionar el perfil de tu entidad y explorar las funcionalidades de nuestra plataforma, establece tu contraseña.',
        'action' => 'Establecer tu contraseña',
        'line3' => 'Una vez establecida tu contraseña, tendrás acceso completo a tu panel.',
        'line4' => 'Esperamos tu participación activa.',
        'line5' => 'Gracias por formar parte de :app. Si tienes alguna pregunta, no dudes en contactarnos.',
        'salutation' => 'Un cordial saludo, el equipo de :app',
    ],

    'welcome_email' => [
        'title' => 'Correo de bienvenida',
        'user_email' => 'Correo electrónico del usuario',
        'sent_status' => 'Enviado',
        'not_sent_status' => 'No enviado',
        'send_button' => 'Enviar correo de bienvenida',
        'resend_button' => 'Reenviar correo de bienvenida',
        'confirm_send' => '¿Seguro que quieres enviar el correo de bienvenida?',
        'description' => 'Este correo contiene un enlace para que el usuario establezca su contraseña y active su cuenta.',
        'sent' => 'Correo de bienvenida enviado correctamente.',
        'failed' => 'No se ha podido enviar el correo de bienvenida.',
        'no_user' => 'No hay ninguna cuenta de usuario asociada.',
    ],

    // Payment notifications
    'payment_made' => 'Se ha realizado un pago de :value.',

    // Event notifications
    'event_enrollment_confirmed' => 'Tu inscripción al evento ha sido confirmada.',
    'event_registration_confirmed' => 'Tu registro al evento ha sido confirmado.',

    // Request notifications
    'request_approved' => 'Tu solicitud para unirte a :federation ha sido aprobada.',
    'federation_request_approved' => "Tu solicitud para unirte a {$portalName} ha sido aprobada.",
    'association_request_accepted' => 'Solicitud de asociación aceptada correctamente.',
    'error_accepting_request' => 'Error al aceptar la solicitud.',
    'request_join_accepted' => 'La solicitud de :name para unirse ha sido aceptada.',
    'request_rejected' => 'Solicitud del particular rechazada correctamente.',
    'error_rejecting_request' => 'No se ha podido rechazar la solicitud del particular.',
    'request_deleted' => 'Solicitud del particular eliminada correctamente.',

    // Document notifications
    'document_created' => [
        'subject' => 'Notificación de creación de documento',
        'greeting' => 'Notificación',
        'line' => "El documento :invoice está disponible en {$portalName}. Haz clic en el botón de abajo para acceder a {$portalName}, donde podrás comprobar el estado del documento en el menú de Pagos.",
        'action' => 'Abrir documento',
    ],

    'admin_license_attributed' => [
        'subject' => 'Nueva licencia solicitada',
        'greeting' => 'Notificación',
        'line_intro' => 'Se ha solicitado una nueva licencia.',
        'line_license' => '**Nombre de la licencia:** :name',
        'line_holder' => '**Nombre del titular:** :holder',
        'line_federation' => '**Nombre de la federación:** :federation',
        'action' => 'Ver detalles',
    ],

    'membership_create' => [
        'intro' => 'Se ha asignado una nueva afiliación. Se activará una vez confirmado el pago.',
        'action' => 'Abrir afiliación',
        'outro' => '¡Gracias por usar nuestra aplicación!',
        'database' => 'Se ha asignado una nueva afiliación. Se activará una vez confirmado el pago.',
    ],

    'entity_approval' => [
        'subject' => 'Se requiere la aprobación de una entidad',
        'greeting' => 'Hola :name,',
        'line_intro' => 'Hay una nueva entidad pendiente de tu aprobación.',
        'line_entity' => 'Nombre de la entidad: :entity',
        'action' => 'Ver entidad',
        'line_review' => 'Revisa los detalles de la entidad y continúa con el proceso de aprobación.',
        'salutation_regards' => 'Un cordial saludo,',
        'salutation_team' => 'Equipo de :app',
        'database' => 'Una nueva entidad requiere tu aprobación.',
    ],

    'entity_member_accepted' => [
        'subject' => 'Nuevo miembro aceptado: :name',
        'greeting' => '¡Hola!',
        'line_accepted' => ':name ha aceptado la invitación para ser miembro de :entity.',
        'line_active' => 'Este miembro ya está activo en tu entidad.',
        'action' => 'Ver miembros',
        'salutation' => 'Un saludo,<br>El equipo de :app',
        'database' => ':name ha aceptado la invitación para ser miembro.',
    ],

    'entity_member_invitation' => [
        'subject' => 'Invitación para ser miembro de :entity',
        'greeting' => '¡Hola!',
        'line_invited' => ':inviter te ha invitado a ser miembro de su entidad.',
        'line_instructions' => 'Para aceptar esta invitación, inicia sesión en la plataforma y ve a \'Entidades\' en el menú lateral.',
        'action' => 'Ver invitación',
        'line_ignore' => 'Si no esperabas esta invitación, puedes ignorar este correo.',
        'salutation' => 'Un saludo,<br>El equipo de :app',
        'database' => 'La entidad :entity te ha invitado a ser miembro.',
    ],

    'entity_request' => [
        'database_title' => 'Nueva solicitud de entidad',
        'database_message' => 'Tienes una nueva solicitud de :name para unirse.',
    ],

    'export_ready' => [
        'line_intro' => 'Tu exportación está lista para descargar. Consulta el correo para obtener el enlace.',
        'action' => 'Descargar exportación',
        'database' => 'Tu exportación está lista para descargar.',
    ],

    'federation_join_request' => [
        'database' => ':name ha solicitado unirse a la Federación.',
    ],

    'individual_request_license' => [
        'line' => 'Hay una nueva licencia de :type para aprobar.',
        'database' => 'Hay una nueva licencia de :type para aprobar.',
    ],

    'instructor_new_certification' => [
        'line' => 'Hay una nueva certificación para aprobar.',
        'action' => 'Abrir',
        'database' => 'Hay una nueva certificación para aprobar.',
    ],

    'invite_individual_professional' => [
        'subject' => 'Invitación para ser :role',
        'greeting' => '¡Hola :name!',
        'line_invited' => 'Has sido invitado a ser :role de :entity.',
        'action' => 'Consultar la invitación',
        'line_thanks' => '¡Gracias por considerar nuestra invitación!',
        'salutation' => 'Un saludo, :app',
        'database' => 'Has sido invitado a ser :role de :entity.',
    ],

    'membership_activation' => [
        'line_activated' => 'La afiliación :name se ha activado correctamente.',
        'action' => 'Abrir afiliación',
        'salutation' => $primaryShortName,
        'database' => 'La afiliación :name se ha activado correctamente.',
    ],

    'membership_expiration' => [
        'line_expires' => 'Tu afiliación :name caducará el :date.',
        'action' => 'Abrir afiliación',
        'outro' => '¡Gracias por usar nuestra aplicación!',
    ],

    'official_document_activated' => [
        'database' => 'El documento :name ha sido aprobado.',
    ],

    'official_document_created' => [
        'database' => 'El documento oficial :name ha sido enviado.',
    ],

    'official_document_deleted' => [
        'database' => 'El documento :name ha sido eliminado.',
    ],

    'report_generated' => [
        'line_ready' => 'Tu informe está listo.',
        'action' => 'Descargar el informe',
        'line_auth' => 'Debes estar autenticado para descargar el informe.',
        'database' => 'Tu descarga del informe está lista. Haz clic aquí para descargar.',
    ],
];
